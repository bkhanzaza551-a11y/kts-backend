<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mt5_bot_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_config_id')->constrained('mt5_bot_configs')->cascadeOnDelete();
            $table->string('ticket')->unique();
            $table->string('symbol');
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('volume', 10, 2);
            $table->decimal('open_price', 12, 5);
            $table->decimal('close_price', 12, 5)->nullable();
            $table->decimal('stop_loss', 12, 5)->nullable();
            $table->decimal('take_profit', 12, 5)->nullable();
            $table->decimal('profit', 12, 2)->default(0);
            $table->decimal('commission', 12, 2)->default(0);
            $table->decimal('swap', 12, 2)->default(0);
            $table->enum('status', ['open', 'closed', 'pending'])->default('open');
            $table->string('strategy')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('bot_config_id');
            $table->index('symbol');
            $table->index('status');
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mt5_bot_trades');
    }
};
