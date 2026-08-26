<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mt5_bot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_config_id')->constrained('mt5_bot_configs')->cascadeOnDelete();
            $table->enum('level', ['info', 'warning', 'error', 'success'])->default('info');
            $table->string('action');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('bot_config_id');
            $table->index('level');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mt5_bot_logs');
    }
};
