<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBlockedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blocked_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    public static function getBlockedUserIds(int $userId): array
    {
        return static::where('user_id', $userId)
            ->pluck('blocked_user_id')
            ->toArray();
    }

    public static function isBlocked(int $userId, int $targetUserId): bool
    {
        return static::where('user_id', $userId)
            ->where('blocked_user_id', $targetUserId)
            ->exists();
    }
}
