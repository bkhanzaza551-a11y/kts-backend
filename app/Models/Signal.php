<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'symbol',
        'direction',
        'entry_price',
        'take_profit',
        'stop_loss',
        'status',
        'result',
        'pips_result',
        'close_price',
        'published_at',
        'closed_at',
        'expires_at',
        'is_featured',
        'views_count',
        'followers_notified',
    ];

    protected $casts = [
        'entry_price' => 'decimal:5',
        'take_profit' => 'decimal:5',
        'stop_loss' => 'decimal:5',
        'pips_result' => 'decimal:2',
        'close_price' => 'decimal:5',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'followers_notified' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(SignalCategory::class, 'signal_category_signal');
    }

    public function isWin(): bool
    {
        return $this->result === 'win';
    }

    public function isLoss(): bool
    {
        return $this->result === 'loss';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' || $this->status === 'draft';
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function publish(): bool
    {
        if (in_array($this->status, ['active', 'closed', 'cancelled'])) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'published_at' => now(),
        ]);

        return true;
    }

    public function close(string $result, ?float $pipsResult = null, ?float $closePrice = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'closed',
            'result' => $result,
            'pips_result' => $pipsResult,
            'close_price' => $closePrice,
            'closed_at' => now(),
        ]);

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
