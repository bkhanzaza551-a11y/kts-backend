<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group')->map(function ($group) {
            return $group->mapWithKeys(fn($s) => [$s->key => $s]);
        });
        return view('admin.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'groq_api_key' => 'nullable|string|max:500',
            'openai_api_key' => 'nullable|string|max:500',
            'firebase_key' => 'nullable|string|max:5000',
            'jazzcash_merchant_id' => 'nullable|string|max:255',
            'jazzcash_password' => 'nullable|string|max:255',
            'easypaisa_store_id' => 'nullable|string|max:255',
            'easypaisa_password' => 'nullable|string|max:255',
            'app_name' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        foreach ($validated as $key => $value) {
            $group = str_contains($key, 'api_key') ? 'api_keys' : (str_contains($key, 'jazzcash') || str_contains($key, 'easypaisa') ? 'payment' : 'general');

            if (!empty($value)) {
                SystemSetting::setValue($key, $value, 'text', null, $group);
            }
        }

        ActivityLogger::log('update', 'SystemSetting', null, 'Updated system settings');
        Cache::forget('system_settings');

        return back()->with('success', 'Settings updated successfully.');
    }

    public function toggleMaintenance()
    {
        return \DB::transaction(function () {
            $current = SystemSetting::getValue('maintenance_mode', '0');
            $new = $current === '1' ? '0' : '1';

            SystemSetting::setValue('maintenance_mode', $new, 'boolean', 'Enable/disable maintenance mode', 'system');

            if ($new === '1') {
                $secret = Str::random(32);
                $exitCode = Artisan::call('down', ['--secret' => $secret]);
                if ($exitCode !== 0) {
                    SystemSetting::setValue('maintenance_mode', '0', 'boolean', 'Enable/disable maintenance mode', 'system');
                    return back()->with('error', 'Failed to enable maintenance mode.');
                }
            } else {
                $exitCode = Artisan::call('up');
                if ($exitCode !== 0) {
                    return back()->with('error', 'Failed to disable maintenance mode.');
                }
            }

            ActivityLogger::log('toggle_maintenance', 'SystemSetting', null, "Maintenance mode: " . ($new === '1' ? 'enabled' : 'disabled'));
            Cache::forget('system_health_metrics');

            return back()->with('success', 'Maintenance mode ' . ($new === '1' ? 'enabled' : 'disabled') . '.');
        });
    }

    public function backups()
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (File::exists($backupPath)) {
            $files = collect(File::files($backupPath))->sortByDesc(fn($f) => $f->getMTime())->take(50);
            foreach ($files as $file) {
                $backups[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'date' => $file->getMTime(),
                ];
            }
        }

        return view('admin.settings.backups', compact('backups'));
    }

    public function createBackup()
    {
        try {
            $backupDir = storage_path('app/backups');
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0700, true, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '_' . Str::random(6) . '.sqlite';
            $filepath = $backupDir . '/' . $filename;

            $dbPath = database_path('database.sqlite');

            if (!File::exists($dbPath)) {
                return back()->with('error', 'Database file not found.');
            }

            if (!copy($dbPath, $filepath)) {
                return back()->with('error', 'Failed to create backup.');
            }

            $this->cleanupOldBackups();

            ActivityLogger::log('create_backup', 'SystemSetting', null, "Created backup: {$filename}");

            return back()->with('success', "Backup created: {$filename}");
        } catch (\Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function downloadBackup($filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_[A-Za-z0-9]+\.sqlite$/', $filename)) {
            abort(404);
        }

        $filepath = storage_path('app/backups/' . $filename);

        if (!File::exists($filepath)) {
            abort(404);
        }

        return response()->download($filepath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    public function deleteBackup($filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}_[A-Za-z0-9]+\.sqlite$/', $filename)) {
            return back()->with('error', 'Invalid backup filename.');
        }

        $filepath = storage_path('app/backups/' . $filename);

        if (File::exists($filepath)) {
            File::delete($filepath);
            ActivityLogger::log('delete_backup', 'SystemSetting', null, "Deleted backup: {$filename}");
            return back()->with('success', 'Backup deleted.');
        }

        return back()->with('error', 'Backup not found.');
    }

    private function cleanupOldBackups(int $maxBackups = 10): void
    {
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) return;

        $files = collect(File::files($backupPath))
            ->sortBy(fn($f) => $f->getMTime())
            ->values();

        if ($files->count() > $maxBackups) {
            $files->slice(0, $files->count() - $maxBackups)->each(function ($file) {
                File::delete($file->getPathname());
            });
        }
    }
}
