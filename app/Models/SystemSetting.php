<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description', 'group'];

    public $timestamps = false;

    public static function getValue(string $key, $default = null)
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, $value, string $type = 'text', ?string $description = null, string $group = 'general')
    {
        $result = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'description' => $description, 'group' => $group]
        );

        Cache::forget('system_settings');

        return $result;
    }

    public static function isMaintenanceMode(): bool
    {
        return static::getValue('maintenance_mode', '0') === '1';
    }
}
