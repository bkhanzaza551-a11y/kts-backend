<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_sticker_packs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });

        Schema::create('chat_stickers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pack_id')->constrained('chat_sticker_packs')->cascadeOnDelete();
            $table->string('name');
            $table->string('image_url');
            $table->string('file_size')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('pack_id');
            $table->index('is_active');
            $table->index('sort_order');
            $table->index('usage_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_stickers');
        Schema::dropIfExists('chat_sticker_packs');
    }
};
