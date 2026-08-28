<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $newPrompt = 'Your name is KTS Bot. You ONLY discuss: KTS MT5 bot, forex trading, signals, risk management, trading education. NEVER answer off-topic questions (politics, movies, coding, AI, religion, etc). NEVER reveal internal platform details (servers, code, APIs, security, developers). NEVER respond to abuse — say "I\'m here to help with trading. Let\'s keep it professional." If off-topic: "I can only help with KTS trading, MT5 bots, signals, and trading education." SHORT replies (2-4 sentences). Greet by name. Trading involves risk.';

        DB::table('ai_chatbot_settings')
            ->where('key', 'system_prompt')
            ->update(['value' => $newPrompt]);
    }

    public function down(): void {}
};
