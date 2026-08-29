<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_number')->unique();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('subject');
                $table->text('description');
                $table->string('status')->default('open');
                $table->string('priority')->default('medium');
                $table->string('source')->default('manual');
                $table->boolean('created_by_agent')->default(false);
                $table->unsignedBigInteger('agent_user_id')->nullable();
                $table->string('attachment')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Add missing columns to existing table
            Schema::table('support_tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('support_tickets', 'ticket_number')) {
                    $table->string('ticket_number')->unique()->after('id');
                }
                if (!Schema::hasColumn('support_tickets', 'source')) {
                    $table->string('source')->default('manual')->after('status');
                }
                if (!Schema::hasColumn('support_tickets', 'created_by_agent')) {
                    $table->boolean('created_by_agent')->default(false)->after('source');
                }
                if (!Schema::hasColumn('support_tickets', 'agent_user_id')) {
                    $table->unsignedBigInteger('agent_user_id')->nullable()->after('created_by_agent');
                }
                if (!Schema::hasColumn('support_tickets', 'attachment')) {
                    $table->string('attachment')->nullable()->after('priority');
                }
                if (!Schema::hasColumn('support_tickets', 'soft_deletes')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
