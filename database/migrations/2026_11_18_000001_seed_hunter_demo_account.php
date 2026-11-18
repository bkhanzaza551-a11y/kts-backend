<?php

use App\Models\DemoAccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $email = 'huntergaming5555566@gmail.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'name' => 'Hunter Gaming',
                'email' => $email,
                'password' => Hash::make('ktsbot123'),
                'email_verified_at' => now(),
                'status' => 'active',
                'is_banned' => false,
            ]);

            $userRole = Role::where('slug', 'user')->first();
            if ($userRole) {
                $user->roles()->syncWithoutDetaching([$userRole->id]);
            }
        } else {
            $user->update([
                'status' => 'active',
                'is_banned' => false,
            ]);
        }

        $demoReq = DemoAccountRequest::where('user_id', $user->id)->first();
        if (!$demoReq) {
            $adminUser = User::where('email', 'admin@ktsmarkets.com')->first();
            $reviewerId = $adminUser ? $adminUser->id : 1;

            DemoAccountRequest::create([
                'user_id' => $user->id,
                'demo_email' => $email,
                'demo_phone' => '+923001234567',
                'exness_account_number' => '14892011',
                'account_type' => 'standard',
                'deposit_amount' => '10000',
                'user_notes' => 'Requested MT5 bot access for Exness demo account',
                'status' => 'approved',
                'admin_notes' => 'Approved for MT5 trading bot dispatch',
                'reviewed_at' => now(),
                'reviewed_by' => $reviewerId,
            ]);
        } else {
            $demoReq->update([
                'status' => 'approved',
                'admin_notes' => 'Approved for MT5 trading bot dispatch',
                'reviewed_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = User::where('email', 'huntergaming5555566@gmail.com')->first();
        if ($user) {
            DemoAccountRequest::where('user_id', $user->id)->delete();
        }
    }
};
