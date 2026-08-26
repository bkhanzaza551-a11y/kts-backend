<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = ['name', 'slug', 'title', 'body', 'type', 'event', 'channel', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
