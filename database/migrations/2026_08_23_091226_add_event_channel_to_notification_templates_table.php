<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_templates', 'event')) {
                $table->string('event')->nullable()->after('type');
            }
            if (!Schema::hasColumn('notification_templates', 'channel')) {
                $table->string('channel')->nullable()->default('email')->after('event');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_templates', function (Blueprint $table) {
            if (Schema::hasColumn('notification_templates', 'event')) {
                $table->dropColumn('event');
            }
            if (Schema::hasColumn('notification_templates', 'channel')) {
                $table->dropColumn('channel');
            }
        });
    }
};
