<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_action_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name');
            $table->json('arguments');
            $table->unsignedBigInteger('agent_user_id')->nullable();
            $table->string('status'); // started, completed, failed
            $table->json('result')->nullable();
            $table->timestamps();

            $table->index('tool_name');
            $table->index('status');
            $table->index('created_at');
        });

        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type'); // confirmation, reset_password, otp, welcome, support
            $table->string('status')->default('sent'); // sent, delivered, failed
            $table->string('resent_by')->nullable(); // ai_agent, admin, system
            $table->timestamps();

            $table->index('user_id');
            $table->index('type');
            $table->index('status');
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('subject');
            $table->text('description');
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->string('source')->default('manual'); // manual, ai_chatbot, email
            $table->boolean('created_by_agent')->default(false);
            $table->unsignedBigInteger('agent_user_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_action_logs');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('support_tickets');
    }
};
