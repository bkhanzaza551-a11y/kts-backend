<?php

namespace App\Services;

use App\Models\UserDevice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private static ?string $serverKey = null;
    private static ?string $serviceAccountPath = null;

    private static function getServerKey(): ?string
    {
        if (self::$serverKey !== null) {
            return self::$serverKey;
        }
        self::$serverKey = config('services.fcm.server_key') ?: env('FCM_SERVER_KEY');
        return self::$serverKey;
    }

    private static function getServiceAccountPath(): ?string
    {
        if (self::$serviceAccountPath !== null) {
            return self::$serviceAccountPath;
        }
        self::$serviceAccountPath = env('FCM_SERVICE_ACCOUNT_PATH');
        return self::$serviceAccountPath;
    }

    private static function getAccessToken(): ?string
    {
        // Try JSON env var first (for Railway)
        $serviceAccountJson = env('FCM_SERVICE_ACCOUNT_JSON');
        if (!empty($serviceAccountJson)) {
            $serviceAccount = json_decode($serviceAccountJson, true);
        } else {
            // Try file path (for local dev)
            $serviceAccountPath = self::getServiceAccountPath();
            if (empty($serviceAccountPath) || !file_exists($serviceAccountPath)) {
                return null;
            }
            $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
        }

        if (empty($serviceAccount) || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
            Log::warning('FCM: Invalid service account configuration');
            return null;
        }

        try {
            $now = time();

            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $data = "$header.$payload";
            openssl_sign($data, $signature, $serviceAccount['private_key'], 'SHA256');
            $jwt = "$data." . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        } catch (\Exception $e) {
            Log::error('FCM V1 access token error: ' . $e->getMessage());
        }

        return null;
    }

    public static function sendToAll(string $title, string $body, array $data = []): int
    {
        $tokens = UserDevice::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        // Try V1 API first (if service account exists)
        $accessToken = self::getAccessToken();
        if ($accessToken) {
            return self::sendViaV1($tokens, $title, $body, $data, $accessToken);
        }

        // Fallback to Legacy API
        $serverKey = self::getServerKey();
        if (empty($serverKey)) {
            Log::warning('FCM: No server key or service account configured. Push notification skipped.');
            return 0;
        }

        return self::sendViaLegacy($tokens, $title, $body, $data, $serverKey);
    }

    private static function sendViaV1(array $tokens, string $title, string $body, array $data, string $accessToken): int
    {
        $projectId = config('services.fcm.project_id', 'laptopharbor-2d756');
        $sent = 0;

        foreach ($tokens as $token) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_merge($data, [
                            'title' => $title,
                            'body' => $body,
                        ]),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'channel_id' => 'kts_signals',
                                'sound' => 'default',
                            ],
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $sent++;
                } else {
                    $error = $response->json('error.message', 'Unknown');
                    if (str_contains($error, 'UNREGISTERED') || str_contains($error, 'INVALID')) {
                        UserDevice::where('fcm_token', $token)->delete();
                    }
                }
            } catch (\Exception $e) {
                Log::error('FCM V1 push failed: ' . $e->getMessage());
            }
        }

        return $sent;
    }

    private static function sendViaLegacy(array $tokens, string $title, string $body, array $data, string $serverKey): int
    {
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

                    if (isset($result['results'])) {
                        foreach ($result['results'] as $i => $r) {
                            if (isset($r['error']) && in_array($r['error'], ['NotRegistered', 'InvalidRegistration'])) {
                                UserDevice::where('fcm_token', $chunk[$i])->delete();
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('FCM Legacy push failed: ' . $e->getMessage());
            }
        }

        return $sent;
    }

    public static function sendToUser(int $userId, string $title, string $body, array $data = []): int
    {
        $tokens = UserDevice::where('user_id', $userId)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            return 0;
        }

        $accessToken = self::getAccessToken();
        if ($accessToken) {
            return self::sendViaV1($tokens, $title, $body, $data, $accessToken);
        }

        $serverKey = self::getServerKey();
        if (empty($serverKey)) {
            return 0;
        }

        return self::sendViaLegacy($tokens, $title, $body, $data, $serverKey);
    }
}
