<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mt5_bot_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('mt5_account_number');
            $table->string('mt5_server');
            $table->text('mt5_password_encrypted');
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->enum('status', ['active', 'inactive', 'error'])->default('inactive');
            $table->enum('mode', ['live', 'demo', 'backtest'])->default('demo');
            $table->boolean('auto_trade')->default(false);
            $table->decimal('lot_size', 10, 2)->default(0.01);
            $table->decimal('max_lot_size', 10, 2)->default(1.00);
            $table->decimal('take_profit_pips', 10, 2)->default(10.00);
            $table->decimal('stop_loss_pips', 10, 2)->default(20.00);
            $table->decimal('max_daily_trades', 10, 0)->default(10);
            $table->decimal('max_daily_loss', 12, 2)->default(100.00);
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('equity', 12, 2)->default(0);
            $table->decimal('total_profit', 12, 2)->default(0);
            $table->decimal('total_loss', 12, 2)->default(0);
            $table->integer('total_trades')->default(0);
            $table->integer('winning_trades')->default(0);
            $table->integer('losing_trades')->default(0);
            $table->timestamp('last_connected_at')->nullable();
            $table->timestamp('last_trade_at')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mt5_bot_configs');
    }
};
