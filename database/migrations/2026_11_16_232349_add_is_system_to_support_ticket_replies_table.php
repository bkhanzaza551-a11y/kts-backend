<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('support_ticket_replies', function (Blueprint $table) {
            if (!Schema::hasColumn('support_ticket_replies', 'is_system')) {
                $table->boolean('is_system')->default(false);
            }
            if (!Schema::hasColumn('support_ticket_replies', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_replies', function (Blueprint $table) {
            if (Schema::hasColumn('support_ticket_replies', 'is_system')) {
                $table->dropColumn('is_system');
            }
            if (Schema::hasColumn('support_ticket_replies', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
