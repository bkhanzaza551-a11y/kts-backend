<?php

namespace Database\Seeders;

use App\Models\AdminSecurityCode;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSecurityCodeSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@kts10pipsbots.com')->first();

        if ($admin) {
            $result = AdminSecurityCode::generateFor($admin, 'Default Security Code');

            $this->command->info("Security code for {$admin->email}: {$result['code']}");
            $this->command->info("⚠ SAVE THIS CODE - It will not be shown again!");
        }
    }
}
