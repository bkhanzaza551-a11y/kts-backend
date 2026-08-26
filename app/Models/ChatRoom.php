<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'is_public',
        'is_paused',
        'pause_reason',
        'paused_at',
        'paused_by',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'is_paused' => 'boolean',
        'paused_at' => 'datetime',
    ];

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatMessage::class, 'room_id');
    }

    public function pauser(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'paused_by');
    }

    public function pinnedMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ChatMessage::class, 'room_id')->where('is_pinned', true)->latest('pinned_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
