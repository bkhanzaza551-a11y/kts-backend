<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('fcm_token');
            $table->string('platform')->default('android');
            $table->string('device_name')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('fcm_token');
            $table->index('user_id');
            $table->index('is_active');
            $table->unique(['user_id', 'fcm_token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
