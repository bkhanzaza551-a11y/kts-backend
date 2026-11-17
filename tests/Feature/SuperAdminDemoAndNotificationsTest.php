<?php

namespace Tests\Feature;

use App\Models\AdminNotification;
use App\Models\DemoAccountRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminDemoAndNotificationsTest extends TestCase
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

    public function test_demo_account_approval_and_rejection(): void
    {
        $admin = $this->getSuperAdmin();

        $user = User::create([
            'name' => 'Demo Trader',
            'email' => 'demotrader_' . uniqid() . '@ktsmarkets.com',
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $demoRequest = DemoAccountRequest::create([
            'user_id' => $user->id,
            'broker' => 'Exness',
            'experience_level' => 'intermediate',
            'status' => 'pending',
            'notes' => 'Testing bot with 1000 USD demo balance',
        ]);

        // 1. Admin views demo account requests
        $indexResponse = $this->actingAs($admin, 'web')->get(route('admin.demo-accounts.index'));
        $indexResponse->assertStatus(200);

        // 2. Admin approves demo account request
        $approveResponse = $this->actingAs($admin, 'web')->post(route('admin.demo-accounts.approve', $demoRequest), [
            'admin_notes' => 'Approved for demo trading bot testing',
        ]);
        $approveResponse->assertSessionHasNoErrors();

        $demoRequest->refresh();
        $this->assertEquals('approved', $demoRequest->status);

        // 3. Test Rejection flow on another request
        $rejectDemoRequest = DemoAccountRequest::create([
            'user_id' => $user->id,
            'broker' => 'UnregulatedBroker',
            'status' => 'pending',
        ]);

        $rejectResponse = $this->actingAs($admin, 'web')->post(route('admin.demo-accounts.reject', $rejectDemoRequest), [
            'admin_notes' => 'Broker not supported for institutional EA execution',
        ]);
        $rejectResponse->assertSessionHasNoErrors();

        $rejectDemoRequest->refresh();
        $this->assertEquals('rejected', $rejectDemoRequest->status);
    }

    public function test_admin_broadcast_notification_creation(): void
    {
        $admin = $this->getSuperAdmin();

        // 1. Admin views notification center
        $indexResponse = $this->actingAs($admin, 'web')->get(route('admin.notifications.index'));
        $indexResponse->assertStatus(200);

        // 2. Admin creates broadcast notification
        $createResponse = $this->actingAs($admin, 'web')->post(route('admin.notifications.store'), [
            'title' => 'High Impact US CPI Alert',
            'body' => 'US CPI release at 13:30 GMT. Expect elevated volatility on USD pairs.',
            'type' => 'warning',
            'target' => 'all',
        ]);
        $createResponse->assertSessionHasNoErrors();

        $notification = AdminNotification::latest('id')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('High Impact US CPI Alert', $notification->title);
        $this->assertEquals('warning', $notification->type);
    }
}
