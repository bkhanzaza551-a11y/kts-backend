<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('symbol', 20)->comment('e.g. EURUSD, XAUUSD');
            $table->enum('direction', ['buy', 'sell']);
            $table->decimal('entry_price', 16, 5)->nullable();
            $table->decimal('take_profit', 16, 5)->nullable();
            $table->decimal('stop_loss', 16, 5)->nullable();
            $table->enum('status', ['draft', 'pending', 'active', 'closed', 'cancelled'])->default('draft');
            $table->enum('result', ['win', 'loss', 'breakeven', 'pending'])->default('pending');
            $table->decimal('pips_result', 10, 2)->nullable()->comment('Positive = profit, negative = loss');
            $table->decimal('close_price', 16, 5)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('followers_notified')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('symbol');
            $table->index('direction');
            $table->index('published_at');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
