<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->decimal('base_balance', 10, 2)->default(100)->after('max_daily_loss');
            $table->decimal('base_lot_size', 5, 2)->default(0.1)->after('base_balance');
        });
    }

    public function down(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->dropColumn(['base_balance', 'base_lot_size']);
        });
    }
};
