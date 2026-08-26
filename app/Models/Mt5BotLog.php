<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mt5BotLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_config_id',
        'level',
        'action',
        'message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function botConfig(): BelongsTo
    {
        return $this->belongsTo(Mt5BotConfig::class, 'bot_config_id');
    }

    public function getLevelColorAttribute(): string
    {
        return match($this->level) {
            'info' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'success' => 'success',
            default => 'secondary',
        };
    }

    public function getLevelIconAttribute(): string
    {
        return match($this->level) {
            'info' => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
            'error' => 'bi-x-circle',
            'success' => 'bi-check-circle',
            default => 'bi-circle',
        };
    }
}
