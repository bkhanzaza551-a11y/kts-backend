<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTradingTip extends Model
{
    protected $fillable = ['tip', 'category', 'is_sent', 'sent_at'];

    protected $casts = ['is_sent' => 'boolean', 'sent_at' => 'datetime'];
}
