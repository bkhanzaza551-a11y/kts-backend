<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        \ = Schema::hasColumn('chat_messages', 'sticker_id');

        if (!\) {
            Schema::table('chat_messages', function (Blueprint \) {
                \->foreignId('sticker_id')->nullable()->after('type')->constrained('chat_stickers')->nullOnDelete();
            });
        }

        if (DB::getDriverName() === 'sqlite') {
            \ = Schema::hasColumn('chat_messages', 'type_new');
            if (!\) {
                DB::statement("ALTER TABLE chat_messages ADD COLUMN type_new VARCHAR(255) DEFAULT 'text'");
                DB::statement("UPDATE chat_messages SET type_new = type");
                DB::statement("ALTER TABLE chat_messages DROP COLUMN type");
                DB::statement("ALTER TABLE chat_messages RENAME COLUMN type_new TO type");
            }
        } elseif (DB::getDriverName() === 'pgsql') {
            // Postgres throws error on MODIFY COLUMN ENUM, change to string safely
            Schema::table('chat_messages', function (Blueprint \) {
                \->string('type')->default('text')->change();
            });
        } else {
            DB::statement("ALTER TABLE chat_messages MODIFY COLUMN type ENUM('text', 'image', 'system', 'sticker') DEFAULT 'text'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_messages', 'sticker_id')) {
            Schema::table('chat_messages', function (Blueprint \) {
                \->dropForeign(['sticker_id']);
                \->dropColumn('sticker_id');
            });
        }
    }
};
