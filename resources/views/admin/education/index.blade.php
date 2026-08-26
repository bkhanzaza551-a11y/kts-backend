@extends('layouts.app')
@section('title', 'Education Management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-book me-2 text-primary"></i>Education Management</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.education-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-tags me-1"></i>Categories</a>
        @if(auth()->user()->hasPermission('education_create'))
        <a href="{{ route('admin.courses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Course</a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total']) }}</h3><small class="text-secondary">Total</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ number_format($stats['published']) }}</h3><small class="text-secondary">Published</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['draft']) }}</h3><small class="text-secondary">Draft</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-warning mb-0 fw-bold">{{ number_format($stats['total_lessons']) }}</h3><small class="text-secondary">Lessons</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">{{ number_format($stats['total_views']) }}</h3><small class="text-secondary">Views</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ number_format($stats['total_enrollments']) }}</h3><small class="text-secondary">Enrollments</small>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-secondary">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Title..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Difficulty</label>
                <select name="difficulty" class="form-select">
                    <option value="">All</option>
                    <option value="beginner" {{ request('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="intermediate" {{ request('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="advanced" {{ request('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Status</label>
                <select name="is_published" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Published</option>
                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Type</label>
                <select name="is_free" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('is_free') === '1' ? 'selected' : '' }}>Free</option>
                    <option value="0" {{ request('is_free') === '0' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button></div>
        </form>
        @if(request()->hasAny(['search','category_id','difficulty','is_published','is_free']))
        <div class="mt-2"><a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a></div>
        @endif
    </div>
</div>

@php
    $currentSort = request('sort', 'created_at');
    $currentDir = request('dir', 'desc');
    $sortLink = function ($col) use ($currentSort, $currentDir) {
        $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
        return route('admin.courses.index', array_merge(request()->query(), ['sort' => $col, 'dir' => $newDir]));
    };
@endphp

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3"><a href="{{ $sortLink('title') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">Course @if($currentSort==='title')<i class="bi bi-chevron-{{ $currentDir==='asc'?'up':'down' }}" style="font-size:0.6rem;"></i>@else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif</a></th>
                    <th>Category</th>
                    <th>Difficulty</th>
                    <th>Lessons</th>
                    <th>Type</th>
                    <th><a href="{{ $sortLink('views_count') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">Views @if($currentSort==='views_count')<i class="bi bi-chevron-{{ $currentDir==='asc'?'up':'down' }}" style="font-size:0.6rem;"></i>@else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif</a></th>
                    <th>Status</th>
                    <th class="pe-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.courses.show', $course) }}" class="text-dark text-decoration-none fw-semibold">{{ $course->title }}</a>
                            @if($course->is_featured)<span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;"><i class="bi bi-star-fill"></i></span>@endif
                            <div class="text-secondary small">{{ Str::limit($course->description, 50) }}</div>
                        </td>
                        <td><span class="badge" style="background-color:{{ $course->category?->color ?? '#6c757d' }}20;color:{{ $course->category?->color ?? '#6c757d' }};border:1px solid {{ $course->category?->color ?? '#6c757d' }}40;">{{ $course->category?->name ?? 'Unknown' }}</span></td>
                        <td>
                            @php $dc = match($course->difficulty){'beginner'=>'bg-success','intermediate'=>'bg-warning text-dark','advanced'=>'bg-danger',default=>'bg-secondary'}; @endphp
                            <span class="badge {{ $dc }}">{{ ucfirst($course->difficulty) }}</span>
                        </td>
                        <td><span class="text-dark">{{ $course->lessons_count }}</span></td>
                        <td>@if($course->is_free)<span class="badge bg-success">Free</span>@else<span class="badge bg-info">${{ number_format($course->price, 2) }}</span>@endif</td>
                        <td class="text-secondary small">{{ number_format($course->views_count) }}</td>
                        <td>@if($course->is_published)<span class="badge bg-success">Published</span>@else<span class="badge bg-secondary">Draft</span>@endif</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                @if(auth()->user()->hasPermission('education_edit'))
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-secondary py-4"><i class="bi bi-book fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No courses found.</p></td></tr>
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
