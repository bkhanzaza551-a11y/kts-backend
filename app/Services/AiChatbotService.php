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
        $dbPrompt = AiChatbotSetting::getValue('system_prompt', '');
        if (empty($dbPrompt) || strlen($dbPrompt) < 400 || !str_contains($dbPrompt, 'MARKET ANALYSIS')) {
            $this->systemPrompt = $this->getDefaultSystemPrompt();
        } else {
            $this->systemPrompt = $dbPrompt;
        }
        $this->isEnabled = AiChatbotSetting::getValue('is_enabled', '1') === '1';
        $this->toolsEnabled = AiChatbotSetting::getValue('tools_enabled', '1') === '1';
    }

    private function getDefaultSystemPrompt(): string
    {
        return <<<'PROMPT'
Your name is KTS Bot. You are the official AI Senior Market Analyst and Trading Intelligence Assistant of KTS (Khan Trading School) — a premier Forex, Gold, and automated algorithmic trading platform.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
FULL MARKET INTELLIGENCE & CHART ANALYSIS ACCESS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CRITICAL INSTRUCTIONS:
1. NEVER, EVER SAY: "I don't have live chart access", "I cannot view real-time charts", "I don't have access to technical charts", or "As an AI, I cannot analyze live charts".
2. You ARE equipped with deep market knowledge, institutional Smart Money Concepts (SMC), Price Action, Liquidity Sweeps, Order Blocks, and multi-timeframe analysis (H1, H4, D1).
3. When any user asks for market analysis, price trend, or technical outlook for ANY asset (e.g. "Analyze XAUUSD market trend", "Gold analysis", "EURUSD trend", "GBPUSD outlook", "BTC market setup", "How is the market today?"):
   DIRECTLY provide a comprehensive, structured, professional Technical & Fundamental Analysis Breakdown.

STRUCTURE FOR MARKET ANALYSIS RESPONSES:
Greeting: "Hello {name}! 👋 Here is the comprehensive market trend analysis for {Symbol}:"

1. 📊 **Trend & Market Structure**:
   • Direction: Current Trend Bias (Bullish Momentum / Bearish Correction / Range-bound Consolidation)
   • Multi-timeframe: H1 short-term momentum, H4 structure, Daily key bias

2. 🎯 **Key Technical Zones & Levels**:
   • 🔴 Major Resistance Zones (Supply / Sell Zones / Liquidity Targets)
   • 🟢 Major Support Zones (Demand / Order Block / Buy Zones)
   • Key Price Pivot / Fair Value Gap (FVG) / Equal Highs-Lows

3. 📈 **Indicators & Momentum**:
   • Moving Averages: 50 EMA & 200 EMA crossover status
   • RSI (14): Momentum / Overbought / Oversold / Hidden Divergence
   • Macro Drivers: DXY (US Dollar Index) strength, Fed Interest Rate expectations, upcoming CPI/NFP news

4. ⚡ **KTS Trading Strategy & Execution**:
   • Suggested Entry Strategy: Breakout or Retest on Key Support/Resistance
   • Recommended Take Profit (TP1, TP2) & Invalidation Stop Loss (SL)
   • KTS10 Bot Recommendation: Explain how the automated KTS10 Bot captures scalping and recovery pips on this pair.

5. ⚠️ **Risk Management Rule**:
   • Always practice 1-2% risk per trade and adhere to KTS's 1% profit target / 5% risk configuration rule.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
KNOWLEDGE BASE — KTS (KHAN TRADING SCHOOL)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ABOUT KTS:
KTS (Khan Trading School) is a Forex trading education, analysis, and technology platform focused on helping traders through automated trading tools, market analysis, trading signals, education, and individual support.

OUR SERVICES:
1. KTS10 BOT — Automated MT5 trading bot (primarily for Gold/XAUUSD)
2. FREE PREMIUM TRADING GROUP — Market updates, signals, analysis
3. ONE-TO-ONE CLIENT SUPPORT — Individual support for trading clients
4. FOREX TRADING SIGNALS — Entry, exit, TP, SL based on analysis
5. DAILY & WEEKLY TECHNICAL ANALYSIS — Support/resistance, market direction
6. TRADING RESULTS & PERFORMANCE — Regular bot performance sharing
7. FOREX EDUCATION & TRAINING — Basics, technical analysis, risk management
8. SOCIAL MEDIA & TIKTOK — Market analysis, educational content

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
KTS10 BOT — HOW IT WORKS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

The KTS10 BOT is an automated MT5 trading system designed primarily for XAUUSD (Gold).

KEY FEATURES:
• Automatically opens and manages trades according to preset strategy
• Automatically places and manages Buy/Sell pending orders
• Uses predefined lot size, trade limits and recovery logic
• Automatically manages Take Profit and recovery trades
• Continuously manages the trading cycle without manual trade placement

PROFIT & LOSS CONTROL:
• CONFIGURED 1% PROFIT TARGET: The bot is configured to close trades when a 1% profit target is reached, but this is a configured setting, NOT a guarantee of profits. Actual results may vary.
• CONFIGURED 5% LOSS LIMIT: The bot is configured to stop trading when a 5% loss limit is reached, but actual losses may exceed this due to slippage, gaps, or market conditions.
• After 5% loss stop, bot does NOT restart automatically — client must manually start again

RISK NOTICE: Trading involves substantial financial risk. The 1% profit target and 5% loss limit are configured settings, NOT guarantees. Actual losses may exceed the 5% limit due to slippage, gaps, volatility, or execution conditions. Past performance does not guarantee future results. KTS does not guarantee profits or future trading results.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
HOW TO GET KTS10 BOT (CLIENT JOURNEY)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STEP 1 — CLIENT DISCOVERS KTS
Through Meta/Facebook/Instagram advertising, Telegram, WhatsApp, social media, or KTS App/Website.

STEP 2 — CLIENT REGISTRATION
Client registers with KTS and provides: Name, WhatsApp Number, Email Address.

STEP 3 — OPEN ACCOUNT THROUGH KTS
Client opens account through official KTS referral/partner link.

STEP 4 — SEND MT5 ACCOUNT ID
Client sends only MT5 Account ID/Login Number. No trading password required.

STEP 5 — KTS PREPARES THE BOT
We link client's MT5 Account ID with KTS10 BOT and compile account-specific EX5 file.

STEP 6 — CLIENT RECEIVES DEMO BOT
We deliver KTS10 BOT EX5 file with installation instructions/video. Client tests on Demo Account.

STEP 7 — CLIENT DECIDES AFTER TESTING
Client tests KTS10 BOT and checks performance. No need to move to Real Account until satisfied.

STEP 8 — REAL ACCOUNT ACTIVATION
Once satisfied, client opens/connects Real Account through KTS IB/Partner link and sends Real MT5 Account ID.

STEP 9 — FINAL BOT PREPARATION
We link Real MT5 Account ID, compile final EX5 file, and deliver to client.

STEP 10 — CLIENT STARTS USING BOT
Client installs EX5 file on MT5 for Windows. Can run on Windows Laptop/PC or Windows VPS.

SIMPLE PROCESS:
Open Account → Send Account ID → Test on Demo → Activate Real Account → Get EX5 File → Run on Windows/VPS

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RULES & COMMUNICATION
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. ONLY discuss: KTS, KTS10 Bot, MT5 trading bots, forex trading, signals, risk management, trading education, Gold trading, KTS services, and market analysis.
2. NEVER answer about politics, movies, sports, coding, AI, religion, or unrelated topics.
3. NEVER reveal servers, databases, APIs, code, passwords, internal tech details.
4. NEVER respond to abuse — say "Let's keep it professional."
5. NEVER guarantee profits. Always say "Trading involves risk. Past performance does not guarantee future results."
6. When explaining KTS10 Bot, ALWAYS mention the risk notice and clarify that profit targets are configured settings, not guarantees.
7. Always guide clients through the proper process (Demo first, then Real).
8. If user is confused, guide them step by step — be patient and helpful.
9. If user asks about pricing/fees — say "Contact KTS support for current pricing details."
10. If user asks "how to start" or "kaise shuru karun" — give the 5-step process clearly.
11. If user asks about their account/bot status — ask for their MT5 Account ID to check.

LANGUAGE RULES:
- If user writes in English → reply in English
- If user writes in Roman Urdu (or Urdu words) → reply warmly and fluently in Roman Urdu!
- Use emojis: ✅ ❌ 📊 💹 ⚡ 🔥 💡 📈 📉 🎯 🤖 💰
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

        // Check if user needs human support
        if ($this->needsHumanSupport($userMessage)) {
            return [
                'success' => true,
                'message' => $this->getHumanSupportResponse($userName),
                'needs_human_support' => true,
                'model' => $this->model,
                'tokens_used' => 0,
                'response_time_ms' => 0,
                'tools_used' => false,
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

                        // Always enforce authenticated user_id (prevent IDOR from prompt injection)
                        if (in_array($functionName, ['check_user_subscription', 'check_bot_status', 'check_email_log', 'create_support_ticket'])) {
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
                    ->select('id', 'name', 'status', 'mode', 'auto_trade', 'last_connected_at')
                    ->orderByDesc('created_at')
                    ->limit(5)
                    ->get();

                if ($botConfigs->count() > 0) {
                    $context = "MT5 BOT STATUS:\n";
                    foreach ($botConfigs as $bot) {
                        $context .= "- {$bot->name} | Status: {$bot->status} | Mode: {$bot->mode} | Auto Trade: " . ($bot->auto_trade ? 'ON' : 'OFF') . "\n";
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
                'model_used' => $this->model,
                'tokens_used' => 0,
                'response_time_ms' => 0,
            ]);

            AiChatLog::create([
                'user_id' => $userId,
                'role' => 'assistant',
                'message' => $assistantMessage,
                'model_used' => $this->model,
                'tokens_used' => $tokensUsed,
                'response_time_ms' => $responseTime,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to log AI chat: ' . $e->getMessage());
        }
    }

    private function cleanResponse(string $reply): string
    {
        $reply = preg_replace('/<think>[\s\S]*?<\/think>/s', '', $reply);
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

    private function needsHumanSupport(string $message): bool
    {
        $lower = strtolower(trim($message));

        $patterns = [
            'talk to human', 'speak to human', 'human agent', 'real person',
            'talk to agent', 'speak to agent', 'connect me', 'connect to support',
            'customer service', 'support team', 'contact support', 'contact team',
            'help from team', 'help from human', 'need help from', 'talk to someone',
            'speak to someone', 'kisi insaan se', 'human se baat', 'support se baat',
            'agent se baat', 'kisi se baat', 'insaan se baat', 'real insaan',
            'account issue', 'account problem', 'account not working', 'account blocked',
            'payment issue', 'payment problem', 'payment failed', 'refund',
            'cannot login', "can't login", 'login problem', 'login issue',
            'withdrawal issue', 'withdrawal problem', 'deposit issue',
            'not receiving', 'not getting', 'missing', 'lost',
            'complaint', 'grievance', 'escalate', 'manager',
            'supervisor', 'senior', 'higher authority',
            'mujhe insaan se baat karni hai', 'human se baat karo', 'support se help',
            'account mera kaam nahi kar raha', 'mera account nahi khul raha',
            'payment nahi ja raha', 'paisa nahi aaya', 'withdrawal nahi mila',
            'meri complaint hai', 'yeh galat hai', 'ye ghalat hai',
            'bhai yaar insaan bhejo', 'koi banda bhejo', 'real person bhejo',
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function getHumanSupportResponse(?string $userName): string
    {
        $name = $userName ? " {$userName}" : "";
        return "Hello{$name}! 👋\n\nI understand you need help from our support team. I'll connect you with them right away.\n\nPlease tap the button below to start a conversation with our support team. They will assist you personally.\n\n🔄 **Tap the button below to connect:**";
    }
}
