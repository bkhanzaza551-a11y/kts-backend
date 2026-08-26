@extends('layouts.app')
@section('title', 'Send Notification')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-bell me-2 text-primary"></i>Send Notification</h4>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
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
                            <option value="{{ $t->id }}" data-title="{{ $t->title }}" data-body="{{ $t->body }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="notifTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Body <span class="text-danger">*</span></label>
                        <textarea name="body" id="notifBody" class="form-control @error('body') is-invalid @enderror" rows="5" maxlength="2000" required>{{ old('body') }}</textarea>
                        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="info">Info</option>
                                <option value="success">Success</option>
                                <option value="warning">Warning</option>
                                <option value="danger">Danger</option>
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
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Preview</h6></div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <strong id="previewTitle">{{ old('title', 'Notification Title') }}</strong>
                        <p class="mb-0 mt-1" id="previewBody">{{ old('body', 'Notification body will appear here...') }}</p>
                    </div>
                </div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Send this notification now? This cannot be undone.')"><i class="bi bi-send me-1"></i>Send Now</button></div>
        </div>
    </div>
</form>
<script>
function applyTemplate(){var s=document.getElementById('templateSelect');var o=s.options[s.selectedIndex];if(o.value){document.getElementById('notifTitle').value=o.dataset.title||'';document.getElementById('notifBody').value=o.dataset.body||'';}}
function toggleTargetFields(){var t=document.getElementById('targetSelect').value;document.getElementById('roleField').style.display=t==='role'?'block':'none';document.getElementById('userField').style.display=t==='user'?'block':'none';}
document.getElementById('notifTitle').addEventListener('input',function(){document.getElementById('previewTitle').textContent=this.value||'Notification Title';});
document.getElementById('notifBody').addEventListener('input',function(){document.getElementById('previewBody').textContent=this.value||'Notification body will appear here...';});
</script>
@endsection
