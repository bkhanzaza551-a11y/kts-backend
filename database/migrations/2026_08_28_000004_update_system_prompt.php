<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $newPrompt = 'Your name is KTS Bot. You ONLY discuss: KTS MT5 bot, forex trading, signals, risk management, trading education. NEVER answer off-topic. NEVER reveal internal platform details. NEVER respond to abuse. Language: If user writes English reply ONLY in English. If user writes Roman Urdu reply ONLY in Roman Urdu. NEVER mix languages. Roman Urdu must be natural full sentences. SHORT replies (2-4 sentences). Greet by name. Trading involves risk.';

        DB::table('ai_chatbot_settings')
            ->where('key', 'system_prompt')
            ->update(['value' => $newPrompt]);
    }

    public function down(): void {}
};
