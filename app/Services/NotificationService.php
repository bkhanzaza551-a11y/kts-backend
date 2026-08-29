<?php

namespace App\Services;

use App\Models\NotificationSetting;
use App\Models\AdminNotification;
use App\Models\User;
use App\Services\ActivityLogger;

class NotificationService
{
    /**
     * Check if a notification type is enabled by slug.
     */
    public static function isEnabled(string $slug): bool
    {
        return NotificationSetting::isEnabled($slug);
    }

    /**
     * Send an in-app notification only if the notification type is enabled.
     * Returns the AdminNotification if sent, null if blocked.
     */
    public static function send(string $slug, array $data): ?AdminNotification
    {
        if (!static::isEnabled($slug)) {
            return null;
        }

        $notification = AdminNotification::create([
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'type' => $data['type'] ?? 'info',
            'target' => $data['target'] ?? 'all',
            'target_user_id' => $data['target_user_id'] ?? null,
            'target_role_id' => $data['target_role_id'] ?? null,
            'sent_count' => $data['sent_count'] ?? 0,
            'is_sent' => true,
            'sent_by' => $data['sent_by'] ?? null,
        ]);

        ActivityLogger::log('notification_sent', 'Notification', $notification->id, "Notification sent: {$notification->title} (type: {$slug})");

        return $notification;
    }

    /**
     * Send notification to a specific user only if enabled.
     */
    public static function sendToUser(string $slug, User $user, array $data): ?AdminNotification
    {
        if (!static::isEnabled($slug)) {
            return null;
        }

        $notification = AdminNotification::create([
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'type' => $data['type'] ?? 'info',
            'target' => 'user',
            'target_user_id' => $user->id,
            'sent_count' => 1,
            'is_sent' => true,
            'sent_by' => $data['sent_by'] ?? null,
        ]);

        return $notification;
    }

    /**
     * Get all notification settings grouped by category.
     */
    public static function getGrouped(): array
    {
        $settings = NotificationSetting::orderBy('category')->orderBy('name')->get();
        return $settings->groupBy('category')->toArray();
    }

    /**
     * Get enabled notification slugs.
     */
    public static function getEnabledSlugs(): array
    {
        return NotificationSetting::enabled()->pluck('slug')->toArray();
    }

    /**
     * Toggle a notification setting.
     */
    public static function toggle(string $slug): ?NotificationSetting
    {
        $setting = NotificationSetting::where('slug', $slug)->first();
        if (!$setting) {
            return null;
        }
        $setting->update(['is_enabled' => !$setting->is_enabled]);
        return $setting;
    }
}
