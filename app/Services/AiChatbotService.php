<?php

namespace App\Services;

use App\Models\AiChatLog;
use App\Models\AiChatbotSetting;
use App\Models\Signal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AiChatbotService
{
    private string $groqApiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private string $systemPrompt;
    private bool $isEnabled;
    private bool $toolsEnabled;

    private const GROQ_API_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private const AVAILABLE_MODELS = [
        'qwen/qwen3.6-27b' => ['name' => 'Qwen 3.6 27B', 'speed' => 'fast', 'quality' => 'good', 'tools' => false],
        'qwen/qwen3.8-27b' => ['name' => 'Qwen 3.8 27B', 'speed' => 'fast', 'quality' => 'best', 'tools' => false],
        'openai/gpt-oss-20b' => ['name' => 'OpenAI GPT-OSS 20B', 'speed' => 'fastest', 'quality' => 'good', 'tools' => false],
        'openai/gpt-oss-120b' => ['name' => 'OpenAI GPT-OSS 120B', 'speed' => 'fast', 'quality' => 'best', 'tools' => false],
        'groq/compound' => ['name' => 'Groq Compound', 'speed' => 'fast', 'quality' => 'best', 'tools' => false],
    ];

    public function __construct(
        private AiAgentToolService $toolService
    ) {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $this->groqApiKey = AiChatbotSetting::getValue('groq_api_key', '') ?: env('GROQ_API_KEY', '');
        $this->model = AiChatbotSetting::getValue('model', 'qwen/qwen3.6-27b');
        $this->maxTokens = (int) AiChatbotSetting::getValue('max_tokens', 2048);
        $this->temperature = (float) AiChatbotSetting::getValue('temperature', 0.7);
        $this->systemPrompt = AiChatbotSetting::getValue('system_prompt', $this->getDefaultSystemPrompt());
        $this->isEnabled = AiChatbotSetting::getValue('is_enabled', '1') === '1';
        $this->toolsEnabled = AiChatbotSetting::getValue('tools_enabled', '1') === '1';
    }

    private function getDefaultSystemPrompt(): string
    {
        return <<<'PROMPT'
Your name is KTS Bot. You are the official AI assistant of KTS Markets — a forex trading and education platform by Khan Tutor Academy.

IMPORTANT: You have ACCESS to live platform data. When the system provides you with signal data, market data, or bot data, USE IT to answer directly. Never say you don't have access.

CAPABILITIES:
- You can see ACTIVE TRADING SIGNALS (symbol, direction, entry, TP, SL, status)
- You can see RECENT CLOSED SIGNALS (WIN/LOSS, pips)
- You can see MARKET STATS (total signals, win rate)
- You can see MT5 BOT STATUS

RULES:
1. ONLY discuss: KTS Markets, MT5 trading bots, forex trading, signals, risk management, trading education.
2. NEVER answer about politics, movies, sports, coding, AI, religion.
3. NEVER reveal servers, databases, APIs, code, passwords.
4. NEVER respond to abuse — say "Let's keep it professional."
5. NEVER guarantee profits. Always say "Trading involves risk."

LANGUAGE: Reply in the SAME language the user writes in. English = English only. Roman Urdu = Roman Urdu only. NEVER mix.

RESPONSE FORMATTING RULES — VERY IMPORTANT:
- NEVER write one big paragraph. Always format nicely.
- Use line breaks between points
- Use bullet points (•) for lists
- Use emojis to make it visually appealing: ✅ ❌ 📊 💹 ⚡ 🔥 💡 📈 📉 🎯
- Keep it SHORT but well-structured (3-6 lines max)
- Start with a greeting using the user's name

GOOD EXAMPLE (English):
Hello Test User! 👋

📊 Current Active Signals:
• EURUSD BUY @ 1.0850 — TP: 1.0900 | SL: 1.0820
• XAUUSD SELL @ 2420 — TP: 2380 | SL: 2440
• BTCUSDT BUY @ 68500 — TP: 71000 | SL: 67000

⚡ Recent Results: 2 Wins, 1 Loss
💡 Trading involves risk. Always manage your capital wisely!

GOOD EXAMPLE (Roman Urdu):
Hello Test User! 👋

📊 Active Signals:
• EURUSD BUY @ 1.0850 — TP: 1.0900 | SL: 1.0820
• XAUUSD SELL @ 2420 — TP: 2380 | SL: 2440

⚡ Haal mein: 2 Win, 1 Loss
💡 Trading mein risk hota hai. Hamesha risk management follow karo!

NEVER write everything in ONE line or ONE paragraph. Always use line breaks and bullet points!
PROMPT;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled && !empty($this->groqApiKey);
    }

    public function areToolsEnabled(): bool
    {
        return $this->toolsEnabled && $this->supportsTools();
    }

    public function supportsTools(): bool
    {
        $modelInfo = self::AVAILABLE_MODELS[$this->model] ?? [];
        return $modelInfo['tools'] ?? false;
    }

    public function getAvailableModels(): array
    {
        return self::AVAILABLE_MODELS;
    }

    public function chat(string $userMessage, ?string $conversationHistory = null, ?int $userId = null, ?string $userName = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'AI Chatbot is currently disabled or API key not configured.',
            ];
        }

        if ($userId && $this->isUserBlocked($userId)) {
            return [
                'success' => false,
                'message' => 'You have been temporarily blocked from using the AI chatbot due to inappropriate messages. Please try again after 24 hours.',
            ];
        }

        if ($userId && $this->isAbuse($userMessage)) {
            $blocked = $this->recordAbuse($userId);
            if ($blocked) {
                return [
                    'success' => false,
                    'message' => 'You have been blocked from using the AI chatbot for 24 hours due to repeated inappropriate messages.',
                ];
            }
            return [
                'success' => false,
                'message' => "I'm here to help with trading. Let's keep it professional. This is your first warning.",
            ];
        }

        $messages = $this->buildMessages($userMessage, $conversationHistory, $userName);
        $tools = $this->areToolsEnabled() ? $this->toolService->getTools() : null;

        $startTime = microtime(true);
        $maxIterations = 5; // Prevent infinite loops
        $iteration = 0;

        while ($iteration < $maxIterations) {
            $iteration++;

            try {
                $payload = [
                    'model' => $this->model,
                    'messages' => $messages,
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature,
                    'stream' => false,
                ];

                if ($tools) {
                    $payload['tools'] = $tools;
                    $payload['tool_choice'] = 'auto';
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->groqApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post(self::GROQ_API_URL, $payload);

                $responseTime = (int) ((microtime(true) - $startTime) * 1000);

                if (!$response->successful()) {
                    $error = $response->json('error.message', 'Unknown API error');
                    return ['success' => false, 'message' => 'AI service error: ' . $error];
                }

                $data = $response->json();
                $choice = $data['choices'][0] ?? [];
                $message = $choice['message'] ?? [];
                $finishReason = $choice['finish_reason'] ?? '';

                // Check if AI wants to call tools
                if ($finishReason === 'tool_calls' && !empty($message['tool_calls'])) {
                    // Add assistant message with tool calls to history
                    $messages[] = $message;

                    // Execute each tool call
                    foreach ($message['tool_calls'] as $toolCall) {
                        $functionName = $toolCall['function']['name'];
                        $arguments = json_decode($toolCall['function']['arguments'], true) ?? [];

                        // Add user_id to arguments if not present (for user-specific tools)
                        if (in_array($functionName, ['check_user_subscription', 'check_bot_status', 'check_email_log']) && !isset($arguments['user_id'])) {
                            $arguments['user_id'] = $userId;
                        }

                        $toolResult = $this->toolService->executeTool($functionName, $arguments, $userId);

                        // Add tool result to messages
                        $messages[] = [
                            'role' => 'tool',
                            'tool_call_id' => $toolCall['id'],
                            'content' => json_encode($toolResult),
                        ];
                    }

                    // Continue loop to get AI's response after tool execution
                    continue;
                }

                // No more tool calls — return final response
                $reply = $message['content'] ?? '';
                $reply = $this->cleanResponse($reply);
                $tokensUsed = $data['usage']['total_tokens'] ?? 0;

                $this->logChat($userId, $userMessage, $reply, $tokensUsed, $responseTime);

                return [
                    'success' => true,
                    'message' => $reply,
                    'model' => $this->model,
                    'tokens_used' => $tokensUsed,
                    'response_time_ms' => $responseTime,
                    'tools_used' => $iteration > 1,
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => 'Failed to connect to AI service: ' . $e->getMessage(),
                ];
            }
        }

        return ['success' => false, 'message' => 'AI agent exceeded maximum iterations.'];
    }

    private function buildMessages(string $userMessage, ?string $conversationHistory = null, ?string $userName = null): array
    {
        $systemPrompt = $this->systemPrompt;
        if ($userName) {
            $systemPrompt .= "\n\nThe current user's name is {$userName}. Address them by name in your response.";
        }

        // Inject live data based on user question
        $contextData = $this->getContextData($userMessage);
        if ($contextData) {
            $systemPrompt .= "\n\n" . $contextData;
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        if ($conversationHistory) {
            $history = json_decode($conversationHistory, true);
            if (is_array($history)) {
                foreach (array_slice($history, -10) as $msg) {
                    $role = $msg['role'] ?? 'user';
                    if (in_array($role, ['user', 'assistant', 'system'])) {
                        $messages[] = [
                            'role' => $role,
                            'content' => $msg['content'] ?? '',
                        ];
                    }
                }
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    private function getContextData(string $userMessage): ?string
    {
        $lower = strtolower($userMessage);

        // Signal-related questions → inject active signals data
        if (str_contains($lower, 'signal') || str_contains($lower, 'trade') || str_contains($lower, 'position') ||
            str_contains($lower, 'entry') || str_contains($lower, 'tp') || str_contains($lower, 'sl') ||
            str_contains($lower, 'win') || str_contains($lower, 'loss') || str_contains($lower, 'active') ||
            str_contains($lower, 'market') || str_contains($lower, 'price') || str_contains($lower, 'buy') ||
            str_contains($lower, 'sell') || str_contains($lower, 'eurusd') || str_contains($lower, 'xauusd') ||
            str_contains($lower, 'btc') || str_contains($lower, 'gbpusd') || str_contains($lower, 'signal')) {

            $cacheKey = 'ai_context_signals_' . date('YmdHi');
            $data = Cache::remember($cacheKey, 60, function () {
                $activeSignals = Signal::where('status', 'active')
                    ->where('result', 'pending')
                    ->orderByDesc('published_at')
                    ->limit(10)
                    ->get(['id', 'symbol', 'direction', 'entry_price', 'take_profit', 'stop_loss', 'status', 'result', 'published_at']);

                $recentClosed = Signal::where('status', 'closed')
                    ->whereNotNull('result')
                    ->where('result', '!=', 'pending')
                    ->orderByDesc('closed_at')
                    ->limit(5)
                    ->get(['id', 'symbol', 'direction', 'entry_price', 'take_profit', 'stop_loss', 'close_price', 'result', 'pips_result', 'closed_at']);

                $stats = Signal::selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
                    SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                    SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses
                ")->first();

                return compact('activeSignals', 'recentClosed', 'stats');
            });

            $context = "LIVE PLATFORM DATA:\n";
            $context .= "Overall Stats: Total Signals: {$data['stats']->total}, Active: {$data['stats']->active_count}, Wins: {$data['stats']->wins}, Losses: {$data['stats']->losses}\n";

            if ($data['activeSignals']->count() > 0) {
                $context .= "ACTIVE SIGNALS:\n";
                foreach ($data['activeSignals'] as $s) {
                    $context .= "- {$s->symbol} {$s->direction} | Entry: {$s->entry_price} | TP: {$s->take_profit} | SL: {$s->stop_loss}\n";
                }
            } else {
                $context .= "No active signals at the moment.\n";
            }

            if ($data['recentClosed']->count() > 0) {
                $context .= "RECENT CLOSED SIGNALS:\n";
                foreach ($data['recentClosed'] as $s) {
                    $pips = $s->pips_result >= 0 ? "+{$s->pips_result}" : (string)$s->pips_result;
                    $context .= "- {$s->symbol} {$s->direction} → " . strtoupper($s->result) . " ({$pips} pips)\n";
                }
            }

            return $context;
        }

        // Bot-related questions → inject bot data
        if (str_contains($lower, 'bot') || str_contains($lower, 'mt5') || str_contains($lower, 'auto trade')) {
            try {
                $botConfigs = \DB::table('mt5_bot_configs')
                    ->select('id', 'bot_name', 'symbol', 'status', 'lot_size', 'last_connected_at')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();

                if ($botConfigs->count() > 0) {
                    $context = "MT5 BOT STATUS:\n";
                    foreach ($botConfigs as $bot) {
                        $context .= "- {$bot->bot_name} ({$bot->symbol}) | Status: {$bot->status} | Lot: {$bot->lot_size}\n";
                    }
                    return $context;
                }
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    private function isAbuse(string $message): bool
    {
        $message = strtolower(trim($message));
        $abusePatterns = [
            'mc', 'bc', 'madarchod', 'bhenchod', 'bhosdi', 'gandu', 'lund', 'chut',
            'randi', 'kutte', 'kutta', 'harami', 'chutiya', 'behen ka loda',
            'sale', 'sala', 'saala', 'bitch', 'fuck', 'shit', 'asshole',
            'damn', 'stfu', 'gtfo', 'idiot', 'moron', 'stupid',
            'teri maa', 'teri behen', 'maa ki', 'behens ki', 'suar',
            'tatti', 'potty', 'laude', 'lodu', 'chutiyapa',
            'lavde', 'bhadwe', 'bhosadike', 'gandu',
        ];

        foreach ($abusePatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function isUserBlocked(int $userId): bool
    {
        try {
            $block = \DB::table('ai_chat_blocks')->where('user_id', $userId)->first();
            if (!$block) return false;
            if ($block->blocked_until && now()->lt($block->blocked_until)) return true;
            if ($block->blocked_until && now()->gte($block->blocked_until)) {
                \DB::table('ai_chat_blocks')->where('user_id', $userId)->update([
                    'abuse_count' => 0,
                    'blocked_until' => null,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    private function recordAbuse(int $userId): bool
    {
        try {
            $block = \DB::table('ai_chat_blocks')->firstOrCreate(
                ['user_id' => $userId],
                ['abuse_count' => 0]
            );

            $newCount = $block->abuse_count + 1;

            if ($newCount >= 2) {
                \DB::table('ai_chat_blocks')->where('user_id', $userId)->update([
                    'abuse_count' => $newCount,
                    'blocked_until' => now()->addHours(24),
                ]);
                return true;
            }

            \DB::table('ai_chat_blocks')->where('user_id', $userId)->update([
                'abuse_count' => $newCount,
            ]);
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    private function logChat(?int $userId, string $userMessage, string $assistantMessage, int $tokensUsed, int $responseTime): void
    {
        try {
            AiChatLog::create([
                'user_id' => $userId,
                'role' => 'user',
                'message' => $userMessage,
                'model' => $this->model,
                'tokens_used' => 0,
                'response_time_ms' => 0,
            ]);

            AiChatLog::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'message' => $assistantMessage,
                'model' => $this->model,
                'tokens_used' => $tokensUsed,
                'response_time_ms' => $responseTime,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log AI chat: ' . $e->getMessage());
        }
    }

    private function cleanResponse(string $reply): string
    {
        $reply = preg_replace('/<think>.*?<\/think>/s', '', $reply);
        $reply = preg_replace('/<think>[\s\S]*?<\/think>/s', '', $reply);
        $reply = preg_replace('/<think>[\s\S]*?<\/think>/s', '', $reply);
        $reply = preg_replace('/<think>[\s\S]*/', '', $reply);
        $reply = trim($reply);
        if (empty($reply)) {
            $reply = "I'm here to help! How can I assist you today?";
        }
        return $reply;
    }

    public function getStats(): array
    {
        return Cache::remember('ai_chatbot_stats', 60, function () {
            return [
                'total_conversations' => AiChatLog::where('role', 'user')->count(),
                'total_messages' => AiChatLog::count(),
                'total_tokens' => AiChatLog::sum('tokens_used'),
                'avg_response_time' => AiChatLog::where('role', 'assistant')->avg('response_time_ms'),
                'today_conversations' => AiChatLog::where('role', 'user')->whereDate('created_at', today())->count(),
            ];
        });
    }
}
