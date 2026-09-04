<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->string('demo_server', 100)->nullable()->after('whatsapp_number');
            $table->string('demo_account', 50)->nullable()->after('demo_server');
            $table->string('demo_email', 100)->nullable()->after('demo_account');
            $table->string('demo_phone', 20)->nullable()->after('demo_email');
            $table->decimal('demo_deposit', 15, 2)->default(10000)->after('demo_phone');
        });
    }

    public function down(): void
    {
        Schema::table('mt5_bot_configs', function (Blueprint $table) {
            $table->dropColumn(['demo_server', 'demo_account', 'demo_email', 'demo_phone', 'demo_deposit']);
        });
    }
};