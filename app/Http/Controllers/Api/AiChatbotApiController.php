<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatbotApiController extends Controller
{
    public function __construct(
        private AiChatbotService $chatbotService
    ) {}

    /**
     * Send a message to AI chatbot.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_history' => 'nullable',
        ]);

        if (!$this->chatbotService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Chatbot is currently disabled. Please contact admin.',
            ], 503);
        }

        $userId = $request->user()?->id;

        $history = $request->input('conversation_history');
        if (is_array($history)) {
            $history = json_encode($history);
        }

        $result = $this->chatbotService->chat(
            $request->input('message'),
            $history,
            $userId,
            $request->user()?->name
        );

        $statusCode = $result['success'] ? 200 : 500;

        return response()->json([
            'success' => $result['success'],
            'data' => [
                'user_message' => $request->input('message'),
                'response' => $result['message'] ?? $result['message'] ?? '',
                'model' => $result['model'] ?? '',
                'tokens_used' => $result['tokens_used'] ?? 0,
                'response_time_ms' => $result['response_time_ms'] ?? 0,
                'tools_used' => $result['tools_used'] ?? false,
                'timestamp' => now()->toIso8601String(),
            ],
        ], $statusCode);
    }

    /**
     * Get chatbot status and info.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'enabled' => $this->chatbotService->isEnabled(),
            'models' => $this->chatbotService->getAvailableModels(),
        ]);
    }

    /**
     * Get chatbot usage stats.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'stats' => $this->chatbotService->getStats(),
        ]);
    }

    /**
     * Report an AI chatbot message.
     */
    public function report(Request $request): JsonResponse
    {
        $request->validate([
            'message_id' => 'required|string',
            'reason' => 'required|string|in:inappropriate,misleading,spam,other',
        ]);

        $user = $request->user();

        \App\Models\AiChatLog::create([
            'user_id' => $user?->id,
            'message' => '[REPORTED] Message ID: ' . $request->input('message_id'),
            'response' => 'Reason: ' . $request->input('reason'),
            'model' => 'report',
            'is_flagged' => true,
            'flag_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report submitted successfully. Thank you for your feedback.',
        ]);
    }
}
