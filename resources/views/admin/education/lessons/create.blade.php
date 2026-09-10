@extends('layouts.app')
@section('title', 'Create Lesson - ' . $course->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-plus-circle me-2 text-primary"></i>Create Lesson</h4>
        <a href="{{ route('admin.courses.show', $course) }}" class="text-secondary text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i>{{ $course->title }}
        </a>
    </div>
    <a href="{{ route('admin.courses.lessons.index', $course) }}" class="btn btn-outline-secondary btn-sm fw-semibold">
        <i class="bi bi-arrow-left me-1"></i>Back to Lessons
    </a>
</div>

<form method="POST" action="{{ route('admin.courses.lessons.store', $course) }}" enctype="multipart/form-data" id="lessonForm">
    @csrf
    {{-- Hidden field for auto-detected video duration in minutes --}}
    <input type="hidden" name="duration_minutes" id="durationMinutesInput" value="{{ old('duration_minutes', 0) }}">

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-text text-primary me-2"></i>Lesson Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Lesson Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Introduction to Forex Risk Management" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Short Overview / Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000" placeholder="Brief 1-2 line summary of what students will learn...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary">Lesson Content (Notes / Markdown / HTML)</label>
                        <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="8" maxlength="50000" placeholder="Detailed lesson study notes, trading formulas, key takeaways...">{{ old('content') }}</textarea>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Video Source Section with Upload & Auto-Duration --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <label class="form-label fw-bold text-dark mb-0">
                                <i class="bi bi-camera-video-fill text-danger me-1"></i>Lesson Video <span class="text-danger">*</span>
                            </label>
                            {{-- Auto duration indicator badge --}}
                            <div id="durationBadgeContainer" class="d-none">
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2" id="durationBadgeText" style="font-size: 0.8rem;">
                                    <i class="bi bi-clock-history me-1"></i>Auto Duration: <strong id="formattedDuration">00:00</strong>
                                </span>
                            </div>
                        </div>

                        @error('video_file')
                        <div class="alert alert-danger p-2 small mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                        </div>
                        @enderror

                        {{-- Nav Tabs for Video Upload vs URL --}}
                        <ul class="nav nav-pills nav-fill mb-3" id="videoSourceTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-2 fw-semibold" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">
                                    <i class="bi bi-cloud-arrow-up-fill me-1"></i>Upload Video File
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-semibold" id="url-tab" data-bs-toggle="tab" data-bs-target="#url-pane" type="button" role="tab">
                                    <i class="bi bi-link-45deg me-1"></i>Paste Video URL
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="videoSourceTabContent">
                            {{-- Tab 1: Video File Upload --}}
                            <div class="tab-pane fade show active" id="upload-pane" role="tabpanel">
                                <div class="border-2 border-dashed rounded-3 p-4 text-center bg-white" style="border: 2px dashed #cbd5e1; cursor: pointer;" onclick="document.getElementById('videoFileInput').click()">
                                    <i class="bi bi-film fs-1 text-primary mb-2 d-block"></i>
                                    <div class="fw-bold text-dark mb-1">Click to browse or drop video file here</div>
                                    <small class="text-secondary d-block mb-2">Supported formats: MP4, MOV, WebM, MKV (Max 500MB)</small>
                                    <input type="file" name="video_file" id="videoFileInput" class="d-none" accept="video/mp4,video/quicktime,video/webm,video/x-matroska,video/*" onchange="handleVideoFile(this)">
                                </div>
                                <div id="videoFilePreview" class="d-none mt-3 p-3 bg-white border rounded-3">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2 overflow-hidden">
                                            <i class="bi bi-file-earmark-play-fill text-danger fs-3 flex-shrink-0"></i>
                                            <div class="overflow-hidden">
                                                <div class="fw-bold text-dark small text-truncate" id="videoFileName"></div>
                                                <small class="text-secondary" id="videoFileSize"></small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearVideoFile()">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                    <video id="tempVideoPlayer" controls class="w-100 rounded-3 mt-3 d-none" style="max-height: 240px;"></video>
                                </div>
                            </div>

                            {{-- Tab 2: External Video URL --}}
                            <div class="tab-pane fade" id="url-pane" role="tabpanel">
                                <div class="bg-white p-3 rounded-3 border">
                                    <label class="form-label small text-secondary fw-semibold">Video Stream / Embed URL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-link-45deg"></i></span>
                                        <input type="url" name="video_url" id="videoUrlInput" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=... or direct MP4 link" oninput="handleVideoUrlInput(this.value)">
                                    </div>
                                    <small class="text-muted mt-1 d-block">Supported: YouTube, Vimeo, Dailymotion, or direct video CDN URLs.</small>
                                    @error('video_url')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Sort Order --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary">Sort Order / Lesson #</label>
                            <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $nextSort) }}" min="0">
                            <small class="text-muted">Controls the sequential order in course list.</small>
                            @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Publication Status & Submit --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sliders text-primary me-2"></i>Publish Settings</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" class="form-check-input" id="isPublished" value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="isPublished">Publish Immediately</label>
                    </div>
                    <small class="text-secondary d-block mb-3">When published, students will immediately be able to view and stream this lesson.</small>

                    <div class="d-grid pt-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold" style="border-radius: 10px;">
                            <i class="bi bi-check-lg me-1"></i>Create Lesson
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Hidden HTML5 Video Element for Extracting Video Metadata & Duration --}}
<video id="metadataExtractor" preload="metadata" style="display:none;"></video>

<script>
function handleVideoFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('videoFileName').textContent = file.name;
        document.getElementById('videoFileSize').textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        document.getElementById('videoFilePreview').classList.remove('d-none');

        // Extract duration automatically via HTML5 Video element
        const blobUrl = URL.createObjectURL(file);
        const extractor = document.getElementById('metadataExtractor');
        extractor.src = blobUrl;

        extractor.onloadedmetadata = function() {
            const totalSeconds = Math.round(extractor.duration);
            const minutes = Math.ceil(totalSeconds / 60);
            
            // Set hidden input value for backend
            document.getElementById('durationMinutesInput').value = minutes;

            // Format mm:ss
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            const formatted = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

            document.getElementById('formattedDuration').textContent = formatted + ' (' + minutes + ' min)';
            document.getElementById('durationBadgeContainer').classList.remove('d-none');

            // Set temporary video player preview
            const player = document.getElementById('tempVideoPlayer');
            player.src = blobUrl;
            player.classList.remove('d-none');
        };
    }
}

function clearVideoFile() {
    const input = document.getElementById('videoFileInput');
    if (input) input.value = '';
    document.getElementById('videoFilePreview').classList.add('d-none');
    document.getElementById('durationBadgeContainer').classList.add('d-none');
    document.getElementById('durationMinutesInput').value = 0;
    const player = document.getElementById('tempVideoPlayer');
    player.pause();
    player.src = '';
    player.classList.add('d-none');
}

function handleVideoUrlInput(url) {
    url = url.trim();
    if (url.match(/\.(mp4|webm|mov|m4v|ogg)(\?.*)?$/i)) {
        const extractor = document.getElementById('metadataExtractor');
        extractor.src = url;
        extractor.onloadedmetadata = function() {
            const totalSeconds = Math.round(extractor.duration);
            const minutes = Math.ceil(totalSeconds / 60);
            document.getElementById('durationMinutesInput').value = minutes;

            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;
            const formatted = (mins < 10 ? '0' : '') + mins + ':' + (secs < 10 ? '0' : '') + secs;

            document.getElementById('formattedDuration').textContent = formatted + ' (' + minutes + ' min)';
            document.getElementById('durationBadgeContainer').classList.remove('d-none');
        };
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('lessonForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('videoFileInput');
            const urlInput = document.getElementById('videoUrlInput');
            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
            const hasUrl = urlInput && urlInput.value.trim().length > 0;

            if (!hasFile && !hasUrl) {
                e.preventDefault();
                e.stopPropagation();
                Swal.fire({
                    icon: 'warning',
                    title: 'Lesson Video Required',
                    text: 'Please upload a video file (MP4/MOV/WebM) or paste a video URL before creating the lesson.',
                    confirmButtonText: 'Understood',
                    customClass: {
                        confirmButton: 'btn btn-primary px-4'
                    }
                });
                return false;
            }
        });
    }
});
</script>
@endsection

