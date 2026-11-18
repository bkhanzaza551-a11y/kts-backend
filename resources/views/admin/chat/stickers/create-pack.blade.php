@extends('layouts.app')
@section('title', 'Create Sticker Pack & Bulk Upload')

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-emoji-smile-fill fs-4 text-warning"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">Create Sticker Pack & Bulk Upload</h4>
                <p class="text-secondary small mb-0">Create a new community sticker pack and upload all sticker images in one go.</p>
            </div>
        </div>
        <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius:8px;">
            <i class="bi bi-arrow-left"></i> Back to Packs
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert" style="border-radius:10px;">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1">
            <strong>Please check the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.chat.stickers.store-pack') }}" enctype="multipart/form-data" id="createPackForm">
        @csrf

        {{-- 1. Pack Details Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-folder-plus fs-6"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-dark">1. Pack Details</h6>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Pack Name <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-emoji-sunglasses"></i></span>
                            <input type="text" name="name" class="form-control border-start-0 ps-1 @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" placeholder="e.g. Bullish Traders, Memes, Crypto Fun">
                        </div>
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Pack Thumbnail / Cover <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <input type="file" name="thumbnail" id="thumbnailInput" class="form-control @error('thumbnail') is-invalid @enderror" accept="image/*">
                        <div class="form-text text-muted small mt-1">PNG, JPG, WebP. If left blank, your first uploaded sticker will be used automatically.</div>
                        @error('thumbnail')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <div id="thumbPreview" class="mt-2"></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark small mb-1.5">
                            Description <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="500" placeholder="Brief description for mobile traders...">{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Bulk Sticker Upload Zone (The Core Feature!) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1.5 rounded-2 bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                        <i class="bi bi-images fs-6"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">2. Bulk Upload Stickers</h6>
                        <small class="text-muted">Select or drag & drop multiple sticker images to attach directly to this pack</small>
                    </div>
                </div>
                <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill fw-semibold" id="stagedCountBadge" style="font-size:0.75rem;">
                    0 Stickers Selected
                </span>
            </div>

            <div class="card-body p-4">
                {{-- Drag & Drop Upload Zone --}}
                <div class="bulk-upload-dropzone p-4 text-center border-dashed rounded-3 mb-3 cursor-pointer position-relative" id="dropZone" onclick="document.getElementById('bulkStickersInput').click()">
                    <input type="file" name="stickers[]" multiple accept="image/png,image/webp,image/gif,image/jpeg" id="bulkStickersInput" class="d-none">

                    <div class="p-3 bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                        <i class="bi bi-cloud-arrow-up-fill fs-2"></i>
                    </div>

                    <h6 class="fw-bold text-dark mb-1">Click to Browse or Drag & Drop Sticker Images Here</h6>
                    <p class="text-secondary small mb-3">
                        Upload up to <strong>100 sticker images at once</strong> (.png, .webp, .gif, .jpg). Transparent PNGs recommended (256x256px).
                    </p>

                    <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-sm" style="border-radius:8px;">
                        <i class="bi bi-folder2-open"></i> Choose Multiple Stickers
                    </button>
                </div>

                {{-- Staged Stickers Live Preview Grid --}}
                <div id="stagedPreviewContainer" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pt-2 border-top">
                        <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                            <i class="bi bi-grid text-primary"></i> Staged Stickers for Upload (<span id="previewCount">0</span>)
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2" style="font-size:0.75rem;" onclick="clearAllStickers()">
                            <i class="bi bi-trash3 me-1"></i>Clear All
                        </button>
                    </div>

                    <div class="row g-2.5" id="stagedGrid">
                        {{-- Populated dynamically via JS --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Actions Bar --}}
        <div class="card border-0 shadow-sm p-3 mb-5" style="border-radius:12px; background:linear-gradient(to right, #f8fafc, #ffffff); border:1px solid #e2e8f0 !important;">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2 text-secondary small">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span id="footerSummary">Ready to create pack. Add stickers above to upload in bulk.</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-light border px-3 py-2 fw-semibold" style="border-radius:9px;">
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2" id="submitBtn" style="border-radius:9px;">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span id="submitBtnText">Create Pack & Upload Stickers</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .border-dashed {
        border: 2px dashed #cbd5e1;
        transition: all 0.2s ease;
        background-color: #f8fafc;
    }
    .border-dashed:hover, .border-dashed.dragover {
        border-color: #3b82f6 !important;
        background-color: #eff6ff !important;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .staged-sticker-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        transition: all 0.15s ease;
        position: relative;
    }
    .staged-sticker-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }
    .staged-remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: rgba(239, 68, 68, 0.9);
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .staged-remove-btn:hover {
        background: #dc2626;
    }
</style>
@endpush

@push('scripts')
<script>
    const bulkInput = document.getElementById('bulkStickersInput');
    const dropZone = document.getElementById('dropZone');
    const stagedGrid = document.getElementById('stagedGrid');
    const stagedPreviewContainer = document.getElementById('stagedPreviewContainer');
    const stagedCountBadge = document.getElementById('stagedCountBadge');
    const previewCount = document.getElementById('previewCount');
    const submitBtnText = document.getElementById('submitBtnText');
    const footerSummary = document.getElementById('footerSummary');
    const thumbInput = document.getElementById('thumbnailInput');
    const thumbPreview = document.getElementById('thumbPreview');

    // DataTransfer to manage selected files list
    let dt = new DataTransfer();

    // Thumbnail Preview
    thumbInput?.addEventListener('change', function(e) {
        thumbPreview.innerHTML = '';
        if (e.target.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(e.target.files[0]);
            img.style.cssText = 'width:70px;height:70px;object-fit:cover;border-radius:10px;border:2px solid #e2e8f0;box-shadow:0 2px 6px rgba(0,0,0,0.06);';
            thumbPreview.appendChild(img);
        }
    });

    // Drag and drop handlers
    ['dragenter', 'dragover'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(name => {
        dropZone.addEventListener(name, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer && e.dataTransfer.files) {
            handleFiles(e.dataTransfer.files);
        }
    });

    bulkInput.addEventListener('change', function() {
        if (this.files) {
            handleFiles(this.files);
        }
    });

    function handleFiles(files) {
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                dt.items.add(file);
            }
        }
        bulkInput.files = dt.files;
        renderStagedFiles();
    }

    function removeFile(index) {
        const newDt = new DataTransfer();
        for (let i = 0; i < dt.files.length; i++) {
            if (i !== index) {
                newDt.items.add(dt.files[i]);
            }
        }
        dt = newDt;
        bulkInput.files = dt.files;
        renderStagedFiles();
    }

    function clearAllStickers() {
        dt = new DataTransfer();
        bulkInput.files = dt.files;
        renderStagedFiles();
    }

    function renderStagedFiles() {
        const count = dt.files.length;
        stagedCountBadge.textContent = `${count} Sticker${count === 1 ? '' : 's'} Selected`;
        previewCount.textContent = count;

        if (count > 0) {
            stagedPreviewContainer.style.display = 'block';
            submitBtnText.textContent = `Create Pack & Upload ${count} Sticker${count === 1 ? '' : 's'}`;
            footerSummary.innerHTML = `<strong>${count} sticker(s)</strong> staged for upload. Will be processed immediately upon saving.`;
        } else {
            stagedPreviewContainer.style.display = 'none';
            submitBtnText.textContent = 'Create Pack';
            footerSummary.textContent = 'Ready to create pack. Add stickers above to upload in bulk.';
        }

        stagedGrid.innerHTML = '';
        for (let i = 0; i < dt.files.length; i++) {
            const file = dt.files[i];
            const sizeKb = Math.round(file.size / 1024);
            const objectUrl = URL.createObjectURL(file);

            const col = document.createElement('div');
            col.className = 'col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6';
            col.innerHTML = `
                <div class="staged-sticker-card p-2 text-center h-100 position-relative">
                    <button type="button" class="staged-remove-btn" onclick="removeFile(${i})" title="Remove this sticker">
                        <i class="bi bi-x"></i>
                    </button>
                    <div class="p-2 mb-1 bg-light rounded-2 d-flex align-items-center justify-content-center" style="height:80px;">
                        <img src="${objectUrl}" alt="" style="max-height:70px; max-width:100%; object-fit:contain;">
                    </div>
                    <div class="fw-semibold text-dark text-truncate small" style="font-size:0.75rem;" title="${file.name}">
                        ${file.name}
                    </div>
                    <div class="text-muted" style="font-size:0.68rem;">
                        ${sizeKb} KB
                    </div>
                </div>
            `;
            stagedGrid.appendChild(col);
        }
    }
</script>
@endpush
