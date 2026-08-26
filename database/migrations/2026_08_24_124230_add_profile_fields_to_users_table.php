<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('demo_account_id')->nullable()->after('avatar');
            $table->string('demo_account_server')->nullable()->after('demo_account_id');
            $table->string('real_account_id')->nullable()->after('demo_account_server');
            $table->string('real_account_server')->nullable()->after('real_account_id');
            $table->string('broker_name')->nullable()->after('real_account_server');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['demo_account_id', 'demo_account_server', 'real_account_id', 'real_account_server', 'broker_name']);
        });
    }
};
