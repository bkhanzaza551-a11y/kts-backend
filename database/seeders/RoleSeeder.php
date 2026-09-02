<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $allPermissions = Permission::pluck('id')->toArray();

        // Super Admin
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full unrestricted access to all modules',
                'is_system' => true,
            ]
        );
        $superAdmin->permissions()->sync($allPermissions);

        // Admin
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrative access with most permissions',
                'is_system' => true,
            ]
        );
        $admin->permissions()->sync($allPermissions);

        // Signal Manager
        $signalManager = Role::updateOrCreate(
            ['slug' => 'signal-manager'],
            [
                'name' => 'Signal Manager',
                'description' => 'Manage trading signals',
                'is_system' => false,
            ]
        );
        $signalManager->permissions()->sync(
            Permission::whereIn('slug', [
                'signals_view', 'signals_create', 'signals_edit', 'signals_delete',
                'signal_categories_view', 'signal_categories_create', 'signal_categories_edit', 'signal_categories_delete',
            ])->pluck('id')->toArray()
        );

        // Education Manager
        $educationManager = Role::updateOrCreate(
            ['slug' => 'education-manager'],
            [
                'name' => 'Education Manager',
                'description' => 'Manage courses, lessons, and education content',
                'is_system' => false,
            ]
        );
        $educationManager->permissions()->sync(
            Permission::whereIn('slug', [
                'education_view', 'education_create', 'education_edit', 'education_delete',
                'education_categories_view', 'education_categories_create', 'education_categories_edit', 'education_categories_delete',
                'lessons_view', 'lessons_create', 'lessons_edit', 'lessons_delete',
            ])->pluck('id')->toArray()
        );

        // Chat Moderator
        $chatModerator = Role::updateOrCreate(
            ['slug' => 'chat-moderator'],
            [
                'name' => 'Chat Moderator',
                'description' => 'Moderate global chat',
                'is_system' => false,
            ]
        );
        $chatModerator->permissions()->sync(
            Permission::whereIn('slug', [
                'chat_view', 'chat_moderate', 'chat_delete_message', 'chat_ban_user',
            ])->pluck('id')->toArray()
        );

        // Support Manager
        $supportManager = Role::updateOrCreate(
            ['slug' => 'support-manager'],
            [
                'name' => 'Support Manager',
                'description' => 'Manage user support and tickets',
                'is_system' => false,
            ]
        );
        $supportManager->permissions()->sync(
            Permission::whereIn('slug', [
                'users_view', 'users_edit', 'users_ban',
                'subscriptions_view', 'subscriptions_manage',
                'chat_view', 'chat_moderate',
            ])->pluck('id')->toArray()
        );

        // User (for mobile app)
        $user = Role::updateOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Regular mobile app user',
                'is_system' => true,
            ]
        );
        $user->permissions()->sync([]);

        // Create Super Admin & Test Users
        $superAdminUser = User::updateOrCreate(
            ['email' => 'admin@ktsmarkets.com'],
            [
                'name' => 'KTS Admin',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $superAdminUser->assignRole('super-admin');

        // Legacy admin compatibility
        $legacyAdmin = User::updateOrCreate(
            ['email' => 'admin@kts10pipsbots.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $legacyAdmin->assignRole('super-admin');

        // Test Mobile App User 1
        $testUser1 = User::updateOrCreate(
            ['email' => 'test@ktsmarkets.com'],
            [
                'name' => 'Demo Trader',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $testUser1->assignRole('user');

        // Test Mobile App User 2
        $testUser2 = User::updateOrCreate(
            ['email' => 'user@ktsmarkets.com'],
            [
                'name' => 'Pro Trader',
                'password' => Hash::make('Password123!'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $testUser2->assignRole('user');
    }
}
