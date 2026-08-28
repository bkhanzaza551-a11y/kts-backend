<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_chatbot_settings')
            ->where('key', 'model')
            ->update(['value' => 'qwen/qwen3.6-27b']);
    }

    public function down(): void
    {
        DB::table('ai_chatbot_settings')
            ->where('key', 'model')
            ->update(['value' => 'llama-3.1-8b-instant']);
    }
};
