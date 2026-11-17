<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\LegalPage;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminSettingsAndAuditTest extends TestCase
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

    public function test_admin_can_view_and_update_system_settings(): void
    {
        $admin = $this->getSuperAdmin();

        // 1. Admin views settings page
        $indexResponse = $this->actingAs($admin, 'web')->get(route('admin.settings.index'));
        $indexResponse->assertStatus(200);

        // 2. Admin updates system settings
        $updateResponse = $this->actingAs($admin, 'web')->put(route('admin.settings.update'), [
            'app_name' => 'KTS Markets Pro Terminal',
            'support_email' => 'support@ktsmarkets.com',
        ]);
        $updateResponse->assertSessionHasNoErrors();

        $appNameSetting = SystemSetting::where('key', 'app_name')->first();
        $this->assertNotNull($appNameSetting);
        $this->assertEquals('KTS Markets Pro Terminal', $appNameSetting->value);
    }

    public function test_admin_can_view_audit_logs(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'web')->get(route('admin.audit-logs.index'));
        $response->assertStatus(200);
        $response->assertSee('Audit Logs');
    }

    public function test_admin_can_manage_legal_pages(): void
    {
        $admin = $this->getSuperAdmin();

        $legalPage = LegalPage::firstOrCreate(
            ['slug' => 'privacy-policy'],
            [
                'title' => 'Privacy Policy',
                'content' => 'KTS Markets strictly preserves user confidentiality.',
                'is_published' => true,
            ]
        );

        $indexResponse = $this->actingAs($admin, 'web')->get(route('admin.legal-pages.index'));
        $indexResponse->assertStatus(200);

        $editResponse = $this->actingAs($admin, 'web')->get(route('admin.legal-pages.edit', $legalPage->slug));
        $editResponse->assertStatus(200);

        $updateResponse = $this->actingAs($admin, 'web')->put(route('admin.legal-pages.update', $legalPage->slug), [
            'title' => 'Privacy Policy Updated',
            'content' => 'Updated privacy policy content regarding institutional compliance.',
            'is_published' => true,
        ]);
        $updateResponse->assertSessionHasNoErrors();

        $legalPage->refresh();
        $this->assertEquals('Privacy Policy Updated', $legalPage->title);
    }
}
