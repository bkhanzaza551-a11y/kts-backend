<?php

namespace Tests\Feature;

use App\Models\Mt5BotConfig;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminMt5BotTest extends TestCase
{
    protected function getSuperAdmin(): User
    {
        $admin = User::where('email', 'admin@ktsmarkets.com')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'KTS Admin',
                'email' => 'admin@ktsmarkets.com',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
            ]);
        }

        Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Full administrative access']
        );

        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        return $admin;
    }

    public function test_admin_can_view_and_update_mt5_bot_config(): void
    {
        $admin = $this->getSuperAdmin();

        $bot = Mt5BotConfig::firstOrCreate(
            ['id' => 1],
            [
                'name' => '10 Pips Scalper Bot',
                'mt5_account_number' => '1000999',
                'mt5_server' => 'KTS-LiveServer',
                'mode' => 'demo',
                'status' => 'active',
                'take_profit_pips' => 10,
                'stop_loss_pips' => 20,
                'max_daily_trades' => 50,
                'created_by' => $admin->id,
            ]
        );

        // 1. Admin views MT5 Bot dashboard
        $viewResponse = $this->actingAs($admin, 'web')->get(route('admin.mt5-bot.index'));
        $viewResponse->assertStatus(200);

        // 2. Admin updates MT5 Bot parameters
        $updateResponse = $this->actingAs($admin, 'web')->put(route('admin.mt5-bot.update', $bot), [
            'name' => '10 Pips Pro EA Updated',
            'mt5_account_number' => $bot->mt5_account_number,
            'mt5_server' => 'KTS-DemoServer-V2',
            'mode' => 'demo',
            'status' => 'active',
            'auto_trade' => 1,
            'take_profit_pips' => 15,
            'stop_loss_pips' => 25,
            'max_daily_trades' => 100,
            'max_daily_loss' => 500,
            'base_balance' => 1000,
            'base_lot_size' => 0.01,
        ]);
        $updateResponse->assertSessionHasNoErrors();

        $bot->refresh();
        $this->assertEquals('10 Pips Pro EA Updated', $bot->name);
        $this->assertEquals(15, (int) $bot->take_profit_pips);
        $this->assertEquals('KTS-DemoServer-V2', $bot->mt5_server);

        // 3. Mobile API reflects the updated configuration
        $mobileResponse = $this->getJson('/api/v1/bot');
        $mobileResponse->assertStatus(200);
        $this->assertEquals('10 Pips Pro EA Updated', $mobileResponse->json('data.name'));
    }

    public function test_admin_can_toggle_bot_auto_trade(): void
    {
        $admin = $this->getSuperAdmin();
        $bot = Mt5BotConfig::first();

        if ($bot) {
            $initialState = (bool) $bot->auto_trade;
            $toggleResponse = $this->actingAs($admin, 'web')->patch(route('admin.mt5-bot.toggle-auto-trade', $bot));
            $toggleResponse->assertSessionHasNoErrors();

            $bot->refresh();
            $this->assertNotEquals($initialState, (bool) $bot->auto_trade);
        }
    }
}
