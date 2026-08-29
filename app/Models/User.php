<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'google_id',
        'auth_provider',
        'demo_account_id',
        'demo_account_server',
        'real_account_id',
        'real_account_server',
        'broker_name',
        'email_verified_at',
        'remember_token',
        'chat_badge',
        'badge_color',
        'last_login_at',
        'last_login_ip',
        'status',
        'is_banned',
        'is_premium',
        'premium_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'premium_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_banned' => 'boolean',
            'is_premium' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->roles()->where('slug', 'super-admin')->exists();
    }

    public function isPremiumActive(): bool
    {
        return $this->is_premium
            && $this->premium_expires_at
            && $this->premium_expires_at->isFuture();
    }
}
