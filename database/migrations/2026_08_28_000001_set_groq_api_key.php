<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $apiKey = env('GROQ_API_KEY', '');
        if (!empty($apiKey)) {
            DB::table('ai_chatbot_settings')->updateOrInsert(
                ['key' => 'groq_api_key'],
                ['value' => $apiKey, 'type' => 'password', 'description' => 'Groq API key']
            );
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'groq_api_key'],
                ['value' => $apiKey, 'type' => 'password', 'description' => 'Groq API key', 'group' => 'api_keys']
            );
        }
    }

    public function down(): void {}
};
