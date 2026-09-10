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
        'bot_file_name',
        'bot_file_size',
        'bot_file_type',
        'bot_file_uploaded_at',
        'api_key',
        'api_secret',
        'status',
        'mode',
        'auto_trade',
        'take_profit_pips',
        'stop_loss_pips',
        'max_daily_trades',
        'max_daily_loss',
        'base_balance',
        'base_lot_size',
        'whatsapp_number',
        'demo_server',
        'demo_account',
        'demo_email',
        'demo_phone',
        'demo_deposit',
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

    protected $appends = [
        'lot_size',
        'win_rate',
        'net_profit',
    ];

    protected $casts = [
        'auto_trade' => 'boolean',
        'base_balance' => 'decimal:2',
        'base_lot_size' => 'decimal:2',
        'demo_deposit' => 'decimal:2',
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
        'bot_file_uploaded_at' => 'datetime',
    ];

    public function hasBotFile(): bool
    {
        return !empty($this->bot_file_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->bot_file_path);
    }

    public function getBotFileInfo(): ?array
    {
        if (!$this->hasBotFile()) {
            return null;
        }

        $extension = pathinfo($this->bot_file_name ?: $this->bot_file_path, PATHINFO_EXTENSION) ?: 'file';

        return [
            'available' => true,
            'file_name' => $this->bot_file_name ?: basename($this->bot_file_path),
            'file_size' => $this->bot_file_size ?: $this->calculateFileSize(),
            'file_type' => strtoupper($this->bot_file_type ?: $extension),
            'file_extension' => strtolower($extension),
            'uploaded_at' => $this->bot_file_uploaded_at?->toISOString() ?: $this->updated_at?->toISOString(),
        ];
    }

    protected function calculateFileSize(): string
    {
        try {
            $bytes = \Illuminate\Support\Facades\Storage::disk('public')->size($this->bot_file_path);
            if ($bytes >= 1048576) {
                return round($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return round($bytes / 1024, 2) . ' KB';
            }
            return $bytes . ' B';
        } catch (\Throwable $e) {
            return 'Unknown Size';
        }
    }

    public function getLotSizeAttribute(): float
    {
        return (float) ($this->base_lot_size ?? 0.01);
    }

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
        if (!$this->total_trades || $this->total_trades <= 0) return 0;
        return round(($this->winning_trades / $this->total_trades) * 100, 2);
    }

    public function getNetProfitAttribute(): float
    {
        return (float) ($this->total_profit - $this->total_loss);
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
