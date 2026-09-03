@extends('layouts.app')
@section('title', 'Lessons - ' . $course->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Lessons</h4>
        <a href="{{ route('admin.courses.show', $course) }}" class="text-secondary text-decoration-none small"><i class="bi bi-arrow-left me-1"></i>{{ $course->title }}</a>
    </div>
    @if(auth()->user()->hasPermission('lessons_create'))
    <a href="{{ route('admin.courses.lessons.create', $course) }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Lesson</a>
    @endif
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3" style="width:50px;">#</th><th>Title</th><th>Duration</th><th>Type</th><th>Status</th><th>Views</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($lessons as $lesson)
                    <tr>
                        <td class="ps-3 text-secondary">{{ $lesson->sort_order }}</td>
                        <td><a href="{{ route('admin.courses.lessons.show', [$course, $lesson]) }}" class="text-dark text-decoration-none fw-semibold">{{ $lesson->title }}</a></td>
                        <td class="text-secondary">{{ $lesson->duration_minutes ? $lesson->duration_minutes . 'm' : '-' }}</td>
                        <td><span class="badge bg-success">Free</span></td>
                        <td>@if($lesson->is_published)<span class="badge bg-success">Published</span>@else<span class="badge bg-secondary">Draft</span>@endif</td>
                        <td class="text-secondary">{{ number_format($lesson->views_count) }}</td>
                        <td class="pe-3"><div class="d-flex gap-1">
                            @if(auth()->user()->hasPermission('lessons_edit'))<a href="{{ route('admin.courses.lessons.edit', [$course, $lesson]) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>@endif
                            @if(auth()->user()->hasPermission('lessons_delete'))<form method="POST" action="{{ route('admin.courses.lessons.destroy', [$course, $lesson]) }}" class="d-inline" onsubmit="return confirm('Delete this lesson?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>@endif
                        </div></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4"><i class="bi bi-list-ul fs-1 d-block mb-2 opacity-50"></i>No lessons yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($lessons->hasPages())<div class="d-flex justify-content-between align-items-center mt-3"><small class="text-secondary">Showing {{ $lessons->firstItem() }}-{{ $lessons->lastItem() }} of {{ $lessons->total() }}</small>{{ $lessons->links() }}</div>@endif
@endsection
