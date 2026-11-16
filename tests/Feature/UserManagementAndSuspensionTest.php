<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class UserManagementAndSuspensionTest extends TestCase
{
    public function test_authenticated_user_can_access_profile(): void
    {
        $user = User::first();
        if ($user) {
            $token = $user->createToken('test-token')->plainTextToken;

            $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                ->getJson('/api/v1/profile');

            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'data',
            ]);
        } else {
            $this->markTestSkipped('No user found in database.');
        }
    }

    public function test_suspended_user_is_forbidden_from_api(): void
    {
        $user = User::factory()->create([
            'name' => 'Suspended Test User',
            'email' => 'suspended_test_' . time() . '@example.com',
            'status' => 'suspended',
            'is_banned' => true,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/profile');

        $response->assertStatus(403);
        $user->delete();
    }
}
