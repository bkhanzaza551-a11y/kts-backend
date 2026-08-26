<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->index('is_free');
            $table->index('difficulty');
            $table->index('created_at');
        });

        Schema::table('education_categories', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->index(['course_id', 'sort_order', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['is_free']);
            $table->dropIndex(['difficulty']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('education_categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'sort_order', 'deleted_at']);
        });
    }
};
