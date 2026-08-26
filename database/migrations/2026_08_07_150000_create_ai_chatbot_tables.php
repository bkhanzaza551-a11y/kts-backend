<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('message');
            $table->string('model_used')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('created_at');
            $table->index('is_flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_logs');
        Schema::dropIfExists('ai_chatbot_settings');
    }
};
