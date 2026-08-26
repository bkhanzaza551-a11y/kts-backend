<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->string('bot_file_path')->nullable()->after('mt5_server');
            
            // In SQLite, dropping columns is not always supported without doctrine/dbal.
            // But Laravel 11 handles it natively. Let's drop the columns.
            $table->dropColumn(['mt5_password_encrypted', 'lot_size', 'max_lot_size']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->dropColumn('bot_file_path');
            $table->text('mt5_password_encrypted')->nullable();
            $table->decimal('lot_size', 8, 2)->default(0.01);
            $table->decimal('max_lot_size', 8, 2)->default(1.00);
        });
    }
};
