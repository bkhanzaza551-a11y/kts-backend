@extends('layouts.app')

@section('title', 'Create Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create Role</h4>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form method="POST" action="{{ route('admin.roles.store') }}">
    @csrf
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Role Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="name" class="form-label text-secondary">Role Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="slug" class="form-label text-secondary">Slug *</label>
                    <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" required>
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="description" class="form-label text-secondary">Description</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Assign Permissions</h6>
        </div>
        <div class="card-body">
            @foreach($grouped as $module => $modulePermissions)
            <div class="mb-3">
                <h6 class="text-info text-uppercase small">{{ $module }}</h6>
                <div class="row">
                    @foreach($modulePermissions as $permission)
                    <div class="col-md-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->action }}</label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @error('permissions') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Role</button>
    </div>
</form>

@push('scripts')
<script>
document.getElementById('name').addEventListener('input', function() {
    document.getElementById('slug').value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
});
</script>
@endpush
@endsection
