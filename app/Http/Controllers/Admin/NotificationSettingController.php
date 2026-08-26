<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationSetting;
use App\Services\NotificationService;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $grouped = NotificationService::getGrouped();
        $categories = [
            'signals' => ['name' => 'Signals', 'icon' => 'bi-graph-up', 'color' => '#D4A843'],
            'bots' => ['name' => 'MT5 Bots', 'icon' => 'bi-robot', 'color' => '#4CAF50'],
            'payments' => ['name' => 'Payments', 'icon' => 'bi-credit-card', 'color' => '#2196F3'],
            'chat' => ['name' => 'Chat', 'icon' => 'bi-chat-dots', 'color' => '#FF9800'],
            'education' => ['name' => 'Education', 'icon' => 'bi-book', 'color' => '#9C27B0'],
            'system' => ['name' => 'System', 'icon' => 'bi-gear', 'color' => '#607D8B'],
            'security' => ['name' => 'Security', 'icon' => 'bi-shield-lock', 'color' => '#f44336'],
            'demo' => ['name' => 'Demo Account', 'icon' => 'bi-person-check', 'color' => '#00BCD4'],
        ];

        return view('admin.notification-settings.index', compact('grouped', 'categories'));
    }

    public function toggle(Request $request, $slug)
    {
        $setting = NotificationSetting::where('slug', $slug)->first();
        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        $setting->update(['is_enabled' => !$setting->is_enabled]);

        ActivityLogger::log(
            'notification_setting_changed',
            "Notification '{$setting->name}' ({$setting->slug}) " . ($setting->is_enabled ? 'ENABLED' : 'DISABLED')
        );

        return response()->json([
            'success' => true,
            'is_enabled' => $setting->is_enabled,
            'message' => "{$setting->name} has been " . ($setting->is_enabled ? 'enabled' : 'disabled'),
        ]);
    }

    public function toggleAll(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'is_enabled' => 'required|boolean',
        ]);

        NotificationSetting::where('category', $request->category)
            ->update(['is_enabled' => $request->is_enabled]);

        $catName = $request->category;
        ActivityLogger::log(
            'notification_setting_changed',
            "All {$catName} notifications " . ($request->is_enabled ? 'ENABLED' : 'DISABLED')
        );

        return response()->json([
            'success' => true,
            'message' => "All {$catName} notifications have been " . ($request->is_enabled ? 'enabled' : 'disabled'),
        ]);
    }

    public function stats()
    {
        $total = NotificationSetting::count();
        $enabled = NotificationSetting::enabled()->count();
        $disabled = $total - $enabled;

        return response()->json([
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $disabled,
        ]);
    }
}
