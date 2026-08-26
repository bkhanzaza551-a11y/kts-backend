<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'transaction_id', 'amount', 'currency', 'gateway', 'status',
        'plan_type', 'plan_duration_days', 'description', 'proof_file',
        'approved_by', 'approved_at', 'admin_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'plan_duration_days' => 'integer',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'info',
            default => 'secondary',
        };
    }

    public function getGatewayColorAttribute(): string
    {
        return match($this->gateway) {
            'jazzcash' => 'danger',
            'easypaisa' => 'success',
            'bank_transfer' => 'primary',
            'manual' => 'secondary',
            default => 'secondary',
        };
    }
}
