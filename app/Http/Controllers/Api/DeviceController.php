<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserDevice;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'platform' => 'required|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:50',
        ]);

        $device = UserDevice::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'fcm_token' => $validated['fcm_token'],
            ],
            [
                'platform' => $validated['platform'],
                'device_name' => $validated['device_name'] ?? null,
                'os_version' => $validated['os_version'] ?? null,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'data' => ['device_id' => $device->id],
        ]);
    }

    public function unregister(Request $request)
    {
        $validated = $request->validate([
            'fcm_token' => 'required|string',
        ]);

        UserDevice::where('user_id', $request->user()->id)
            ->where('fcm_token', $validated['fcm_token'])
            ->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Device unregistered',
        ]);
    }
}
