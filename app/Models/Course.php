<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'slug',
        'description',
        'thumbnail',
        'difficulty',
        'estimated_hours',
        'is_featured',
        'is_published',
        'published_at',
        'sort_order',
    ];

    protected $casts = [
        'estimated_hours' => 'integer',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'enrollments_count' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(EducationCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_id')->orderBy('sort_order');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $slug = Str::slug($course->title);
                $existing = static::where('slug', $slug)->first();
                $course->slug = $existing ? $slug . '-' . ($existing->id + 1) : $slug;
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title') && !$course->isDirty('slug')) {
                $newSlug = Str::slug($course->title);
                $existing = static::where('slug', $newSlug)->where('id', '!=', $course->id)->first();
                $course->slug = $existing ? $newSlug . '-' . $course->id : $newSlug;
            }
        });
    }

    public function publish(): bool
    {
        if ($this->is_published) {
            return false;
        }

        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return true;
    }

    public function unpublish(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);

        return true;
    }
}
