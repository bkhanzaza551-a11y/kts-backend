<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\NotificationSetting;
use App\Models\UserNotificationSetting;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $notifications = AdminNotification::where('is_sent', true)
            ->where(function ($q) use ($userId) {
                $q->where('target', 'all')
                    ->orWhere(function($sub) use ($userId) {
                        $sub->where('target', 'specific')->where('target_user_id', $userId);
                    });
            })
            ->latest()
            ->paginate(20);

        $readIds = \DB::table('admin_notification_reads')
            ->where('user_id', $userId)
            ->whereIn('admin_notification_id', $notifications->pluck('id'))
            ->pluck('admin_notification_id')
            ->toArray();

        $notifications->getCollection()->transform(function ($notif) use ($readIds) {
            $notif->is_read = in_array($notif->id, $readIds);
            return $notif;
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unread(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $count = AdminNotification::where('is_sent', true)
            ->where(function ($q) use ($userId) {
                $q->where('target', 'all')
                    ->orWhere(function($sub) use ($userId) {
                        $sub->where('target', 'specific')->where('target_user_id', $userId);
                    });
            })
            ->whereNotIn('id', function ($query) use ($userId) {
                $query->select('admin_notification_id')
                    ->from('admin_notification_reads')
                    ->where('user_id', $userId);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    public function markAsRead(Request $request, $id): JsonResponse
    {
        \DB::table('admin_notification_reads')->updateOrInsert(
            ['user_id' => $request->user()->id, 'admin_notification_id' => $id],
            ['read_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Get notification settings for the current user.
     */
    public function settings(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $globalSettings = NotificationSetting::orderBy('category')->orderBy('name')->get([
            'slug', 'name', 'description', 'category', 'icon', 'is_enabled'
        ]);

        $userSettings = UserNotificationSetting::where('user_id', $userId)
            ->pluck('is_enabled', 'notification_setting_slug')
            ->toArray();

        $mergedSettings = $globalSettings->map(function ($setting) use ($userSettings) {
            if (array_key_exists($setting->slug, $userSettings)) {
                $setting->is_enabled = $userSettings[$setting->slug];
            }
            return $setting;
        });

        $grouped = $mergedSettings->groupBy('category');
        $enabledSlugs = $mergedSettings->where('is_enabled', true)->pluck('slug')->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $mergedSettings,
                'grouped' => $grouped,
                'enabled_slugs' => $enabledSlugs,
            ],
        ]);
    }

    /**
     * Check if a specific notification type is enabled for the current user.
     */
    public function checkSetting(Request $request, $slug): JsonResponse
    {
        $userId = $request->user()->id;
        $isEnabled = UserNotificationSetting::isEnabledForUser($userId, $slug);

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $slug,
                'is_enabled' => $isEnabled,
            ],
        ]);
    }

    /**
     * Toggle a notification setting for the current user only.
     */
    public function toggleSetting(Request $request, $slug): JsonResponse
    {
        $userId = $request->user()->id;

        $globalSetting = NotificationSetting::where('slug', $slug)->first();
        if (!$globalSetting) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        }

        $userSetting = UserNotificationSetting::where('user_id', $userId)
            ->where('notification_setting_slug', $slug)
            ->first();

        if ($userSetting) {
            $newEnabled = !$userSetting->is_enabled;
            $userSetting->update(['is_enabled' => $newEnabled]);
        } else {
            $newEnabled = !$globalSetting->is_enabled;
            UserNotificationSetting::create([
                'user_id' => $userId,
                'notification_setting_slug' => $slug,
                'is_enabled' => $newEnabled,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => ['is_enabled' => $newEnabled],
            'message' => "{$globalSetting->name} has been " . ($newEnabled ? 'enabled' : 'disabled'),
        ]);
    }

    /**
     * Toggle all settings in a category for the current user only.
     */
    public function toggleAllCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string',
            'is_enabled' => 'required|boolean',
        ]);

        $userId = $request->user()->id;
        $globalSettings = NotificationSetting::where('category', $request->category)->get();

        foreach ($globalSettings as $setting) {
            UserNotificationSetting::updateOrCreate(
                ['user_id' => $userId, 'notification_setting_slug' => $setting->slug],
                ['is_enabled' => $request->is_enabled]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'All ' . $request->category . ' notifications have been ' . ($request->is_enabled ? 'enabled' : 'disabled') . ' for your account',
        ]);
    }
}
