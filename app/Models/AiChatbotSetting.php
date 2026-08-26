<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiChatbotSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    public $timestamps = false;

    public static function getValue(string $key, $default = null)
    {
        $settings = Cache::remember('ai_chatbot_settings', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    public static function setValue(string $key, $value, string $type = 'text', ?string $description = null)
    {
        $result = static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'description' => $description]
        );

        Cache::forget('ai_chatbot_settings');

        return $result;
    }
}
