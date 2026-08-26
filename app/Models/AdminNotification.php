<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminNotification extends Model
{
    protected $fillable = [
        'title', 'body', 'type', 'target', 'target_user_id', 'target_role_id', 'sent_count', 'is_sent', 'sent_by',
    ];

    protected $casts = ['is_sent' => 'boolean', 'sent_count' => 'integer'];

    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sent_by'); }
}
