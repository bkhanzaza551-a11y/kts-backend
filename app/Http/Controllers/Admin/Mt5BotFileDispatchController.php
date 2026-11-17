<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Mt5BotFileMail;
use App\Models\DemoAccountRequest;
use App\Models\Mt5BotConfig;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Mt5BotFileDispatchController extends Controller
{
    /**
     * GET /admin/mt5-bot/send-file
     * Show the send-file page with approved demo requests + all users.
     */
    public function index(Request $request)
    {
        $bot = Mt5BotConfig::first();

        // Approved & linked demo account requests (with user info)
        $demoRequests = DemoAccountRequest::with('user')
            ->whereIn('status', ['approved', 'linked'])
            ->latest()
            ->get();

        // All registered users (searchable, exclude super admins)
        $userQuery = User::query()
            ->whereNull('deleted_at')
            ->where('is_banned', false)
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = '%' . str_replace(['%', '_'], ['\\%', '\\_'], trim($request->search)) . '%';
            $userQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                  ->orWhere('email', 'like', $s);
            });
        }

        $allUsers = $userQuery->get(['id', 'name', 'email', 'status', 'created_at']);

        // Collect user IDs that already have an approved/linked demo request
        $demoUserIds = $demoRequests->pluck('user_id')->unique()->toArray();

        return view('admin.mt5-bot.send-file', compact(
            'bot',
            'demoRequests',
            'allUsers',
            'demoUserIds',
        ));
    }

    /**
     * POST /admin/mt5-bot/send-file
     * Send the MT5 bot file to the selected users via email.
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1|max:200',
            'user_ids.*' => 'integer|exists:users,id',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $bot = Mt5BotConfig::first();

        if (!$bot || !$bot->hasBotFile()) {
            return back()->with('error', 'No bot file has been uploaded yet. Please upload a bot file first from the MT5 Bot settings.');
        }

        $users     = User::whereIn('id', $request->user_ids)->get();
        $adminNote = $request->input('admin_note', '');
        $sent      = 0;
        $failed    = [];

        foreach ($users as $user) {
            try {
                Mail::to($user->email, $user->name)
                    ->send(new Mt5BotFileMail($user, $bot, $adminNote));
                $sent++;
            } catch (\Throwable $e) {
                $failed[] = $user->email . ' (' . $e->getMessage() . ')';
                \Log::error("Mt5BotFileMail failed for {$user->email}: " . $e->getMessage());
            }
        }

        ActivityLogger::log(
            'send_bot_file',
            'Mt5BotConfig',
            $bot->id,
            "Dispatched MT5 bot file to {$sent} user(s)" . (count($failed) ? ", failed: " . implode(', ', array_column(array_map(fn($f) => ['e' => explode(' (', $f)[0]], $failed), 'e')) : ''),
        );

        $message = "✅ Bot file sent successfully to <strong>{$sent}</strong> user(s).";
        if (count($failed)) {
            $message .= ' ⚠️ Failed for: ' . implode(', ', $failed);
            return back()->with('warning', $message);
        }

        return redirect()->route('admin.mt5-bot.send-file')
            ->with('success', $message);
    }
}
