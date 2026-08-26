<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'content',
        'video_url',
        'duration_minutes',
        'sort_order',
        'is_free',
        'is_published',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
        'is_free' => 'boolean',
        'is_published' => 'boolean',
        'views_count' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function getNextLesson()
    {
        return self::where('course_id', $this->course_id)
            ->where('sort_order', '>', $this->sort_order)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->first();
    }

    public function getPreviousLesson()
    {
        return self::where('course_id', $this->course_id)
            ->where('sort_order', '<', $this->sort_order)
            ->where('is_published', true)
            ->orderBy('sort_order', 'desc')
            ->first();
    }
}
