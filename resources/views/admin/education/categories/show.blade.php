@extends('layouts.app')
@section('title', $category->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h4 class="mb-0 fw-bold"><span class="badge me-2" style="background-color:{{ $category?->color ?? '#6c757d' }}20;color:{{ $category?->color ?? '#6c757d' }};border:1px solid {{ $category?->color ?? '#6c757d' }}40;">@if($category->icon)<i class="bi bi-{{ $category->icon }} me-1"></i>@endif{{ $category->name }}</span></h4><small class="text-secondary">{{ $category->description ?? 'No description' }}</small></div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('education_categories_edit'))<a href="{{ route('admin.education-categories.edit', $category) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>@endif
        @if(auth()->user()->hasPermission('education_categories_delete'))
        <form method="POST" action="{{ route('admin.education-categories.destroy', $category) }}" class="d-inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button></form>
        @endif
        <a href="{{ route('admin.education-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card stat-card border-0"><div class="card-body p-3 text-center"><h3 class="text-dark mb-0 fw-bold">{{ $category->courses_count }}</h3><small class="text-secondary">Courses</small></div></div></div>
    <div class="col-md-4"><div class="card stat-card border-0"><div class="card-body p-3 text-center"><h3 class="text-secondary mb-0 fw-bold">{{ $category->sort_order }}</h3><small class="text-secondary">Sort Order</small></div></div></div>
    <div class="col-md-4"><div class="card stat-card border-0"><div class="card-body p-3 text-center">@if($category->is_active)<h3 class="text-success mb-0 fw-bold">Active</h3>@else<h3 class="text-secondary mb-0 fw-bold">Inactive</h3>@endif<small class="text-secondary">Status</small></div></div></div>
</div>
<div class="card">
    <div class="card-header"><h6 class="mb-0">Courses in This Category</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Course</th><th>Difficulty</th><th>Lessons</th><th>Views</th><th>Status</th><th class="pe-3">Date</th></tr></thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-3"><a href="{{ route('admin.courses.show', $course) }}" class="text-dark text-decoration-none fw-semibold">{{ $course->title }}</a></td>
                        <td>@php $dc=match($course->difficulty){'beginner'=>'bg-success','intermediate'=>'bg-warning text-dark','advanced'=>'bg-danger',default=>'bg-secondary'};@endphp<span class="badge {{ $dc }}">{{ ucfirst($course->difficulty) }}</span></td>
                        <td class="text-dark">{{ $course->lessons_count }}</td>
                        <td class="text-secondary">{{ number_format($course->views_count) }}</td>
                        <td>@if($course->is_published)<span class="badge bg-success">Published</span>@else<span class="badge bg-secondary">Draft</span>@endif</td>
                        <td class="pe-3 text-secondary small">{{ $course->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">No courses in this category.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($courses->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $courses->firstItem() }}-{{ $courses->lastItem() }} of {{ $courses->total() }}</small>
    {{ $courses->withQueryString()->links() }}
</div>
@endif
@endsection
