<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatBannedUser;
use App\Models\ChatMessage;
use App\Models\ChatRestrictedWord;
use App\Models\ChatRoom;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $query = ChatMessage::with(['user', 'room', 'deleter']);

        if ($roomId = $request->input('room_id')) {
            $query->where('room_id', $roomId);
        }

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('message', 'like', "%{$safeSearch}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$safeSearch}%"));
            });
        }

        if ($request->input('flagged') === '1') {
            $query->where('is_flagged', true);
        }

        if ($request->input('deleted') === '1') {
            $query->where('is_deleted', true);
        } elseif ($request->input('deleted') === '0') {
            $query->where('is_deleted', false);
        }

        $messages = $query->latest()->paginate(30)->withQueryString();
        $rooms = ChatRoom::where('is_active', true)->orderBy('sort_order')->get();

        $stats = Cache::remember('chat_stats', 60, function () {
            return [
                'total_messages' => ChatMessage::count(),
                'today_messages' => ChatMessage::whereDate('created_at', today())->count(),
                'flagged' => ChatMessage::where('is_flagged', true)->where('is_deleted', false)->count(),
                'deleted' => ChatMessage::where('is_deleted', true)->count(),
                'banned_users' => ChatBannedUser::where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->count(),
                'restricted_words' => ChatRestrictedWord::where('is_active', true)->count(),
                'rooms' => ChatRoom::where('is_active', true)->count(),
                'pinned_messages' => ChatMessage::where('is_pinned', true)->count(),
            ];
        });

        return view('admin.chat.index', compact('messages', 'rooms', 'stats'));
    }

    public function destroyMessage(ChatMessage $message)
    {
        if ($message->is_deleted) {
            return back()->with('error', 'Message is already deleted.');
        }

        $message->update([
            'is_deleted' => true,
            'deleted_by' => auth()->id(),
        ]);

        $userName = $message->user->name ?? 'Unknown';
        ActivityLogger::log('chat_delete_message', 'ChatMessage', $message->id, "Deleted chat message by {$userName}: " . Str::limit($message->message, 50));
        Cache::forget('chat_stats');

        return back()->with('success', 'Message deleted.');
    }

    public function restoreMessage(ChatMessage $message)
    {
        $message->update([
            'is_deleted' => false,
            'deleted_by' => null,
        ]);

        ActivityLogger::log('chat_restore_message', 'ChatMessage', $message->id, "Restored chat message");
        Cache::forget('chat_stats');

        return back()->with('success', 'Message restored.');
    }

    public function toggleFlag(ChatMessage $message)
    {
        $message->update(['is_flagged' => !$message->is_flagged]);
        Cache::forget('chat_stats');

        return back()->with('success', $message->is_flagged ? 'Message flagged.' : 'Flag removed.');
    }

    public function pinMessage(ChatMessage $message)
    {
        ChatMessage::where('room_id', $message->room_id)
            ->where('is_pinned', true)
            ->update(['is_pinned' => false, 'pinned_at' => null, 'pinned_by' => null]);

        $message->update([
            'is_pinned' => true,
            'pinned_at' => now(),
            'pinned_by' => auth()->id(),
        ]);

        ActivityLogger::log('chat_pin_message', 'ChatMessage', $message->id, "Pinned message in room: {$message->room->name}");
        Cache::forget('chat_stats');

        return back()->with('success', 'Message pinned.');
    }

    public function unpinMessage(ChatMessage $message)
    {
        $message->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_by' => null,
        ]);

        ActivityLogger::log('chat_unpin_message', 'ChatMessage', $message->id, "Unpinned message in room: {$message->room->name}");
        Cache::forget('chat_stats');

        return back()->with('success', 'Message unpinned.');
    }

    public function banUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
            'duration' => 'nullable|integer|min:1|max:365',
        ]);

        ChatBannedUser::where('user_id', $validated['user_id'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $existing = ChatBannedUser::where('user_id', $validated['user_id'])
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->first();

        if ($existing) {
            return back()->with('error', 'User is already banned.');
        }

        ChatBannedUser::create([
            'user_id' => $validated['user_id'],
            'banned_by' => auth()->id(),
            'reason' => $validated['reason'] ?? null,
            'expires_at' => isset($validated['duration']) ? now()->addDays($validated['duration']) : null,
        ]);

        ActivityLogger::log('chat_ban_user', 'ChatBannedUser', null, "Banned user ID {$validated['user_id']}: {$validated['reason']}");
        Cache::forget('chat_stats');

        return back()->with('success', 'User banned successfully.');
    }

    public function unbanUser(ChatBannedUser $ban)
    {
        $ban->delete();
        Cache::forget('chat_stats');

        return back()->with('success', 'User unbanned.');
    }

    public function bannedUsers()
    {
        ChatBannedUser::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $bans = ChatBannedUser::with(['user', 'banner'])->latest()->paginate(30);
        return view('admin.chat.banned-users', compact('bans'));
    }

    public function restrictedWords()
    {
        $words = ChatRestrictedWord::with('creator')->latest()->paginate(30);
        return view('admin.chat.restricted-words', compact('words'));
    }

    public function storeRestrictedWord(Request $request)
    {
        $validated = $request->validate([
            'word' => 'required|string|max:100|min:1',
            'replacement' => 'nullable|string|max:10',
        ]);

        $word = strtolower(trim($validated['word']));
        if (ChatRestrictedWord::where('word', $word)->exists()) {
            return back()->with('error', 'This word is already restricted.');
        }

        ChatRestrictedWord::create([
            'word' => $word,
            'replacement' => $validated['replacement'] ?? '***',
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        Cache::forget('chat_stats');
        Cache::forget('active_restricted_words');

        return back()->with('success', 'Restricted word added.');
    }

    public function toggleRestrictedWord(ChatRestrictedWord $word)
    {
        $word->update(['is_active' => !$word->is_active]);
        Cache::forget('active_restricted_words');
        Cache::forget('chat_stats');
        return back()->with('success', $word->is_active ? 'Word activated.' : 'Word deactivated.');
    }

    public function destroyRestrictedWord(ChatRestrictedWord $word)
    {
        $word->delete();
        Cache::forget('active_restricted_words');
        Cache::forget('chat_stats');
        return back()->with('success', 'Restricted word removed.');
    }

    public function rooms()
    {
        $rooms = ChatRoom::withCount('messages')->orderBy('sort_order')->paginate(30);
        return view('admin.chat.rooms', compact('rooms'));
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $slug = Str::slug($validated['name']);
        if (empty($slug)) {
            $slug = Str::random(8);
        }
        if (ChatRoom::where('slug', $slug)->exists()) {
            $slug .= '-' . Str::random(5);
        }
        $validated['slug'] = $slug;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sort_order'] = (int) ChatRoom::max('sort_order') + 1;

        ChatRoom::create($validated);

        return back()->with('success', 'Room created.');
    }

    public function toggleRoom(ChatRoom $room)
    {
        $room->update(['is_active' => !$room->is_active]);
        return back()->with('success', $room->is_active ? 'Room activated.' : 'Room deactivated.');
    }

    public function togglePauseRoom(ChatRoom $room, Request $request)
    {
        $validated = $request->validate([
            'pause_reason' => 'nullable|string|max:500',
        ]);

        $room->update([
            'is_paused' => !$room->is_paused,
            'pause_reason' => $room->is_paused ? null : ($validated['pause_reason'] ?? 'Paused by admin'),
            'paused_at' => $room->is_paused ? null : now(),
            'paused_by' => $room->is_paused ? null : auth()->id(),
        ]);

        ActivityLogger::log('chat_toggle_pause', 'ChatRoom', $room->id, ($room->is_paused ? 'Paused' : 'Unpaused') . " room: {$room->name}");

        return back()->with('success', $room->is_paused ? 'Room paused.' : 'Room unpaused.');
    }

    public function destroyRoom(ChatRoom $room)
    {
        try {
            $room->delete();
            Cache::forget('chat_stats');
            return back()->with('success', 'Room deleted.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Cannot delete room: it may be referenced by other data.');
        } catch (\Exception $e) {
            \Log::error('Failed to delete chat room: ' . $e->getMessage());
            return back()->with('error', 'An unexpected error occurred.');
        }
    }

    public function badges()
    {
        $users = User::where(function ($q) {
                $q->whereNotNull('chat_badge')
                    ->orWhere('is_premium', true);
            })
            ->orderBy('chat_badge')
            ->paginate(30);

        return view('admin.chat.badges', compact('users'));
    }

    public function updateBadge(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'chat_badge' => 'nullable|string|max:50',
            'badge_color' => 'nullable|string|in:primary,secondary,success,danger,warning,info',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $user->update([
            'chat_badge' => $validated['chat_badge'] ?? null,
            'badge_color' => $validated['badge_color'] ?? 'primary',
        ]);

        ActivityLogger::log('update_chat_badge', 'User', $user->id, "Updated chat badge for {$user->name}: " . ($validated['chat_badge'] ?? 'removed'));

        return back()->with('success', 'Badge updated for ' . $user->name . '.');
    }
}
