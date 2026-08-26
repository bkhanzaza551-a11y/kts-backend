@extends('layouts.app')
@section('title', 'Edit Pack - ' . $pack->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-pencil me-2 text-warning"></i>Edit Pack: {{ $pack->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.chat.stickers.show-pack', $pack) }}" class="btn btn-outline-info btn-sm">View Stickers</a>
        <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.chat.stickers.update-pack', $pack) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Pack Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Pack Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $pack->name) }}" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="500">{{ old('description', $pack->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Thumbnail</label>
                            <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                            <small class="text-secondary">Leave empty to keep current</small>
                            @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" {{ old('is_active', $pack->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $pack->sort_order) }}" min="0">
                        </div>
                    </div>
                    @if($pack->thumbnail)
                    <div class="mt-3">
                        <small class="text-secondary">Current thumbnail:</small><br>
                        <img src="{{ asset('storage/' . $pack->thumbnail) }}" alt="" style="width:64px;height:64px;border-radius:10px;object-fit:cover;margin-top:4px;">
                    </div>
                    @endif
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update Pack</button>
                <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="text-secondary mb-2"><i class="bi bi-emoji-smile fs-1"></i></div>
                <h6 class="text-dark">{{ $pack->stickers_count }} Stickers</h6>
                <a href="{{ route('admin.chat.stickers.show-pack', $pack) }}" class="btn btn-sm btn-outline-primary mt-2">Manage Stickers</a>
            </div>
        </div>
    </div>
</div>
@endsection
