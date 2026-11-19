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
use Illuminate\Support\Facades\Storage;

class Mt5BotFileDispatchController extends Controller
{
    /**
     * GET /admin/mt5-bot/send-file
     * Show the send-file page with approved demo requests + all users.
     */
    public function index(Request $request)
    {
        $bot = Mt5BotConfig::whereNotNull('bot_file_data')
            ->orWhereNotNull('bot_file_name')
            ->orderBy('id', 'asc')
            ->first() ?? Mt5BotConfig::orderBy('id', 'asc')->first();

        // Approved & linked demo account requests (with user info)
        $demoRequests = DemoAccountRequest::with('user')
            ->whereIn('status', ['approved', 'linked'])
            ->latest()
            ->get();

        // All registered users (searchable, exclude banned)
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
     * POST /admin/mt5-bot/send-file/upload
     * Upload bot file directly on the send-file page (stores in DB for Vercel persistence).
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'bot_file' => 'required|file|max:4608',
        ], [
            'bot_file.max' => 'The bot file must not be greater than 4.5 MB due to serverless payload limits. Please compress your file.',
        ]);

        $bot = Mt5BotConfig::orderBy('id', 'asc')->first();
        if (!$bot) {
            $bot = Mt5BotConfig::create([
                'name' => 'KTS10 Pips Bot',
                'mt5_account_number' => '87654321',
                'mt5_server' => 'Exness-MT5Real',
                'status' => 'active',
                'mode' => 'live',
            ]);
        }

        $uploadedFile = $request->file('bot_file');

        // Read and base64-encode for permanent database persistence (survives Vercel serverless resets)
        $fileContent = file_get_contents($uploadedFile->getRealPath());
        $base64Data = base64_encode($fileContent);

        $originalName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $formattedSize = $fileSize >= 1048576 
            ? round($fileSize / 1048576, 2) . ' MB' 
            : round($fileSize / 1024, 2) . ' KB';
        $fileExt = strtolower($uploadedFile->getClientOriginalExtension() ?: 'ex5');

        // Attempt disk storage as well
        try {
            $filePath = $uploadedFile->store('mt5-bots', 'public');
        } catch (\Throwable $e) {
            $filePath = 'mt5-bots/' . $originalName;
        }

        // Update ALL bot configs so regardless of which row is retrieved, file is immediately recognized
        Mt5BotConfig::query()->update([
            'bot_file_path' => $filePath,
            'bot_file_name' => $originalName,
            'bot_file_size' => $formattedSize,
            'bot_file_type' => $fileExt,
            'bot_file_data' => $base64Data,
            'bot_file_uploaded_at' => now(),
        ]);

        ActivityLogger::log('upload_bot_file', 'Mt5BotConfig', $bot->id, "Uploaded bot file {$originalName} ({$formattedSize}) directly from dispatch console");

        return redirect()->route('admin.mt5-bot.send-file')
            ->with('success', "✅ Bot software file <strong>{$originalName}</strong> ({$formattedSize}) uploaded successfully! You can now send it to users.");
    }

    /**
     * POST /admin/mt5-bot/send-file
     * Send the MT5 bot file to selected users OR all registered users.
     */
    public function send(Request $request)
    {
        $request->validate([
            'send_target' => 'required|in:selected,all',
            'user_ids'    => 'required_if:send_target,selected|array|min:1',
            'user_ids.*'  => 'integer|exists:users,id',
            'admin_note'  => 'nullable|string|max:500',
        ]);

        $bot = Mt5BotConfig::whereNotNull('bot_file_data')
            ->orWhereNotNull('bot_file_name')
            ->orderBy('id', 'asc')
            ->first() ?? Mt5BotConfig::orderBy('id', 'asc')->first();

        if (!$bot || !$bot->hasBotFile()) {
            return back()->with('error', 'No bot file has been uploaded yet. Please upload a bot file below first.');
        }

        if ($request->input('send_target') === 'all') {
            $users = User::whereNull('deleted_at')
                ->where('is_banned', false)
                ->get();
        } else {
            $users = User::whereIn('id', $request->input('user_ids', []))->get();
        }

        if ($users->isEmpty()) {
            return back()->with('error', 'No valid users selected to receive the email.');
        }

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
            "Dispatched MT5 bot file to {$sent} user(s)" . (count($failed) ? ", failed: " . count($failed) : ''),
        );

        $message = "✅ Bot file email successfully sent to <strong>{$sent}</strong> user(s)!";
        if (count($failed)) {
            $message .= ' ⚠️ Some failed: ' . implode(', ', array_slice($failed, 0, 5));
            return back()->with('warning', $message);
        }

        return redirect()->route('admin.mt5-bot.send-file')
            ->with('success', $message);
    }
}
