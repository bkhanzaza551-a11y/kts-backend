<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LegalPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'summary',
        'is_active',
        'last_published_at',
        'last_edited_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_published_at' => 'datetime',
    ];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    public function isPublished(): bool
    {
        return $this->is_active && $this->last_published_at !== null;
    }
}
