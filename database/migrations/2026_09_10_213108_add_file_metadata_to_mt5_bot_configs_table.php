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
            if (!Schema::hasColumn('mt5_bot_configs', 'bot_file_name')) {
                $table->string('bot_file_name')->nullable()->after('bot_file_path');
            }
            if (!Schema::hasColumn('mt5_bot_configs', 'bot_file_size')) {
                $table->string('bot_file_size')->nullable()->after('bot_file_name');
            }
            if (!Schema::hasColumn('mt5_bot_configs', 'bot_file_type')) {
                $table->string('bot_file_type')->nullable()->after('bot_file_size');
            }
            if (!Schema::hasColumn('mt5_bot_configs', 'bot_file_uploaded_at')) {
                $table->timestamp('bot_file_uploaded_at')->nullable()->after('bot_file_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->dropColumn(['bot_file_name', 'bot_file_size', 'bot_file_type', 'bot_file_uploaded_at']);
        });
    }
};
