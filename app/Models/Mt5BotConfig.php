<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mt5BotConfig extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'mt5_account_number',
        'mt5_server',
        'bot_file_path',
        'api_key',
        'api_secret',
        'status',
        'mode',
        'auto_trade',
        'take_profit_pips',
        'stop_loss_pips',
        'max_daily_trades',
        'max_daily_loss',
        'balance',
        'equity',
        'total_profit',
        'total_loss',
        'total_trades',
        'winning_trades',
        'losing_trades',
        'last_connected_at',
        'last_trade_at',
        'error_message',
        'created_by',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
        'mt5_account_number',
        'mt5_server',
        'bot_file_path',
    ];

    protected $casts = [
        'auto_trade' => 'boolean',
        'take_profit_pips' => 'decimal:2',
        'stop_loss_pips' => 'decimal:2',
        'max_daily_trades' => 'integer',
        'max_daily_loss' => 'decimal:2',
        'balance' => 'decimal:2',
        'equity' => 'decimal:2',
        'total_profit' => 'decimal:2',
        'total_loss' => 'decimal:2',
        'total_trades' => 'integer',
        'winning_trades' => 'integer',
        'losing_trades' => 'integer',
        'last_connected_at' => 'datetime',
        'last_trade_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(Mt5BotLog::class, 'bot_config_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Mt5BotTrade::class, 'bot_config_id');
    }

    public function getWinRateAttribute(): float
    {
        if ($this->total_trades <= 0) return 0;
        return round(($this->winning_trades / $this->total_trades) * 100, 2);
    }

    public function getNetProfitAttribute(): float
    {
        return $this->total_profit - $this->total_loss;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'secondary',
            'error' => 'danger',
            default => 'secondary',
        };
    }

    public function getModeColorAttribute(): string
    {
        return match($this->mode) {
            'live' => 'danger',
            'demo' => 'info',
            'backtest' => 'warning',
            default => 'secondary',
        };
    }
}
