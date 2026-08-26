@extends('layouts.app')

@section('title', 'Edit - ' . $page->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $page->title }}</h4>
        <small class="text-secondary">Slug: <code>{{ $page->slug }}</code></small>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" action="{{ route('admin.legal-pages.publish', $page->slug) }}">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Publish</button>
        </form>
        <a href="{{ route('admin.legal-pages.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.legal-pages.update', $page->slug) }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-9">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Page Content</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $page->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Summary (for app preview)</label>
                        <textarea name="summary" class="form-control @error('summary') is-invalid @enderror" rows="2" maxlength="500">{{ old('summary', $page->summary) }}</textarea>
                        <small class="text-secondary">Short preview shown in the app. Leave empty to auto-generate from content.</small>
                        @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary">Content (HTML) <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="20" style="font-family:monospace;font-size:13px;" required>{{ old('content', $page->content) }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            {{-- Publish Settings --}}
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Settings</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive"><i class="bi bi-globe me-1"></i>Active (visible in app)</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
                </div>
            </div>

            {{-- Info --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <small class="text-secondary d-block">Created</small>
                            <small class="text-dark">{{ $page->created_at->format('M d, Y') }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block">Updated</small>
                            <small class="text-dark">{{ $page->updated_at->format('M d, Y') }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block">Published</small>
                            <small class="text-dark">{{ $page->last_published_at?->format('M d, Y') ?? 'Never' }}</small>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block">Editor</small>
                            <small class="text-dark">{{ $page->editor->name ?? 'N/A' }}</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- API Preview --}}
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">API Endpoint</h6></div>
                <div class="card-body">
                    <code class="small d-block mb-2">GET /api/v1/legal/{{ $page->slug }}</code>
                    <small class="text-secondary">Mobile app fetches from here</small>
                </div>
            </div>

            {{-- Delete --}}
            @if($page->slug !== 'privacy-policy' && $page->slug !== 'terms-conditions')
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-danger mb-2">Danger Zone</h6>
                    <form method="POST" action="{{ route('admin.legal-pages.destroy', $page->slug) }}" onsubmit="return confirm('Delete this page permanently?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1"></i>Delete</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>
@endsection
