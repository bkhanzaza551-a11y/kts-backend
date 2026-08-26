<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationSetting;

class NotificationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Signals
            ['slug' => 'signal_new', 'name' => 'New Signal Alert', 'description' => 'Notify users when a new trading signal is published', 'category' => 'signals', 'icon' => 'bi-graph-up-arrow', 'is_enabled' => true],
            ['slug' => 'signal_update', 'name' => 'Signal Update', 'description' => 'Notify users when a signal is updated (entry, TP, SL changes)', 'category' => 'signals', 'icon' => 'bi-pencil-square', 'is_enabled' => true],
            ['slug' => 'signal_closed', 'name' => 'Signal Closed', 'description' => 'Notify users when a signal is closed with win/loss result', 'category' => 'signals', 'icon' => 'bi-check-circle', 'is_enabled' => true],

            // MT5 Bots
            ['slug' => 'bot_trade', 'name' => 'Bot Trade Executed', 'description' => 'Notify users when their bot executes a trade', 'category' => 'bots', 'icon' => 'bi-arrow-left-right', 'is_enabled' => true],
            ['slug' => 'bot_profit', 'name' => 'Bot Profit Alert', 'description' => 'Notify users when their bot makes a profit', 'category' => 'bots', 'icon' => 'bi-arrow-up-circle', 'is_enabled' => true],
            ['slug' => 'bot_loss', 'name' => 'Bot Loss Alert', 'description' => 'Notify users when their bot incurs a loss', 'category' => 'bots', 'icon' => 'bi-arrow-down-circle', 'is_enabled' => true],
            ['slug' => 'bot_status', 'name' => 'Bot Status Change', 'description' => 'Notify users when bot status changes (started, stopped, error)', 'category' => 'bots', 'icon' => 'bi-info-circle', 'is_enabled' => true],

            // Payments
            ['slug' => 'payment_success', 'name' => 'Payment Success', 'description' => 'Notify users when a payment is processed successfully', 'category' => 'payments', 'icon' => 'bi-check-circle-fill', 'is_enabled' => true],
            ['slug' => 'payment_failed', 'name' => 'Payment Failed', 'description' => 'Notify users when a payment fails', 'category' => 'payments', 'icon' => 'bi-x-circle-fill', 'is_enabled' => true],
            ['slug' => 'subscription_expiring', 'name' => 'Subscription Expiring', 'description' => 'Notify users before their subscription expires (3 days, 1 day)', 'category' => 'payments', 'icon' => 'bi-clock-history', 'is_enabled' => true],
            ['slug' => 'subscription_expired', 'name' => 'Subscription Expired', 'description' => 'Notify users when their subscription has expired', 'category' => 'payments', 'icon' => 'bi-x-octagon', 'is_enabled' => true],

            // Chat
            ['slug' => 'chat_message', 'name' => 'New Chat Message', 'description' => 'Notify users of new messages in chat rooms they belong to', 'category' => 'chat', 'icon' => 'bi-chat-left-text', 'is_enabled' => true],
            ['slug' => 'chat_mention', 'name' => 'Chat Mention', 'description' => 'Notify users when they are mentioned in chat', 'category' => 'chat', 'icon' => 'bi-at', 'is_enabled' => true],

            // Education
            ['slug' => 'course_new', 'name' => 'New Course Available', 'description' => 'Notify users when a new course is published', 'category' => 'education', 'icon' => 'bi-journal-plus', 'is_enabled' => true],
            ['slug' => 'lesson_new', 'name' => 'New Lesson Added', 'description' => 'Notify users when a new lesson is added to their enrolled course', 'category' => 'education', 'icon' => 'bi-plus-circle', 'is_enabled' => true],

            // System
            ['slug' => 'system_announcement', 'name' => 'System Announcements', 'description' => 'General announcements from admin to all users', 'category' => 'system', 'icon' => 'bi-megaphone', 'is_enabled' => true],
            ['slug' => 'system_maintenance', 'name' => 'Maintenance Notices', 'description' => 'Notify users about scheduled maintenance', 'category' => 'system', 'icon' => 'bi-tools', 'is_enabled' => true],
            ['slug' => 'system_update', 'name' => 'App Update Notices', 'description' => 'Notify users about new app versions and features', 'category' => 'system', 'icon' => 'bi-cloud-download', 'is_enabled' => true],

            // Security
            ['slug' => 'security_login', 'name' => 'Login Alert', 'description' => 'Notify users when their account is logged into from a new device', 'category' => 'security', 'icon' => 'bi-box-arrow-in-right', 'is_enabled' => true],
            ['slug' => 'security_password', 'name' => 'Password Changed', 'description' => 'Notify users when their password is changed', 'category' => 'security', 'icon' => 'bi-key', 'is_enabled' => true],
            ['slug' => 'security_2fa', 'name' => '2FA Alert', 'description' => 'Notify users when 2FA is enabled or disabled', 'category' => 'security', 'icon' => 'bi-shield-check', 'is_enabled' => true],
            ['slug' => 'security_suspicious', 'name' => 'Suspicious Activity', 'description' => 'Notify users of suspicious account activity', 'category' => 'security', 'icon' => 'bi-exclamation-triangle', 'is_enabled' => true],

            // Demo Account
            ['slug' => 'demo_approved', 'name' => 'Demo Account Approved', 'description' => 'Notify users when their demo account request is approved', 'category' => 'demo', 'icon' => 'bi-check-lg', 'is_enabled' => true],
            ['slug' => 'demo_rejected', 'name' => 'Demo Account Rejected', 'description' => 'Notify users when their demo account request is rejected', 'category' => 'demo', 'icon' => 'bi-x-lg', 'is_enabled' => true],
        ];

        foreach ($settings as $setting) {
            NotificationSetting::updateOrCreate(
                ['slug' => $setting['slug']],
                $setting
            );
        }
    }
}
