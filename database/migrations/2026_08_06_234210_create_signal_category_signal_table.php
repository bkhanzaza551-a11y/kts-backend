<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_category_signal', function (Blueprint $table) {
            $table->foreignId('signal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signal_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['signal_id', 'signal_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signal_category_signal');
    }
};
