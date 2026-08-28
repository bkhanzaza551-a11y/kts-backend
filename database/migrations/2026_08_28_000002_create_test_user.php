<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('users')->where('email', 'test@kts.com')->exists();
        if (!$exists) {
            DB::table('users')->insert([
                'name' => 'Test User',
                'email' => 'test@kts.com',
                'password' => Hash::make('Test123!'),
                'status' => 'active',
                'is_admin' => 0,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'test@kts.com')->delete();
    }
};
