<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\NotificationSetting;
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
        $totalTargeted = AdminNotification::where('is_sent', true)
            ->where(function ($q) use ($userId) {
                $q->where('target', 'all')
                    ->orWhere(function($sub) use ($userId) {
                        $sub->where('target', 'specific')->where('target_user_id', $userId);
                    });
            })
            ->count();

        $readCount = \DB::table('admin_notification_reads')
            ->where('user_id', $userId)
            ->count();

        $count = max(0, $totalTargeted - $readCount);

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
     * Get all notification settings for the mobile app.
     */
    public function settings(): JsonResponse
    {
        $settings = NotificationSetting::orderBy('category')->orderBy('name')->get([
            'slug', 'name', 'description', 'category', 'icon', 'is_enabled'
        ]);

        $grouped = $settings->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => [
                'settings' => $settings,
                'grouped' => $grouped,
                'enabled_slugs' => NotificationService::getEnabledSlugs(),
            ],
        ]);
    }

    /**
     * Check if a specific notification type is enabled.
     */
    public function checkSetting($slug): JsonResponse
    {
        $setting = NotificationSetting::where('slug', $slug)->first();

        if (!$setting) {
            return response()->json([
                'success' => true,
                'data' => ['slug' => $slug, 'is_enabled' => true],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $setting->slug,
                'name' => $setting->name,
                'is_enabled' => $setting->is_enabled,
            ],
        ]);
    }

    /**
     * Toggle a notification setting (for mobile app).
     */
    public function toggleSetting($slug): JsonResponse
    {
        $setting = NotificationSetting::where('slug', $slug)->first();

        if (!$setting) {
            return response()->json(['success' => false, 'message' => 'Setting not found'], 404);
        }

        $setting->update(['is_enabled' => !$setting->is_enabled]);

        return response()->json([
            'success' => true,
            'data' => ['is_enabled' => $setting->is_enabled],
            'message' => "{$setting->name} has been " . ($setting->is_enabled ? 'enabled' : 'disabled'),
        ]);
    }

    /**
     * Toggle all settings in a category (for mobile app).
     */
    public function toggleAllCategory(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string',
            'is_enabled' => 'required|boolean',
        ]);

        NotificationSetting::where('category', $request->category)
            ->update(['is_enabled' => $request->is_enabled]);

        return response()->json([
            'success' => true,
            'message' => 'All ' . $request->category . ' notifications have been ' . ($request->is_enabled ? 'enabled' : 'disabled'),
        ]);
    }
}
