<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_account_settings', function (Blueprint $table) {
            $table->id();
            $table->string('referral_link')->nullable();
            $table->string('page_title')->default('How to Create Exness Demo Account');
            $table->text('page_description')->nullable();
            $table->json('instructions')->nullable(); // Array of instruction steps
            $table->json('account_types')->nullable(); // Array of account type options
            $table->json('deposit_amounts')->nullable(); // Array of deposit amount options
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_account_settings');
    }
};
