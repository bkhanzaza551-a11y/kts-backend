<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'user_id',
        'message',
        'type',
        'sticker_id',
        'is_flagged',
        'is_pinned',
        'pinned_at',
        'pinned_by',
        'is_deleted',
        'deleted_by',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'is_pinned' => 'boolean',
        'is_deleted' => 'boolean',
        'pinned_at' => 'datetime',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class, 'room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function pinner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    public function sticker(): BelongsTo
    {
        return $this->belongsTo(ChatSticker::class);
    }

    public function getFilteredMessageAttribute(): string
    {
        $words = Cache::remember('active_restricted_words', 600, function () {
            return ChatRestrictedWord::where('is_active', true)->get();
        });

        $message = $this->message;
        foreach ($words as $word) {
            $message = str_ireplace($word->word, $word->replacement, $message);
        }
        return $message;
    }
}
