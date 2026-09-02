<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatSticker;
use App\Models\ChatStickerPack;
use App\Models\ChatBannedUser;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatApiController extends Controller
{
    public function rooms()
    {
        $rooms = ChatRoom::where('is_active', true)
            ->where('is_paused', false)
            ->withCount('messages')
            ->orderBy('sort_order')
            ->get()
            ->map(function ($room) {
                $lastMessage = $room->messages()
                    ->where('is_deleted', false)
                    ->with('user:id,name')
                    ->latest()
                    ->first();

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'slug' => $room->slug,
                    'description' => $room->description,
                    'is_public' => $room->is_public,
                    'messages_count' => $room->messages_count,
                    'last_message' => $lastMessage ? [
                        'id' => $lastMessage->id,
                        'message' => $lastMessage->type === 'sticker' ? null : Str::limit($lastMessage->filtered_message, 50),
                        'type' => $lastMessage->type,
                        'user_name' => $lastMessage->user->name ?? 'Unknown',
                        'created_at' => $lastMessage->created_at->toISOString(),
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rooms,
        ]);
    }

    public function messages(Request $request, ChatRoom $room)
    {
        $user = $request->user();

        $isBanned = ChatBannedUser::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->exists();

        if ($isBanned) {
            return response()->json([
                'success' => false,
                'message' => 'You are banned from this chat room.',
            ], 403);
        }

        $validated = $request->validate([
            'before_id' => 'nullable|integer',
            'after_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $room->messages()
            ->with(['user:id,name,chat_badge,badge_color,is_premium', 'sticker:id,name,image_url,pack_id'])
            ->where('is_deleted', false);

        if (!empty($validated['before_id'])) {
            $query->where('id', '<', $validated['before_id']);
        }

        if (!empty($validated['after_id'])) {
            $query->where('id', '>', $validated['after_id']);
        }

        $messages = $query->orderByDesc('id')
            ->limit($validated['limit'] ?? 50)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($msg) {
                $data = [
                    'id' => $msg->id,
                    'type' => $msg->type,
                    'is_pinned' => $msg->is_pinned,
                    'is_flagged' => $msg->is_flagged,
                    'created_at' => $msg->created_at->toISOString(),
                    'user' => [
                        'id' => $msg->user->id,
                        'name' => $msg->user->name,
                        'badge' => $msg->user->chat_badge,
                        'badge_color' => $msg->user->badge_color,
                        'is_premium' => $msg->user->is_premium,
                    ],
                ];

                if ($msg->type === 'sticker' && $msg->sticker) {
                    $data['sticker'] = [
                        'id' => $msg->sticker->id,
                        'name' => $msg->sticker->name,
                        'image_url' => asset('storage/' . $msg->sticker->image_url),
                    ];
                    $data['message'] = null;
                } else {
                    $data['message'] = $msg->filtered_message;
                    $data['sticker'] = null;
                }

                return $data;
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'is_paused' => $room->is_paused,
            ],
        ]);
    }

    public function send(Request $request, ChatRoom $room)
    {
        $user = $request->user();

        $isBanned = ChatBannedUser::where('user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->exists();

        if ($isBanned) {
            return response()->json([
                'success' => false,
                'message' => 'You are banned from this chat room.',
            ], 403);
        }

        if ($room->is_paused) {
            return response()->json([
                'success' => false,
                'message' => 'This chat room is currently paused.',
            ], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:text,image,sticker',
            'message' => 'required_if:type,text|string|max:2000',
            'sticker_id' => 'required_if:type,sticker|exists:chat_stickers,id',
            'image' => 'required_if:type,image|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $messageData = [
            'room_id' => $room->id,
            'user_id' => $user->id,
            'type' => $validated['type'],
        ];

        switch ($validated['type']) {
            case 'text':
                $messageData['message'] = $validated['message'];
                break;

            case 'sticker':
                $sticker = ChatSticker::find($validated['sticker_id']);
                if (!$sticker || !$sticker->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sticker not found or inactive.',
                    ], 422);
                }
                $sticker->incrementUsage();
                $messageData['message'] = '[sticker]';
                $messageData['sticker_id'] = $sticker->id;
                break;

            case 'image':
                $path = $request->file('image')->store('chat/' . $room->slug, 'public');
                $messageData['message'] = $path;
                break;
        }

        $chatMessage = ChatMessage::create($messageData);

        $chatMessage->load(['user:id,name,chat_badge,badge_color,is_premium', 'sticker:id,name,image_url']);

        $response = [
            'id' => $chatMessage->id,
            'type' => $chatMessage->type,
            'is_pinned' => $chatMessage->is_pinned,
            'created_at' => $chatMessage->created_at->toISOString(),
            'user' => [
                'id' => $chatMessage->user->id,
                'name' => $chatMessage->user->name,
                'badge' => $chatMessage->user->chat_badge,
                'badge_color' => $chatMessage->user->badge_color,
                'is_premium' => $chatMessage->user->is_premium,
            ],
        ];

        if ($chatMessage->type === 'sticker' && $chatMessage->sticker) {
            $response['sticker'] = [
                'id' => $chatMessage->sticker->id,
                'name' => $chatMessage->sticker->name,
                'image_url' => asset('storage/' . $chatMessage->sticker->image_url),
            ];
            $response['message'] = null;
        } else {
            $response['message'] = $chatMessage->filtered_message;
            $response['sticker'] = null;
        }

        if ($chatMessage->type === 'image') {
            $response['image_url'] = asset('storage/' . $chatMessage->message);
        }

        return response()->json([
            'success' => true,
            'data' => $response,
        ], 201);
    }

    public function stickers()
    {
        $packs = Cache::remember('active_sticker_packs_api', 600, function () {
            return ChatStickerPack::where('is_active', true)
                ->with(['stickers' => function ($q) {
                    $q->where('is_active', true)->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($pack) {
                    return [
                        'id' => $pack->id,
                        'name' => $pack->name,
                        'slug' => $pack->slug,
                        'description' => $pack->description,
                        'thumbnail' => $pack->thumbnail ? asset('storage/' . $pack->thumbnail) : null,
                        'stickers' => $pack->stickers->map(function ($sticker) {
                            return [
                                'id' => $sticker->id,
                                'name' => $sticker->name,
                                'image_url' => asset('storage/' . $sticker->image_url),
                            ];
                        }),
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $packs,
        ]);
    }

    public function pinnedMessages(ChatRoom $room)
    {
        $messages = $room->messages()
            ->where('is_pinned', true)
            ->with(['user:id,name,chat_badge,badge_color,is_premium', 'sticker:id,name,image_url'])
            ->latest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'type' => $msg->type,
                    'message' => $msg->type === 'sticker' ? null : $msg->filtered_message,
                    'sticker' => $msg->sticker ? [
                        'id' => $msg->sticker->id,
                        'name' => $msg->sticker->name,
                        'image_url' => asset('storage/' . $msg->sticker->image_url),
                    ] : null,
                    'user' => [
                        'name' => $msg->user->name ?? 'Unknown',
                    ],
                    'pinned_at' => $msg->pinned_at?->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function reportMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $message = ChatMessage::findOrFail($id);

        ActivityLogger::log(
            'report_chat_message',
            'ChatMessage',
            $message->id,
            "User {$user->name} reported message #{$message->id} by user #{$message->user_id}. Reason: " . ($validated['reason'] ?? 'Inappropriate content')
        );

        return response()->json([
            'success' => true,
            'message' => 'Thank you. The reported message has been submitted to moderators for review.',
        ]);
    }

    public function blockUser(Request $request, $id)
    {
        $user = $request->user();

        ActivityLogger::log(
            'block_chat_user',
            'User',
            $id,
            "User {$user->name} blocked user #{$id}"
        );

        return response()->json([
            'success' => true,
            'message' => 'User has been blocked. You will no longer see messages from this user.',
        ]);
    }
}
