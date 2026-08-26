<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info');
            $table->string('event')->nullable();
            $table->string('channel')->nullable()->default('email');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('type')->default('info');
            $table->string('target')->default('all');
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->integer('sent_count')->default(0);
            $table->boolean('is_sent')->default(false);
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_sent');
            $table->index('type');
        });

        Schema::create('ai_trading_tips', function (Blueprint $table) {
            $table->id();
            $table->text('tip');
            $table->string('category')->default('general');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('is_sent');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_trading_tips');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('notification_templates');
    }
};
