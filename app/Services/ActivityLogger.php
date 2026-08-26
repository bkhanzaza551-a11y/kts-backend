<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(
        string $action,
        ?string $model = null,
        ?string $modelId = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null
    ): ?ActivityLog {
        try {
            $user = Auth::user();
            $request = $request ?? request();

            return ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'model' => $model,
                'model_id' => $modelId,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::error('ActivityLogger failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function getRecent(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function getUserLogs(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public static function getModelLogs(string $model, string $modelId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::where('model', $model)
            ->where('model_id', $modelId)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
