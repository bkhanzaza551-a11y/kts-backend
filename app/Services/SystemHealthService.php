<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;

class SystemHealthService
{
    private const CACHE_TTL = 300; // 5 minutes

    public static function getMetrics(): array
    {
        return Cache::remember('system_health_metrics', self::CACHE_TTL, function () {
            return [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'db_driver' => static::getDbDriver(),
                'db_version' => static::getDbVersion(),
                'disk_usage' => static::getDiskUsage(),
                'memory_usage' => static::getMemoryUsage(),
                'cache_status' => static::getCacheStatus(),
                'queue_status' => static::getQueueStatus(),
                'active_sessions' => static::getActiveSessions(),
                'server_up' => true,
                'last_backup' => static::getLastBackup(),
                'maintenance_mode' => app()->isDownForMaintenance(),
            ];
        });
    }

    public static function getHealthScore(?array $metrics = null): int
    {
        $metrics = $metrics ?? static::getMetrics();
        $score = 100;

        if ($metrics['disk_usage']['used_percent'] > 90) {
            $score -= 20;
        } elseif ($metrics['disk_usage']['used_percent'] > 80) {
            $score -= 10;
        }

        if ($metrics['memory_usage']['used_percent'] > 90) {
            $score -= 20;
        } elseif ($metrics['memory_usage']['used_percent'] > 80) {
            $score -= 10;
        }

        if (!$metrics['cache_status']) {
            $score -= 10;
        }

        if ($metrics['maintenance_mode']) {
            $score -= 30;
        }

        return max(0, $score);
    }

    public static function clearCache(): void
    {
        Cache::forget('system_health_metrics');
        Cache::forget('db_table_exists_sessions');
    }

    private static function getDbDriver(): string
    {
        return DB::getDriverName();
    }

    private static function getDbVersion(): string
    {
        try {
            $driver = DB::getDriverName();

            return match ($driver) {
                'mysql', 'mariadb' => DB::select('SELECT VERSION() as version')[0]->version ?? 'Unknown',
                'pgsql' => DB::select('SELECT version() as version')[0]->version ?? 'Unknown',
                'sqlite' => 'SQLite ' . DB::select('SELECT sqlite_version() as version')[0]->version ?? 'Unknown',
                default => 'N/A',
            };
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private static function getDiskUsage(): array
    {
        $path = DIRECTORY_SEPARATOR === '\\' ? 'C:' : '/';
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);

        if ($total === false || $free === false || $total <= 0) {
            return [
                'total' => 0,
                'free' => 0,
                'used' => 0,
                'used_percent' => 0,
                'total_formatted' => 'N/A',
                'free_formatted' => 'N/A',
                'used_formatted' => 'N/A',
            ];
        }

        $used = $total - $free;

        return [
            'total' => $total,
            'free' => $free,
            'used' => $used,
            'used_percent' => round(($used / $total) * 100, 1),
            'total_formatted' => static::formatBytes((int) $total),
            'free_formatted' => static::formatBytes((int) $free),
            'used_formatted' => static::formatBytes((int) $used),
        ];
    }

    private static function getMemoryUsage(): array
    {
        $used = memory_get_usage(false);
        $peak = memory_get_peak_usage(false);

        $total = max($used * 2, $peak, 1);

        return [
            'used' => $used,
            'peak' => $peak,
            'total' => $total,
            'used_percent' => round(($used / $total) * 100, 1),
            'used_formatted' => static::formatBytes($used),
            'peak_formatted' => static::formatBytes($peak),
            'total_formatted' => static::formatBytes($total),
        ];
    }

    private static function getCacheStatus(): bool
    {
        return Cache::remember('health_cache_check', self::CACHE_TTL, function () {
            try {
                Cache::put('_health_probe', true, 30);
                return Cache::get('_health_probe') === true;
            } catch (\Exception $e) {
                return false;
            }
        });
    }

    private static function getQueueStatus(): string
    {
        try {
            $pending = Queue::size();
            return "{$pending} pending jobs";
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private static function getActiveSessions(): int
    {
        return Cache::remember('health_session_count', self::CACHE_TTL, function () {
            try {
                $tableExists = Cache::remember('db_table_exists_sessions', 3600, function () {
                    return Schema::hasTable('sessions');
                });

                if (!$tableExists) {
                    return 0;
                }

                return DB::table('sessions')->count();
            } catch (\Exception $e) {
                return 0;
            }
        });
    }

    private static function getLastBackup(): ?string
    {
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) {
            return null;
        }

        $files = File::files($backupPath);
        if (empty($files)) {
            return null;
        }

        $latest = collect($files)->sortBy('mtime')->last();
        return $latest->getFilename() . ' (' . $latest->getMTime()->diffForHumans() . ')';
    }

    private static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
