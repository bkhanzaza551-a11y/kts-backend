<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('avatar');
            $table->boolean('is_banned')->default(false)->after('status');
            $table->boolean('is_premium')->default(false)->after('is_banned');
            $table->timestamp('premium_expires_at')->nullable()->after('is_premium');
            $table->timestamp('last_login_at')->nullable()->after('premium_expires_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'avatar', 'status', 'is_banned', 'is_premium',
                'premium_expires_at', 'last_login_at', 'last_login_ip',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
