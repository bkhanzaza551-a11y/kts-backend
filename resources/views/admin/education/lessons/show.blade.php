@extends('layouts.app')
@section('title', $lesson->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-list-check me-2 text-primary"></i>{{ $lesson->title }}</h4>
        <small class="text-secondary">{{ $course->title }} &middot; Lesson #{{ $lesson->sort_order }}</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->hasPermission('lessons_edit'))
        <a href="{{ route('admin.courses.lessons.edit', [$course, $lesson]) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if(auth()->user()->hasPermission('lessons_delete'))
        <form method="POST" action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}" class="d-inline" onsubmit="return confirm('Delete this lesson?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button></form>
        @endif
        <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Content</h6></div>
            <div class="card-body">
                @if($lesson->description)
                <p class="text-secondary mb-3">{{ $lesson->description }}</p>
                <hr>
                @endif
                @if($lesson->content)
                <div class="text-dark">{!! nl2br(e($lesson->content)) !!}</div>
                @else
                <p class="text-secondary mb-0">No content provided.</p>
                @endif
            </div>
        </div>

        @if($lesson->video_url)
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 14px;">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-play-circle-fill text-danger me-2"></i>Lesson Video</h6>
                <span class="badge bg-light text-dark border"><i class="bi bi-clock me-1"></i>{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' min' : 'Auto' }}</span>
            </div>
            <div class="card-body p-3">
                @php
                    $isDirectVideo = preg_match('/\.(mp4|webm|mov|m4v|ogg)(\?.*)?$/i', $lesson->video_url) || str_contains($lesson->video_url, '/storage/education/videos/');
                @endphp
                @if($isDirectVideo)
                    <div class="ratio ratio-16x9 bg-black rounded-3 overflow-hidden shadow-sm">
                        <video controls class="w-100 h-100" preload="metadata">
                            <source src="{{ $lesson->video_url }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                @else
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                            <i class="bi bi-link-45deg fs-4 text-primary flex-shrink-0"></i>
                            <a href="{{ $lesson->video_url }}" target="_blank" class="text-primary text-truncate fw-semibold">{{ $lesson->video_url }}</a>
                        </div>
                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-primary flex-shrink-0">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Open Video
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><small class="text-secondary d-block">Status</small>@if($lesson->is_published)<span class="badge bg-success">Published</span>@else<span class="badge bg-secondary">Draft</span>@endif</div>
                    <div class="col-6"><small class="text-secondary d-block">Type</small><span class="badge bg-success">Free</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Duration</small><span class="text-dark">{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' min' : 'N/A' }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Sort</small><span class="text-dark">#{{ $lesson->sort_order }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Views</small><span class="text-dark">{{ number_format($lesson->views_count) }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Created</small><span class="text-dark">{{ $lesson->created_at->format('M d, Y') }}</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
