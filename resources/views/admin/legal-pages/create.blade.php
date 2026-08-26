@extends('layouts.app')

@section('title', 'Create Legal Page')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-plus me-2 text-primary"></i>Create Legal Page</h4>
    <a href="{{ route('admin.legal-pages.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('admin.legal-pages.store') }}">
    @csrf

    <div class="row g-4">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Slug (URL friendly) <span class="text-danger">*</span></label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="privacy-policy" pattern="[a-z0-9\-]+" required>
                        <small class="text-secondary">Lowercase, hyphens only. Example: <code>privacy-policy</code></small>
                        @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Summary</label>
                        <textarea name="summary" class="form-control" rows="2" maxlength="500">{{ old('summary') }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label text-secondary">Content (HTML) <span class="text-danger">*</span></label>
                        <textarea name="content" class="form-control" rows="20" style="font-family:monospace;font-size:13px;" required>{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg me-1"></i>Create Page</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
