<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notification_setting_slug',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isEnabledForUser(int $userId, string $slug): bool
    {
        $setting = static::where('user_id', $userId)
            ->where('notification_setting_slug', $slug)
            ->first();

        if ($setting) {
            return $setting->is_enabled;
        }

        $global = NotificationSetting::where('slug', $slug)->first();
        return $global ? $global->is_enabled : true;
    }
}
