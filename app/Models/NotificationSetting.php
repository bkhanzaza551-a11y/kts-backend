<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'category',
        'icon',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public static function isEnabled($slug): bool
    {
        $setting = static::where('slug', $slug)->first();
        return $setting ? $setting->is_enabled : true;
    }
}
