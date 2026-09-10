@extends('layouts.app')
@section('title', 'Send Notification')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-bell me-2 text-primary"></i>Send Notification</h4>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
@if(auth()->user()->hasPermission('notifications_send'))
<form method="POST" action="{{ route('admin.notifications.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Template</label>
                        <select id="templateSelect" class="form-select" onchange="applyTemplate()">
                            <option value="">-- Select Template (optional) --</option>
                            @foreach($templates as $t)
                            <option value="{{ $t->id }}" data-title="{{ $t->title }}" data-body="{{ $t->body }}" data-type="{{ $t->type }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="notifTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Enter notification title..." required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Body <span class="text-danger">*</span></label>
                        <textarea name="body" id="notifBody" class="form-control @error('body') is-invalid @enderror" rows="5" maxlength="2000" placeholder="Enter notification message..." required>{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Type <span class="text-danger">*</span></label>
                            <select name="type" id="typeSelect" class="form-select" required onchange="updatePreviewType()">
                                <option value="info" {{ old('type') === 'info' ? 'selected' : '' }}>Info</option>
                                <option value="success" {{ old('type') === 'success' ? 'selected' : '' }}>Success</option>
                                <option value="warning" {{ old('type') === 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="danger" {{ old('type') === 'danger' ? 'selected' : '' }}>Danger</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Target <span class="text-danger">*</span></label>
                            <select name="target" id="targetSelect" class="form-select" required onchange="toggleTargetFields()">
                                <option value="all">All Users</option>
                                <option value="premium">Premium Only</option>
                                <option value="free">Free Users</option>
                                <option value="role">By Role</option>
                                <option value="user">Single User</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="roleField" style="display:none;">
                            <label class="form-label text-secondary">Role</label>
                            <select name="target_role_id" class="form-select">
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4" id="userField" style="display:none;">
                            <label class="form-label text-secondary">User ID</label>
                            <input type="number" name="target_user_id" class="form-control" placeholder="Enter user ID">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-phone me-2 text-primary"></i>Live In-App Preview</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0 shadow-sm border" id="previewAlert" style="border-radius: 12px; transition: all 0.2s ease;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-info-circle-fill fs-5 mt-1" id="previewIcon"></i>
                            <div>
                                <strong id="previewTitle" class="d-block fs-6">{{ old('title', 'Notification Title') }}</strong>
                                <p class="mb-0 mt-1 small" id="previewBody" style="white-space: pre-line;">{{ old('body', 'Notification body will appear here...') }}</p>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">This is how users will see it in the app notification feed.</small>
                </div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg fw-semibold" onsubmit="return confirm('Send this notification now? This cannot be undone.')"><i class="bi bi-send me-1"></i>Send Now</button></div>
        </div>
    </div>
</form>
@else
<div class="alert alert-warning d-flex align-items-center" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>
    <span>You don't have permission to send notifications. Contact an administrator.</span>
</div>
@endif

<script>
function updatePreviewType() {
    var type = document.getElementById('typeSelect').value || 'info';
    var alertEl = document.getElementById('previewAlert');
    var iconEl = document.getElementById('previewIcon');
    if (!alertEl) return;

    alertEl.className = 'alert mb-0 shadow-sm border alert-' + type;
    if (iconEl) {
        if (type === 'success') {
            iconEl.className = 'bi bi-check-circle-fill fs-5 mt-1 text-success';
        } else if (type === 'warning') {
            iconEl.className = 'bi bi-exclamation-triangle-fill fs-5 mt-1 text-warning';
        } else if (type === 'danger') {
            iconEl.className = 'bi bi-x-circle-fill fs-5 mt-1 text-danger';
        } else {
            iconEl.className = 'bi bi-info-circle-fill fs-5 mt-1 text-info';
        }
    }
}

function applyTemplate(){
    var s = document.getElementById('templateSelect');
    var o = s.options[s.selectedIndex];
    if (o.value) {
        var title = o.dataset.title || '';
        var body = o.dataset.body || '';
        var type = o.dataset.type || 'info';

        document.getElementById('notifTitle').value = title;
        document.getElementById('notifBody').value = body;
        
        var typeSelect = document.getElementById('typeSelect');
        if (typeSelect && type) {
            typeSelect.value = type;
        }

        document.getElementById('previewTitle').textContent = title || 'Notification Title';
        document.getElementById('previewBody').textContent = body || 'Notification body will appear here...';
        updatePreviewType();
    }
}

function toggleTargetFields(){
    var t = document.getElementById('targetSelect').value;
    document.getElementById('roleField').style.display = t === 'role' ? 'block' : 'none';
    document.getElementById('userField').style.display = t === 'user' ? 'block' : 'none';
}

document.getElementById('notifTitle')?.addEventListener('input', function(){
    document.getElementById('previewTitle').textContent = this.value || 'Notification Title';
});

document.getElementById('notifBody')?.addEventListener('input', function(){
    document.getElementById('previewBody').textContent = this.value || 'Notification body will appear here...';
});
</script>
@endsection
