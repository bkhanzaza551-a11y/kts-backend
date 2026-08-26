<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('is_active');
            $table->text('pause_reason')->nullable()->after('is_paused');
            $table->timestamp('paused_at')->nullable()->after('pause_reason');
            $table->foreignId('paused_by')->nullable()->constrained('users')->nullOnDelete()->after('paused_at');
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('is_flagged');
            $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            $table->foreignId('pinned_by')->nullable()->constrained('users')->nullOnDelete()->after('pinned_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('chat_badge')->nullable()->after('is_banned');
            $table->string('badge_color')->default('primary')->after('chat_badge');
        });
    }

    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropColumn(['is_paused', 'pause_reason', 'paused_at', 'paused_by']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'pinned_at', 'pinned_by']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['chat_badge', 'badge_color']);
        });
    }
};
