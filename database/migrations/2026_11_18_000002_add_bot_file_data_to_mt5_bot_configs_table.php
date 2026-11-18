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
            if (!Schema::hasColumn('mt5_bot_configs', 'bot_file_data')) {
                // longText holds up to 4GB in MySQL/PostgreSQL, perfect for .ex4 / .ex5 files on Vercel
                $table->longText('bot_file_data')->nullable()->after('bot_file_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            if (Schema::hasColumn('mt5_bot_configs', 'bot_file_data')) {
                $table->dropColumn('bot_file_data');
            }
        });
    }
};
