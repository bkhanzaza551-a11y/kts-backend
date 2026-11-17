<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Signal;
use App\Models\SignalCategory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSignalsLifecycleTest extends TestCase
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

    public function test_admin_can_create_category_and_signal(): void
    {
        $admin = $this->getSuperAdmin();

        // 1. Create Category
        $catResponse = $this->actingAs($admin, 'web')->post(route('admin.signal-categories.store'), [
            'name' => 'Forex Majors ' . uniqid(),
            'slug' => 'forex-majors-' . uniqid(),
            'description' => 'Major forex pairs signals',
            'color' => '#D4A843',
            'is_active' => true,
        ]);
        $catResponse->assertSessionHasNoErrors();

        $category = SignalCategory::latest('id')->first();
        $this->assertNotNull($category);

        // 2. Create Signal (Draft or Pending)
        $sigResponse = $this->actingAs($admin, 'web')->post(route('admin.signals.store'), [
            'title' => 'EURUSD Breakout Buy ' . uniqid(),
            'symbol' => 'EURUSD',
            'direction' => 'buy',
            'entry_price' => 1.0850,
            'stop_loss' => 1.0800,
            'take_profit' => 1.0920,
            'timeframe' => '1H',
            'status' => 'pending',
            'risk_level' => 'medium',
            'notes' => 'Bullish continuation on 1H chart',
            'categories' => [$category->id],
        ]);
        $sigResponse->assertSessionHasNoErrors();

        $signal = Signal::latest('id')->first();
        $this->assertNotNull($signal);
        $this->assertEquals('EURUSD', $signal->symbol);
        $this->assertEquals('buy', $signal->direction);
        $this->assertEquals('pending', $signal->status);

        // 3. Admin Publishes Signal
        $pubResponse = $this->actingAs($admin, 'web')->post(route('admin.signals.publish', $signal));
        $pubResponse->assertSessionHasNoErrors();

        $signal->refresh();
        $this->assertEquals('active', $signal->status);

        // 4. Mobile User API receives the active signal
        $mobileUser = User::create([
            'name' => 'Trader Mobile',
            'email' => 'trader_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);
        $token = $mobileUser->createToken('test')->plainTextToken;

        $mobileListResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/signals');
        $mobileListResponse->assertStatus(200);
        $signalIds = collect($mobileListResponse->json('data.data'))->pluck('id')->toArray();
        $this->assertContains($signal->id, $signalIds);

        // 5. Admin Closes Signal with Win
        $closeResponse = $this->actingAs($admin, 'web')->post(route('admin.signals.close', $signal), [
            'result' => 'win',
            'pips_result' => 70,
            'close_price' => 1.0920,
        ]);
        $closeResponse->assertSessionHasNoErrors();

        $signal->refresh();
        $this->assertEquals('closed', $signal->status);
        $this->assertEquals('win', $signal->result);
        $this->assertEquals(70, (int) $signal->pips_result);

        // 6. Mobile User API confirms closed status
        $mobileDetailResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/signals/{$signal->id}");
        $mobileDetailResponse->assertStatus(200);
        $this->assertEquals('closed', $mobileDetailResponse->json('data.status'));
        $this->assertEquals('win', $mobileDetailResponse->json('data.result'));
    }

    public function test_invalid_signal_validation_rejected(): void
    {
        $admin = $this->getSuperAdmin();

        // For BUY signal: stop loss MUST be lower than entry, take profit MUST be higher
        // Passing invalid stop loss (higher than entry)
        $response = $this->actingAs($admin, 'web')->post(route('admin.signals.store'), [
            'title' => 'Invalid Signal',
            'symbol' => 'GBPUSD',
            'direction' => 'buy',
            'entry_price' => 1.2500,
            'stop_loss' => 1.2600, // Invalid: SL is higher than entry for BUY
            'take_profit' => 1.2700,
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors(['stop_loss']);
    }
}
