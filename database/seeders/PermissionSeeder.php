<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Staff Management
            ['name' => 'View Staff', 'slug' => 'staff_view', 'module' => 'staff', 'action' => 'view', 'description' => 'View staff listing'],
            ['name' => 'Create Staff', 'slug' => 'staff_create', 'module' => 'staff', 'action' => 'create', 'description' => 'Create new staff members'],
            ['name' => 'Edit Staff', 'slug' => 'staff_edit', 'module' => 'staff', 'action' => 'edit', 'description' => 'Edit staff member details'],
            ['name' => 'Delete Staff', 'slug' => 'staff_delete', 'module' => 'staff', 'action' => 'delete', 'description' => 'Delete staff members'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'roles_view', 'module' => 'roles', 'action' => 'view', 'description' => 'View roles listing'],
            ['name' => 'Create Roles', 'slug' => 'roles_create', 'module' => 'roles', 'action' => 'create', 'description' => 'Create new roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles_edit', 'module' => 'roles', 'action' => 'edit', 'description' => 'Edit role details'],
            ['name' => 'Delete Roles', 'slug' => 'roles_delete', 'module' => 'roles', 'action' => 'delete', 'description' => 'Delete roles'],

            // Permissions
            ['name' => 'View Permissions', 'slug' => 'permissions_view', 'module' => 'permissions', 'action' => 'view', 'description' => 'View system permissions'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions_manage', 'module' => 'permissions', 'action' => 'manage', 'description' => 'Manage permission assignments'],

            // Users
            ['name' => 'View Users', 'slug' => 'users_view', 'module' => 'users', 'action' => 'view', 'description' => 'View user listing'],
            ['name' => 'Create Users', 'slug' => 'users_create', 'module' => 'users', 'action' => 'create', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'users_edit', 'module' => 'users', 'action' => 'edit', 'description' => 'Edit user details'],
            ['name' => 'Delete Users', 'slug' => 'users_delete', 'module' => 'users', 'action' => 'delete', 'description' => 'Delete users'],
            ['name' => 'Ban Users', 'slug' => 'users_ban', 'module' => 'users', 'action' => 'ban', 'description' => 'Ban/unban users'],

            // Subscriptions
            ['name' => 'View Subscriptions', 'slug' => 'subscriptions_view', 'module' => 'subscriptions', 'action' => 'view', 'description' => 'View subscriptions'],
            ['name' => 'Manage Subscriptions', 'slug' => 'subscriptions_manage', 'module' => 'subscriptions', 'action' => 'manage', 'description' => 'Manage user subscriptions'],

            // Signals
            ['name' => 'View Signals', 'slug' => 'signals_view', 'module' => 'signals', 'action' => 'view', 'description' => 'View trading signals'],
            ['name' => 'Create Signals', 'slug' => 'signals_create', 'module' => 'signals', 'action' => 'create', 'description' => 'Create trading signals'],
            ['name' => 'Edit Signals', 'slug' => 'signals_edit', 'module' => 'signals', 'action' => 'edit', 'description' => 'Edit trading signals'],
            ['name' => 'Delete Signals', 'slug' => 'signals_delete', 'module' => 'signals', 'action' => 'delete', 'description' => 'Delete trading signals'],

            // Signal Categories
            ['name' => 'View Signal Categories', 'slug' => 'signal_categories_view', 'module' => 'signal_categories', 'action' => 'view', 'description' => 'View signal categories'],
            ['name' => 'Create Signal Categories', 'slug' => 'signal_categories_create', 'module' => 'signal_categories', 'action' => 'create', 'description' => 'Create signal categories'],
            ['name' => 'Edit Signal Categories', 'slug' => 'signal_categories_edit', 'module' => 'signal_categories', 'action' => 'edit', 'description' => 'Edit signal categories'],
            ['name' => 'Delete Signal Categories', 'slug' => 'signal_categories_delete', 'module' => 'signal_categories', 'action' => 'delete', 'description' => 'Delete signal categories'],

            // Chat
            ['name' => 'View Chat', 'slug' => 'chat_view', 'module' => 'chat', 'action' => 'view', 'description' => 'View chat messages'],
            ['name' => 'Moderate Chat', 'slug' => 'chat_moderate', 'module' => 'chat', 'action' => 'moderate', 'description' => 'Moderate chat messages'],
            ['name' => 'Delete Messages', 'slug' => 'chat_delete_message', 'module' => 'chat', 'action' => 'delete_message', 'description' => 'Delete chat messages'],
            ['name' => 'Ban Users (Chat)', 'slug' => 'chat_ban_user', 'module' => 'chat', 'action' => 'ban_user', 'description' => 'Ban users from chat'],

            // Education
            ['name' => 'View Education', 'slug' => 'education_view', 'module' => 'education', 'action' => 'view', 'description' => 'View educational content'],
            ['name' => 'Create Education', 'slug' => 'education_create', 'module' => 'education', 'action' => 'create', 'description' => 'Create educational content'],
            ['name' => 'Edit Education', 'slug' => 'education_edit', 'module' => 'education', 'action' => 'edit', 'description' => 'Edit educational content'],
            ['name' => 'Delete Education', 'slug' => 'education_delete', 'module' => 'education', 'action' => 'delete', 'description' => 'Delete educational content'],

            // Education Categories
            ['name' => 'View Education Categories', 'slug' => 'education_categories_view', 'module' => 'education_categories', 'action' => 'view', 'description' => 'View education categories'],
            ['name' => 'Create Education Categories', 'slug' => 'education_categories_create', 'module' => 'education_categories', 'action' => 'create', 'description' => 'Create education categories'],
            ['name' => 'Edit Education Categories', 'slug' => 'education_categories_edit', 'module' => 'education_categories', 'action' => 'edit', 'description' => 'Edit education categories'],
            ['name' => 'Delete Education Categories', 'slug' => 'education_categories_delete', 'module' => 'education_categories', 'action' => 'delete', 'description' => 'Delete education categories'],

            // Lessons
            ['name' => 'View Lessons', 'slug' => 'lessons_view', 'module' => 'lessons', 'action' => 'view', 'description' => 'View lessons'],
            ['name' => 'Create Lessons', 'slug' => 'lessons_create', 'module' => 'lessons', 'action' => 'create', 'description' => 'Create lessons'],
            ['name' => 'Edit Lessons', 'slug' => 'lessons_edit', 'module' => 'lessons', 'action' => 'edit', 'description' => 'Edit lessons'],
            ['name' => 'Delete Lessons', 'slug' => 'lessons_delete', 'module' => 'lessons', 'action' => 'delete', 'description' => 'Delete lessons'],

            // AI Chatbot
            ['name' => 'View AI Chatbot', 'slug' => 'ai_chatbot_view', 'module' => 'ai_chatbot', 'action' => 'view', 'description' => 'View AI chatbot settings'],
            ['name' => 'Manage AI Chatbot', 'slug' => 'ai_chatbot_manage', 'module' => 'ai_chatbot', 'action' => 'manage', 'description' => 'Manage AI chatbot settings'],

            // Notifications
            ['name' => 'View Notifications', 'slug' => 'notifications_view', 'module' => 'notifications', 'action' => 'view', 'description' => 'View notifications'],
            ['name' => 'Send Notifications', 'slug' => 'notifications_send', 'module' => 'notifications', 'action' => 'send', 'description' => 'Send push notifications'],

            // MT5 Bot
            ['name' => 'View MT5 Bot', 'slug' => 'mt5_bot_view', 'module' => 'mt5_bot', 'action' => 'view', 'description' => 'View MT5 bot settings'],
            ['name' => 'Manage MT5 Bot', 'slug' => 'mt5_bot_manage', 'module' => 'mt5_bot', 'action' => 'manage', 'description' => 'Manage MT5 bot credentials'],

            // Financials
            ['name' => 'View Transactions', 'slug' => 'transactions_view', 'module' => 'transactions', 'action' => 'view', 'description' => 'View financial transactions'],
            ['name' => 'Manage Transactions', 'slug' => 'transactions_manage', 'module' => 'transactions', 'action' => 'manage', 'description' => 'Manage financial transactions'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings_view', 'module' => 'settings', 'action' => 'view', 'description' => 'View system settings'],
            ['name' => 'Manage Settings', 'slug' => 'settings_manage', 'module' => 'settings', 'action' => 'manage', 'description' => 'Manage system settings'],

            // Backup
            ['name' => 'View Backups', 'slug' => 'backup_view', 'module' => 'backup', 'action' => 'view', 'description' => 'View backups'],
            ['name' => 'Create Backups', 'slug' => 'backup_create', 'module' => 'backup', 'action' => 'create', 'description' => 'Create system backups'],
            ['name' => 'Download Backups', 'slug' => 'backup_download', 'module' => 'backup', 'action' => 'download', 'description' => 'Download backup archives'],

            // Demo Account Requests
            ['name' => 'View Demo Accounts', 'slug' => 'demo_accounts_view', 'module' => 'demo_accounts', 'action' => 'view', 'description' => 'View demo account requests'],
            ['name' => 'Manage Demo Accounts', 'slug' => 'demo_accounts_manage', 'module' => 'demo_accounts', 'action' => 'manage', 'description' => 'Approve/reject demo account requests'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }
    }
}
