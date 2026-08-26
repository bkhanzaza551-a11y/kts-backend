@extends('layouts.app')

@section('title', 'Legal Pages')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Legal Pages</h4>
    @if(auth()->user()->hasPermission('settings_manage'))
    <a href="{{ route('admin.legal-pages.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Page</a>
    @endif
</div>

<div class="row g-4">
    @forelse($pages as $page)
    <div class="col-md-6">
        <div class="card h-100 {{ $page->is_active ? 'border-success' : 'border-secondary' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="mb-1">{{ $page->title }}</h5>
                        <code class="text-secondary small">{{ $page->slug }}</code>
                    </div>
                    @if($page->is_active)
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-eye-slash me-1"></i>Draft</span>
                    @endif
                </div>
                <p class="text-secondary small mb-3">{{ Str::limit($page->summary ?? strip_tags($page->content), 120) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-secondary">
                        @if($page->editor)
                            Edited by {{ $page->editor->name }}
                        @endif
                        @if($page->last_published_at)
                            <br>Published {{ $page->last_published_at->diffForHumans() }}
                        @endif
                    </small>
                    <div class="d-flex gap-1">
                        @if(auth()->user()->hasPermission('settings_manage'))
                        <a href="{{ route('admin.legal-pages.edit', $page->slug) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                        @if($page->slug === 'privacy-policy' || $page->slug === 'terms-conditions')
                        <form method="POST" action="{{ route('admin.legal-pages.publish', $page->slug) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-send"></i> Publish</button>
                        </form>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5 text-secondary">
                <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                <p>No legal pages yet. Create your first one.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
