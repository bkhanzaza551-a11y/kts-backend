<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_account_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('demo_email')->nullable();
            $table->string('demo_phone')->nullable();
            $table->string('exness_account_number')->nullable();
            $table->string('account_type')->default('standard'); // standard, pro, raw
            $table->string('deposit_amount')->default('10000'); // USD
            $table->enum('status', ['pending', 'approved', 'rejected', 'linked'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('user_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_account_requests');
    }
};
