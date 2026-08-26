<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AdminOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'otp',
        'email',
        'expires_at',
        'is_used',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateFor(User $user, ?string $ipAddress = null): self
    {
        static::where('user_id', $user->id)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        return static::create([
            'user_id' => $user->id,
            'otp' => $otp,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(5),
            'ip_address' => $ipAddress,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function verify(string $otp): bool
    {
        if ($this->is_used || $this->isExpired()) {
            return false;
        }

        if (!hash_equals($this->otp, $otp)) {
            return false;
        }

        $this->update(['is_used' => true]);
        return true;
    }
}
