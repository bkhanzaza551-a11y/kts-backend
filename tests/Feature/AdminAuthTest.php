<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_login_page(): void
    {
        $response = $this->get(route('admin.login'));
        $response->assertStatus(200);
        $response->assertSee('KTS Markets');
    }

    public function test_admin_login_with_invalid_credentials_fails(): void
    {
        $response = $this->post(route('admin.login.post'), [
            'email' => 'wrongadmin@ktsmarkets.com',
            'password' => 'WrongPassword999!',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest('web');
    }

    public function test_admin_can_access_dashboard_when_authenticated(): void
    {
        $admin = User::first();
        if ($admin) {
            $response = $this->actingAs($admin, 'web')->get(route('admin.dashboard'));
            $response->assertStatus(200);
            $response->assertSee('KTS Markets');
        } else {
            $this->markTestSkipped('No user found in test database.');
        }
    }
}
