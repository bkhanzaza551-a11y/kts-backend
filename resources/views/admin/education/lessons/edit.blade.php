@extends('layouts.app')
@section('title', 'Edit Lesson - ' . $lesson->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-primary"></i>Edit Lesson</h4><a href="{{ route('admin.courses.show', $course) }}" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $course->title }}</a></div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.courses.lessons.show', [$course, $lesson]) }}" class="btn btn-outline-info">View</a>
        <a href="{{ route('admin.courses.lessons.index', $course) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>
<form method="POST" action="{{ route('admin.courses.lessons.update', [$course, $lesson]) }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Lesson Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label text-secondary">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $lesson->title) }}" required maxlength="255">@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label text-secondary">Description</label><textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="1000">{{ old('description', $lesson->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label text-secondary">Content</label><textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="12" maxlength="50000">{{ old('content', $lesson->content) }}</textarea>@error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label text-secondary">Video URL</label><input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $lesson->video_url) }}" maxlength="500">@error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-3"><label class="form-label text-secondary">Duration (min)</label><input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" value="{{ old('duration_minutes', $lesson->duration_minutes) }}" min="0">@error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-3"><label class="form-label text-secondary">Sort Order</label><input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $lesson->sort_order) }}" min="0">@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4"><div class="card-body">
                <div class="form-check form-switch"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" {{ old('is_published', $lesson->is_published) ? 'checked' : '' }}><label class="form-check-label text-secondary" for="isPublished">Published</label></div>
                <div class="mt-3"><small class="text-secondary">Views: {{ number_format($lesson->views_count) }}</small></div>
            </div></div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Update Lesson</button></div>
        </div>
    </div>
</form>
@endsection
