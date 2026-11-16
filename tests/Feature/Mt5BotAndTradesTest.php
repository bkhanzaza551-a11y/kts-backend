<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Mt5BotConfig;
use App\Models\Mt5BotTrade;
use Tests\TestCase;

class Mt5BotAndTradesTest extends TestCase
{
    public function test_public_or_mobile_user_can_access_bot_config(): void
    {
        $response = $this->getJson('/api/v1/bot');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
        ]);
    }

    public function test_authenticated_user_can_access_bot_trades(): void
    {
        $user = User::first();
        if ($user) {
            $token = $user->createToken('test-token')->plainTextToken;

            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/v1/bot/trades');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'data',
            ]);
        } else {
            $this->markTestSkipped('No user found in test database.');
        }
    }
}
