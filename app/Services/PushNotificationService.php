<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private static ?string $serverKey = null;

    private static function getServerKey(): ?string
    {
        if (self::$serverKey !== null) {
            return self::$serverKey;
        }
        self::$serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
        return self::$serverKey;
    }

    public static function sendToAll(string $title, string $body, array $data = []): int
    {
        $serverKey = self::getServerKey();
        if (empty($serverKey)) {
            Log::warning('FCM server key not configured. Push notification skipped.');
            return 0;
        }

        $tokens = UserDevice::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $sent = 0;
        $chunks = array_chunk($tokens, 500);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                    'data' => array_merge($data, [
                        'title' => $title,
                        'body' => $body,
                    ]),
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $sent += $result['success'] ?? 0;

                    // Remove invalid tokens
                    if (isset($result['results'])) {
                        foreach ($result['results'] as $i => $r) {
                            if (isset($r['error']) && in_array($r['error'], ['NotRegistered', 'InvalidRegistration'])) {
                                UserDevice::where('fcm_token', $chunk[$i])->delete();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('FCM push failed: ' . $e->getMessage());
            }
        }

        return $sent;
    }

    public static function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $serverKey = self::getServerKey();
        if (empty($serverKey)) {
            return 0;
        }

        $tokens = UserDevice::where('user_id', $userId)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $sent = 0;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
                'registration_ids' => $tokens,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'body' => $body,
                ]),
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                $sent = $result['success'] ?? 0;
            }
        } catch (\Exception $e) {
            Log::error('FCM push to user failed: ' . $e->getMessage());
        }

        return $sent;
    }
}
