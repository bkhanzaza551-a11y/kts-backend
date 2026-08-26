@extends('layouts.app')
@section('title', 'Edit - ' . $category->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-tags me-2 text-primary"></i>Edit Category</h4>
    <a href="{{ route('admin.education-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="{{ route('admin.education-categories.update', $category) }}">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Category Details</h6></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label text-secondary">Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $category->name) }}" required maxlength="255">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="mb-3"><label class="form-label text-secondary">Description</label><textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="1000">{{ old('description', $category->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label text-secondary">Icon (Bootstrap)</label><input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ old('icon', $category->icon) }}" maxlength="50">@error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label class="form-label text-secondary">Color <span class="text-danger">*</span></label><div class="input-group"><input type="color" name="color" class="form-control form-control-color @error('color') is-invalid @enderror" value="{{ old('color', $category->color) }}" id="colorPicker"><input type="text" class="form-control" value="{{ old('color', $category->color) }}" maxlength="7" id="colorText" oninput="document.getElementById('colorPicker').value=this.value"></div>@error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4"><label class="form-label text-secondary">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order) }}" min="0"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4"><div class="card-body">
                <div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" class="form-check-input" id="isActive" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}><label class="form-check-label text-secondary" for="isActive">Active</label></div>
                <div class="mt-3"><small class="text-secondary">Slug: <code>{{ $category->slug }}</code></small></div>
                <div class="mt-2"><small class="text-secondary">Courses: <span class="text-dark">{{ $category->courses_count }}</span></small></div>
            </div></div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Update Category</button></div>
        </div>
    </div>
</form>
@push('scripts')
<script>
document.getElementById('colorPicker')?.addEventListener('input', function() {
    document.getElementById('colorText').value = this.value;
});
</script>
@endpush
@endsection
