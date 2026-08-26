@extends('layouts.app')
@section('title', 'Create Sticker Pack')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-emoji-smile me-2 text-warning"></i>Create Sticker Pack</h4>
    <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.chat.stickers.store-pack') }}" enctype="multipart/form-data">
            @csrf
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Pack Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Pack Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Emojis, Trading, Fun">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="500" placeholder="Optional description...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Pack Thumbnail</label>
                        <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                        <small class="text-secondary">PNG, JPG, GIF or WebP. Max 2MB. Recommended: 128x128px</small>
                        @error('thumbnail')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div id="thumbPreview" class="mt-2"></div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Create Pack</button>
                <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-dark mb-2"><i class="bi bi-info-circle me-1"></i>Tips</h6>
                <ul class="text-secondary small mb-0">
                    <li>Upload stickers after creating the pack</li>
                    <li>Use PNG with transparent background</li>
                    <li>Recommended size: 256x256px</li>
                    <li>Max file size: 5MB per sticker</li>
                    <li>You can upload up to 20 stickers at once</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('input[name="thumbnail"]')?.addEventListener('change', function(e) {
    const preview = document.getElementById('thumbPreview');
    preview.innerHTML = '';
    if (e.target.files[0]) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(e.target.files[0]);
        img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:12px;border:2px solid #e5e7eb;';
        preview.appendChild(img);
    }
});
</script>
@endpush
@endsection
