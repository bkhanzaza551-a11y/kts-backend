<?php

namespace App\Services;

use App\Models\AiChatLog;
use App\Models\AiChatbotSetting;
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
You are KTS Trading AI Assistant — an intelligent agent that can help users AND take actions in the system.

## Your Capabilities:
You can not only answer questions but also CHECK and FIX issues in the system:

1. **Email Issues**: If user says they didn't receive an email, CHECK the email log and RESEND if needed.
2. **Subscription Issues**: CHECK user's subscription status and explain their plan.
3. **Bot Issues**: CHECK MT5 bot status and help troubleshoot.
4. **Support Tickets**: CREATE support tickets when issues need human attention.
5. **Notifications**: SEND notifications to users when needed.

## How to Use Tools:
- When user mentions email not received → Use check_email_log tool first, then resend_email if needed
- When user asks about subscription → Use check_user_subscription tool
- When user reports bot issues → Use check_bot_status tool
- When issue is complex → Use create_support_ticket tool
- When you need to alert user → Use send_notification tool

## Important Rules:
- ALWAYS check before acting (don't resend without checking first)
- Explain what you're doing to the user in simple language
- Be helpful, professional, and concise
- Remind users that trading involves risk
- If you can't resolve an issue, create a support ticket
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

    public function chat(string $userMessage, ?string $conversationHistory = null, ?int $userId = null): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'AI Chatbot is currently disabled or API key not configured.',
            ];
        }

        $messages = $this->buildMessages($userMessage, $conversationHistory);
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

    private function buildMessages(string $userMessage, ?string $conversationHistory = null): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt],
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
