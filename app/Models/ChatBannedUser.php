<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBannedUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'banned_by',
        'reason',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function isCurrentlyBanned(): bool
    {
        if ($this->expires_at === null) {
            return true;
        }
        return $this->expires_at->isFuture();
    }
}
