@extends('layouts.app')
@section('title', 'Pack: ' . $pack->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">
            @if($pack->thumbnail)<img src="{{ asset('storage/' . $pack->thumbnail) }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;vertical-align:middle;" class="me-2">@endif
            {{ $pack->name }}
        </h4>
        <small class="text-secondary">{{ $pack->description ?? 'No description' }} &middot; {{ $stickers->total() }} stickers</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.chat.stickers.edit-pack', $pack) }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-pencil me-1"></i>Edit Pack</a>
        <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Upload Stickers</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chat.stickers.upload') }}" enctype="multipart/form-data" id="uploadForm">
            @csrf
            <input type="hidden" name="pack_id" value="{{ $pack->id }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-secondary">Sticker Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255" placeholder="e.g. Happy, Trading, Fire">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5">
                    <label class="form-label text-secondary">Images <span class="text-danger">*</span></label>
                    <input type="file" name="images[]" class="form-control @error('images') is-invalid @enderror" multiple accept="image/*" required id="imageInput">
                    <small class="text-secondary">Select 1-20 images. PNG, JPG, GIF, WebP. Max 20MB each.</small>
                    @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('images.0')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary" id="uploadBtn"><i class="bi bi-upload me-1"></i>Upload</button>
                </div>
            </div>
            <div id="previewGrid" class="row g-2 mt-3" style="display:none;"></div>
        </form>
    </div>
</div>

@if($stickers->count())
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Stickers in this Pack</h6>
        <small class="text-secondary">{{ $stickers->total() }} total</small>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chat.stickers.bulk-delete') }}" id="bulkDeleteForm">
            @csrf
            <div id="bulkDeleteBar" class="mb-3" style="display:none;">
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete selected stickers?')">
                    <i class="bi bi-trash me-1"></i>Delete Selected (<span id="selectedCount">0</span>)
                </button>
            </div>
            <div class="row g-3">
                @foreach($stickers as $sticker)
                <div class="col-xl-2 col-md-3 col-4" id="sticker-{{ $sticker->id }}">
                    <div class="card h-100 {{ !$sticker->is_active ? 'opacity-50' : '' }}" style="border:1px solid #e5e7eb;">
                        <div class="card-body p-2 text-center">
                            <input type="checkbox" name="sticker_ids[]" value="{{ $sticker->id }}" class="form-check-input sticker-checkbox mb-2" style="display:none;">
                            <div class="sticker-img-wrapper position-relative mb-2" style="cursor:pointer;" onclick="this.querySelector('input').checked=!this.querySelector('input').checked;toggleBulkBar();">
                                <input type="checkbox" name="sticker_ids[]" value="{{ $sticker->id }}" class="sticker-checkbox d-none">
                                <img src="{{ asset('storage/' . $sticker->image_url) }}" alt="{{ $sticker->name }}" style="width:64px;height:64px;object-fit:contain;border-radius:8px;background:#f3f4f6;padding:4px;">
                                <div class="sticker-check position-absolute top-0 end-0 bg-primary rounded-circle d-none" style="width:18px;height:18px;">
                                    <i class="bi bi-check text-white" style="font-size:0.6rem;"></i>
                                </div>
                            </div>
                            <div class="text-truncate text-secondary small" style="max-width:100%;">{{ $sticker->name }}</div>
                            <div class="text-secondary" style="font-size:0.65rem;">{{ $sticker->file_size ?? '-' }} &middot; {{ $sticker->usage_count }}x</div>
                            <div class="d-flex gap-1 justify-content-center mt-1">
                                <form method="POST" action="{{ route('admin.chat.stickers.toggle-sticker', $sticker) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $sticker->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }}" style="font-size:0.6rem;padding:0.1rem 0.3rem;" title="{{ $sticker->is_active ? 'Active' : 'Inactive' }}">
                                        <i class="bi bi-{{ $sticker->is_active ? 'check-circle' : 'x-circle' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.chat.stickers.destroy-sticker', $sticker) }}" class="d-inline" onsubmit="return confirm('Delete this sticker?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.6rem;padding:0.1rem 0.3rem;" title="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </form>
    </div>
</div>
@if($stickers->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $stickers->firstItem() }}-{{ $stickers->lastItem() }} of {{ $stickers->total() }}</small>
    {{ $stickers->links() }}
</div>
@endif
@else
<div class="card">
    <div class="card-body text-center text-secondary py-5">
        <i class="bi bi-emoji-smile fs-1 d-block mb-2 opacity-50"></i>
        <p class="mb-0">No stickers in this pack yet. Upload some above!</p>
    </div>
</div>
@endif

@push('scripts')
<script>
document.getElementById('imageInput')?.addEventListener('change', function(e) {
    const grid = document.getElementById('previewGrid');
    grid.innerHTML = '';
    grid.style.display = e.target.files.length ? 'flex' : 'none';
    Array.from(e.target.files).forEach(file => {
        const col = document.createElement('div');
        col.className = 'col-auto';
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.cssText = 'width:64px;height:64px;object-fit:contain;border-radius:8px;border:2px solid #e5e7eb;background:#f9fafb;padding:4px;';
        col.appendChild(img);
        const name = document.createElement('div');
        name.className = 'text-center text-secondary small text-truncate mt-1';
        name.style.maxWidth = '64px';
        name.textContent = file.name.substring(0, 10);
        col.appendChild(name);
        grid.appendChild(col);
    });
});

function toggleBulkBar() {
    const checked = document.querySelectorAll('.sticker-checkbox:checked').length;
    document.getElementById('bulkDeleteBar').style.display = checked > 0 ? 'block' : 'none';
    document.getElementById('selectedCount').textContent = checked;
}

document.querySelectorAll('.sticker-img-wrapper').forEach(el => {
    el.addEventListener('click', function() {
        const cb = this.querySelector('input[type=checkbox]');
        const check = this.querySelector('.sticker-check');
        if (cb.checked) {
            check.classList.remove('d-none');
            this.closest('.card').style.border = '2px solid var(--bs-primary)';
        } else {
            check.classList.add('d-none');
            this.closest('.card').style.border = '1px solid #e5e7eb';
        }
    });
});
</script>
@endpush
@endsection
