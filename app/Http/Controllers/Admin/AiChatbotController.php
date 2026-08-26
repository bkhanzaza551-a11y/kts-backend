<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiChatLog;
use App\Models\AiChatbotSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiChatbotController extends Controller
{
    private const ALLOWED_MODELS = [
        'llama-3.1-8b-instant', 'llama-3.3-70b-versatile',
        'mixtral-8x7b-32768', 'gemma2-9b-it',
    ];

    public function settings()
    {
        $settings = AiChatbotSetting::all()->keyBy('key');
        return view('admin.ai-chatbot.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'system_prompt' => 'required|string|max:10000',
            'groq_api_key' => 'nullable|string|max:500',
            'openai_api_key' => 'nullable|string|max:500',
            'model' => 'required|string|max:100|in:' . implode(',', self::ALLOWED_MODELS),
            'max_tokens' => 'required|integer|min:100|max:8000',
            'temperature' => 'required|numeric|min:0|max:2',
            'is_enabled' => 'boolean',
            'tools_enabled' => 'boolean',
        ]);

        $validated['is_enabled'] = ($validated['is_enabled'] ?? 0) ? '1' : '0';
        $validated['tools_enabled'] = ($validated['tools_enabled'] ?? 0) ? '1' : '0';

        foreach ($validated as $key => $value) {
            if (in_array($key, ['groq_api_key', 'openai_api_key']) && ($value === '' || $value === null)) {
                continue;
            }
            AiChatbotSetting::setValue($key, $value);
        }

        ActivityLogger::log('update', 'AiChatbotSetting', null, 'Updated AI chatbot settings');
        Cache::forget('ai_chatbot_config');
        Cache::forget('ai_chatbot_settings');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function chatLogs(Request $request)
    {
        $query = AiChatLog::with('user');

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
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

        if ($role = $request->input('role')) {
            if (in_array($role, ['user', 'assistant', 'system'])) {
                $query->where('role', $role);
            }
        }

        $logs = $query->latest()->paginate(30)->withQueryString();

        $stats = Cache::remember('ai_chat_stats', 60, function () {
            return [
                'total_conversations' => AiChatLog::where('role', 'user')->count(),
                'total_messages' => AiChatLog::count(),
                'total_tokens' => AiChatLog::sum('tokens_used'),
                'avg_response_time' => AiChatLog::where('role', 'assistant')->avg('response_time_ms'),
                'flagged' => AiChatLog::where('is_flagged', true)->count(),
                'today_conversations' => AiChatLog::where('role', 'user')->whereDate('created_at', today())->count(),
            ];
        });

        return view('admin.ai-chatbot.chat-logs', compact('logs', 'stats'));
    }

    public function toggleFlag(AiChatLog $log)
    {
        $log->update(['is_flagged' => !$log->is_flagged]);
        return back()->with('success', $log->is_flagged ? 'Message flagged.' : 'Flag removed.');
    }
}
