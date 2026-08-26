<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mt5BotTrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_config_id',
        'ticket',
        'symbol',
        'type',
        'volume',
        'open_price',
        'close_price',
        'stop_loss',
        'take_profit',
        'profit',
        'commission',
        'swap',
        'status',
        'strategy',
        'comment',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'open_price' => 'decimal:5',
        'close_price' => 'decimal:5',
        'stop_loss' => 'decimal:5',
        'take_profit' => 'decimal:5',
        'profit' => 'decimal:2',
        'commission' => 'decimal:2',
        'swap' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function botConfig(): BelongsTo
    {
        return $this->belongsTo(Mt5BotConfig::class, 'bot_config_id');
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type === 'buy' ? 'success' : 'danger';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'primary',
            'closed' => 'secondary',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    public function getNetProfitAttribute(): float
    {
        return $this->profit + $this->commission + $this->swap;
    }
}
