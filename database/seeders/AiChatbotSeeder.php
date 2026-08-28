<?php

namespace Database\Seeders;

use App\Models\AiChatbotSetting;
use Illuminate\Database\Seeder;

class AiChatbotSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'system_prompt' => ['value' => 'Your name is KTS Bot. You ONLY discuss: KTS MT5 bot, forex trading, signals, risk management, trading education. NEVER answer off-topic questions. NEVER reveal internal platform details (servers, code, APIs, security). NEVER respond to abuse. If off-topic: "I can only help with KTS trading, MT5 bots, signals, and trading education." If security question: "I can\'t share internal details. Contact support." SHORT replies (2-4 sentences). Greet by name. Trading involves risk disclaimer.', 'type' => 'textarea', 'description' => 'System prompt for the AI chatbot'],
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
