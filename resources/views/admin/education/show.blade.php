@extends('layouts.app')
@section('title', $course->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-book me-2 text-primary"></i>{{ $course->title }}</h4>
        <small class="text-secondary">{{ $course->category?->name ?? 'Unknown' }} &middot; {{ ucfirst($course->difficulty) }}</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(!$course->is_published && auth()->user()->hasPermission('education_edit'))
        <form method="POST" action="{{ route('admin.courses.publish', $course) }}" class="d-inline">@csrf<button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Publish</button></form>
        @endif
        @if($course->is_published && auth()->user()->hasPermission('education_edit'))
        <form method="POST" action="{{ route('admin.courses.unpublish', $course) }}" class="d-inline">@csrf<button type="submit" class="btn btn-warning"><i class="bi bi-stop-circle me-1"></i>Unpublish</button></form>
        @endif
        @if(auth()->user()->hasPermission('lessons_create'))
        <a href="{{ route('admin.courses.lessons.create', $course) }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>Add Lesson</a>
        @endif
        @if(auth()->user()->hasPermission('education_edit'))
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        @if(auth()->user()->hasPermission('education_delete'))
        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="d-inline" onsubmit="return confirm('Delete this course and all its lessons?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button></form>
        @endif
        <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="text-secondary mb-2">Description</h6>
                <p class="text-dark">{{ $course->description ?? 'No description provided.' }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Lessons ({{ $lessonsCount }})</h6>
                @if(auth()->user()->hasPermission('lessons_create'))
                <a href="{{ route('admin.courses.lessons.create', $course) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus"></i></a>
                @endif
            </div>
            <div class="card-body p-0">
                @if($course->lessons->count())
                <div class="list-group list-group-flush">
                    @foreach($course->lessons as $i => $lesson)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-secondary me-2">{{ $i + 1 }}.</span>
                            <a href="{{ route('admin.courses.lessons.show', [$course, $lesson]) }}" class="text-dark text-decoration-none">{{ $lesson->title }}</a>
                            @if(!$lesson->is_published)<span class="badge bg-secondary ms-1">Draft</span>@endif
                            @if($lesson->is_free)<span class="badge bg-success ms-1">Free</span>@endif
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <small class="text-secondary">{{ $lesson->duration_minutes ? $lesson->duration_minutes . 'm' : '-' }}</small>
                            @if(auth()->user()->hasPermission('lessons_edit'))
                            <a href="{{ route('admin.courses.lessons.edit', [$course, $lesson]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center text-secondary py-4"><i class="bi bi-list-ul fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No lessons yet.</p></div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Details</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><small class="text-secondary d-block">Status</small>@if($course->is_published)<span class="badge bg-success">Published</span>@else<span class="badge bg-secondary">Draft</span>@endif</div>
                    <div class="col-6"><small class="text-secondary d-block">Type</small>@if($course->is_free)<span class="badge bg-success">Free</span>@else<span class="badge bg-info">${{ number_format($course->price, 2) }}</span>@endif</div>
                    <div class="col-6"><small class="text-secondary d-block">Difficulty</small>@php $dc=match($course->difficulty){'beginner'=>'bg-success','intermediate'=>'bg-warning text-dark','advanced'=>'bg-danger',default=>'bg-secondary'};@endphp<span class="badge {{ $dc }}">{{ ucfirst($course->difficulty) }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Featured</small>@if($course->is_featured)<span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i></span>@else<span class="text-secondary">No</span>@endif</div>
                    <div class="col-6"><small class="text-secondary d-block">Lessons</small><span class="text-dark">{{ $lessonsCount }} ({{ $publishedLessonsCount }} published)</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Duration</small><span class="text-dark">{{ $totalDuration }} min</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Views</small><span class="text-dark">{{ number_format($course->views_count) }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Enrollments</small><span class="text-dark">{{ number_format($course->enrollments_count) }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Created</small><span class="text-dark">{{ $course->created_at->format('M d, Y') }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Published</small><span class="text-dark">{{ $course->published_at?->format('M d, Y') ?? 'N/A' }}</span></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Created By</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;"><span class="text-white small fw-bold">{{ strtoupper(substr($course->creator?->name ?? 'U', 0, 1)) }}</span></div>
                    <div><span class="text-dark">{{ $course->creator?->name ?? 'Deleted User' }}</span><div class="text-secondary small">{{ $course->creator?->email ?? '' }}</div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
