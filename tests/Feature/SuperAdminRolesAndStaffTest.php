<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminRolesAndStaffTest extends TestCase
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

        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Administrator', 'description' => 'Full administrative access']
        );

        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        return $admin;
    }

    public function test_super_admin_can_view_staff_and_roles_index(): void
    {
        $admin = $this->getSuperAdmin();

        $staffResponse = $this->actingAs($admin, 'web')->get(route('admin.staff.index'));
        $staffResponse->assertStatus(200);

        $rolesResponse = $this->actingAs($admin, 'web')->get(route('admin.roles.index'));
        $rolesResponse->assertStatus(200);
    }

    public function test_super_admin_can_view_permissions_list(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.permissions.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_staff_member(): void
    {
        $admin = $this->getSuperAdmin();

        $supportRole = Role::firstOrCreate(
            ['slug' => 'support-agent'],
            ['name' => 'Support Agent', 'description' => 'Helpdesk access']
        );

        $staffEmail = 'staff_' . uniqid() . '@ktsmarkets.com';

        $response = $this->actingAs($admin, 'web')->post(route('admin.staff.store'), [
            'name' => 'New Staff Agent',
            'email' => $staffEmail,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'roles' => [$supportRole->id],
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();

        $newStaff = User::where('email', $staffEmail)->first();
        $this->assertNotNull($newStaff);
        $this->assertEquals('New Staff Agent', $newStaff->name);
        $this->assertTrue($newStaff->hasRole('support-agent'));
    }

    public function test_unauthorized_user_cannot_access_roles_crud(): void
    {
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $response = $this->actingAs($regularUser, 'web')->get(route('admin.roles.index'));
        // Non-admin without permissions should be redirected or forbidden (403 or redirect)
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }
}
