<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Signal;
use Tests\TestCase;

class SignalsIntegrationTest extends TestCase
{
    public function test_guest_cannot_access_mobile_signals_api(): void
    {
        $response = $this->getJson('/api/v1/signals');
        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_signals_api(): void
    {
        $user = User::first();
        if ($user) {
            $token = $user->createToken('test-token')->plainTextToken;

            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/v1/signals');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'data',
            ]);
        } else {
            $this->markTestSkipped('No user found in database.');
        }
    }

    public function test_active_signal_is_listed_in_mobile_api(): void
    {
        $user = User::first();
        if ($user) {
            $token = $user->createToken('test-token')->plainTextToken;

            $signal = Signal::firstOrCreate(
                ['symbol' => 'XAUUSD', 'status' => 'active'],
                [
                    'title' => 'XAUUSD Buy Signal',
                    'direction' => 'buy',
                    'entry_price' => 2650.00,
                    'stop_loss' => 2640.00,
                    'take_profit' => 2660.00,
                    'result' => 'pending',
                    'created_by' => $user->id,
                    'published_at' => now(),
                ]
            );

            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/v1/signals');

            $response->assertStatus(200);
            $response->assertSee('XAUUSD');
        } else {
            $this->markTestSkipped('No user found in database.');
        }
    }
}
