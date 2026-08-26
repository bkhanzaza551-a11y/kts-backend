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
            'conversation_history' => 'nullable|string|max:50000',
        ]);

        if (!$this->chatbotService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AI Chatbot is currently disabled. Please contact admin.',
            ], 503);
        }

        $userId = $request->user()?->id;

        $result = $this->chatbotService->chat(
            $request->input('message'),
            $request->input('conversation_history'),
            $userId
        );

        return response()->json($result, $result['success'] ? 200 : 500);
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
}
