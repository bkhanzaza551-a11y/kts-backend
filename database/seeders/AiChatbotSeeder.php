<?php

namespace Database\Seeders;

use App\Models\AiChatbotSetting;
use Illuminate\Database\Seeder;

class AiChatbotSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'system_prompt' => ['value' => 'Your name is KTS Bot. You are the official AI assistant of KTS (Khan Trading School). You have ACCESS to live platform data. KTS10 Bot: automated MT5 trading bot for Gold/XAUUSD. 1% profit target, 5% loss limit. Client journey: Open Account → Send MT5 ID → Test Demo → Activate Real → Get EX5 → Run on Windows/VPS. Services: KTS10 Bot, Forex Signals, Technical Analysis, Premium Group, One-to-One Support, Forex Training. NEVER reveal internal details. NEVER guarantee profits. Trading involves risk. Format with line breaks, bullet points, emojis.', 'type' => 'textarea', 'description' => 'System prompt for the AI chatbot'],
            'model' => ['value' => 'qwen/qwen3.6-27b', 'type' => 'select', 'description' => 'AI model to use (Groq)'],
            'max_tokens' => ['value' => '2048', 'type' => 'number', 'description' => 'Maximum tokens per response'],
            'temperature' => ['value' => '0.7', 'type' => 'number', 'description' => 'Response temperature (0-2)'],
            'is_enabled' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable/disable AI chatbot'],
            'tools_enabled' => ['value' => '1', 'type' => 'boolean', 'description' => 'Enable AI agent tools (email check, resend, etc.)'],
            'groq_api_key' => ['value' => env('GROQ_API_KEY', ''), 'type' => 'password', 'description' => 'Groq API key'],
            'openai_api_key' => ['value' => '', 'type' => 'password', 'description' => 'OpenAI API key'],
        ];

        foreach ($defaults as $key => $data) {
            AiChatbotSetting::updateOrCreate(
                ['key' => $key],
                $data
            );
        }
    }
}
