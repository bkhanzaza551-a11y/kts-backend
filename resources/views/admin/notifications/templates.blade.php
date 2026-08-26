@extends('layouts.app')
@section('title', 'Notification Templates')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2 text-info"></i>Notification Templates</h4>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Create Template</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.notifications.store-template') }}">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" required maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary">Type</label>
                    <select name="type" class="form-select">
                        <option value="info">Info</option>
                        <option value="success">Success</option>
                        <option value="warning">Warning</option>
                        <option value="danger">Danger</option>
                    </select>
                </div>
                <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create</button></div>
            </div>
            <div class="mt-3">
                <label class="form-label text-secondary">Body <span class="text-danger">*</span></label>
                <textarea name="body" class="form-control @error('body') is-invalid @enderror" rows="3" maxlength="2000" required placeholder="Enter template body...">{{ old('body') }}</textarea>
                @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Name</th><th>Title</th><th>Type</th><th>Status</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($templates as $t)
                    <tr>
                        <td class="ps-3 text-dark fw-semibold">{{ $t->name }}</td>
                        <td class="text-secondary">{{ Str::limit($t->title, 30) }}</td>
                        <td><span class="badge bg-{{ $t->type === 'danger' ? 'danger' : ($t->type === 'warning' ? 'warning' : 'info') }}">{{ ucfirst($t->type) }}</span></td>
                        <td><span class="badge bg-{{ $t->is_active ? 'success' : 'secondary' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="pe-3">
                            <form method="POST" action="{{ route('admin.notifications.destroy-template', $t) }}" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No templates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($templates->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $templates->firstItem() }}-{{ $templates->lastItem() }} of {{ $templates->total() }}</small>
    {{ $templates->links() }}
</div>
@endif
@endsection
