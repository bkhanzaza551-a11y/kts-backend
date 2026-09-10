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
        try {
            $users = \App\Models\User::whereNull('last_login_at')->get();
            foreach ($users as $user) {
                $latestActivity = \App\Models\ActivityLog::where('user_id', $user->id)->latest()->value('created_at');
                $user->last_login_at = $latestActivity ?: $user->updated_at ?: $user->created_at ?: now();
                if (empty($user->last_login_ip)) {
                    $user->last_login_ip = '127.0.0.1';
                }
                $user->save();
            }
        } catch (\Throwable $e) {
            // Ignored if DB table not ready
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
