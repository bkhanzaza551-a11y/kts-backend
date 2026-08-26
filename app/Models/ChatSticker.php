<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatSticker extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_id',
        'name',
        'image_url',
        'file_size',
        'is_active',
        'sort_order',
        'usage_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'usage_count' => 'integer',
    ];

    public function pack(): BelongsTo
    {
        return $this->belongsTo(ChatStickerPack::class, 'pack_id');
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
