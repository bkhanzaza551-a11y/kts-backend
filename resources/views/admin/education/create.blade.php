@extends('layouts.app')
@section('title', 'Create Course')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-book me-2 text-primary"></i>Create Course</h4>
    <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="{{ route('admin.courses.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Course Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5" maxlength="10000">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select...</option>
                                @foreach($categories as $cat)<option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>@endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Difficulty <span class="text-danger">*</span></label>
                            <select name="difficulty" class="form-select @error('difficulty') is-invalid @enderror" required>
                                <option value="beginner" {{ old('difficulty', 'beginner') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                                <option value="intermediate" {{ old('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                <option value="advanced" {{ old('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                            </select>
                            @error('difficulty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Est. Hours</label>
                            <input type="number" name="estimated_hours" class="form-control @error('estimated_hours') is-invalid @enderror" value="{{ old('estimated_hours') }}" min="0">
                            @error('estimated_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Price ($)</label>
                            <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', '0') }}" min="0" step="0.01" id="priceInput">
                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-8 d-flex align-items-end gap-3">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_free" value="0">
                                <input type="checkbox" name="is_free" class="form-check-input" id="isFree" value="1" {{ old('is_free', true) ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="isFree">Free Course</label>
                            </div>
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="isFeatured"><i class="bi bi-star-fill text-warning"></i> Featured</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Publishing</h6></div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label text-secondary" for="isPublished">Publish Immediately</label>
                    </div>
                </div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Create Course</button></div>
        </div>
    </div>
</form>
@push('scripts')
<script>
function togglePrice() {
    const isFree = document.getElementById('isFree');
    const priceInput = document.getElementById('priceInput');
    if (isFree && priceInput) {
        priceInput.disabled = isFree.checked;
        if (isFree.checked) priceInput.value = '0';
    }
}
document.getElementById('isFree')?.addEventListener('change', togglePrice);
togglePrice();
</script>
@endpush
@endsection
