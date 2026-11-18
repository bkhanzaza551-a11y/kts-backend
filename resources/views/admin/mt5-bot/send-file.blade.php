@extends('layouts.app')
@section('title', 'Send MT5 Bot File via Email')

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-envelope-arrow-up me-2 text-warning"></i>Send MT5 Bot File</h4>
        <p class="text-secondary small mb-0">Upload your MT5 bot file, select recipients (or all registered users), and send via email.</p>
    </div>
    <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to MT5 Bot
    </a>
</div>

{{-- Session Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{!! session('warning') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- DIRECT FILE UPLOAD / STATUS CARD --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        @if($bot && $bot->hasBotFile())
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:52px;height:52px;background:#198754;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;flex-shrink:0;">
                        <i class="bi bi-file-earmark-binary"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold text-dark">{{ $bot->bot_file_name ?? 'KTS10PipsBot_v2.ex5' }}</h5>
                            <span class="badge bg-success">Ready to Send</span>
                            <span class="badge bg-warning text-dark">{{ strtoupper($bot->bot_file_type ?? 'EX5') }}</span>
                        </div>
                        <small class="text-secondary">
                            Size: <strong>{{ $bot->bot_file_size ?? 'Ready' }}</strong> &nbsp;•&nbsp; 
                            Uploaded: {{ $bot->bot_file_uploaded_at?->diffForHumans() ?? 'Recently' }} &nbsp;•&nbsp;
                            <span class="text-success"><i class="bi bi-shield-check me-1"></i>Saved in Database (Permanent)</span>
                        </small>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="toggleUploadBox()">
                        <i class="bi bi-cloud-arrow-up me-1"></i>Upload New / Replace File
                    </button>
                </div>
            </div>
        @else
            <div class="alert alert-danger d-flex align-items-center gap-3 mb-3 border-0">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-danger"></i>
                <div>
                    <div class="fw-bold">No Bot File Ready on Server!</div>
                    <small>Please select and upload your <code>.ex5</code> or <code>.ex4</code> trading bot file below before sending emails.</small>
                </div>
            </div>
        @endif

        {{-- Upload Form Box (Visible if no file, or toggled) --}}
        <div id="uploadBox" class="{{ ($bot && $bot->hasBotFile()) ? 'd-none' : '' }} mt-3 pt-3 border-top">
            <form method="POST" action="{{ route('admin.mt5-bot.send-file.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-2 align-items-center">
                    <div class="col-md-8">
                        <label class="form-label fw-bold small text-secondary">Select MT5 Bot Software File (.ex5, .ex4, .zip):</label>
                        <input type="file" name="bot_file" class="form-control" accept=".ex5,.ex4,.zip,.mq5,.mq4" required>
                    </div>
                    <div class="col-md-4 pt-md-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bi bi-upload me-1"></i>Upload Bot File Now
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle me-1"></i>File is stored securely in the database so it will <strong>never disappear</strong> even on serverless deployments (Vercel).
                </small>
            </form>
        </div>
    </div>
</div>

<div class="row g-4">

    {{-- LEFT: Send Options & User Selection --}}
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.mt5-bot.send-file.post') }}" id="sendForm">
            @csrf

            {{-- TARGET RECIPIENTS SELECTOR (ALL USERS VS SELECTED) --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-send-check me-2 text-primary"></i>1. Select Recipient Mode</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Option 1: Selected Users --}}
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 target-option cursor-pointer" id="optionSelectedBox" onclick="setTargetMode('selected')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_target" id="targetSelected" value="selected" checked onchange="handleTargetChange()">
                                    <label class="form-check-label fw-bold text-dark cursor-pointer" for="targetSelected">
                                        🔘 Specific Selected Users
                                    </label>
                                </div>
                                <p class="text-secondary small mb-0 mt-2 ps-4">
                                    Choose specific users from the Approved Demo Requests or All Users list below.
                                </p>
                            </div>
                        </div>

                        {{-- Option 2: ALL REGISTERED USERS --}}
                        <div class="col-md-6">
                            <div class="p-3 border rounded-3 h-100 target-option cursor-pointer" id="optionAllBox" onclick="setTargetMode('all')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_target" id="targetAll" value="all" onchange="handleTargetChange()">
                                    <label class="form-check-label fw-bold text-success cursor-pointer" for="targetAll">
                                        🚀 Send to ALL Registered Users
                                    </label>
                                </div>
                                <p class="text-secondary small mb-0 mt-2 ps-4">
                                    Dispatches the bot file email to <strong>all {{ $allUsers->count() }} active registered users</strong> in 1 click!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- USER SELECTION TABLES (Visible when "selected" mode is active) --}}
            <div class="card border-0 shadow-sm mb-4" id="userSelectionCard">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-people me-2 text-primary"></i>2. Choose Users from Tabs</h6>
                        {{-- Visual Tabs --}}
                        <ul class="nav nav-pills" id="userTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold px-3 py-1 small" id="demo-tab" data-bs-toggle="pill" data-bs-target="#demoPanel" type="button" role="tab">
                                    <i class="bi bi-pc-display-horizontal me-1"></i>Approved Demo Requests ({{ $demoRequests->count() }})
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold px-3 py-1 small" id="all-tab" data-bs-toggle="pill" data-bs-target="#allUsersPanel" type="button" role="tab">
                                    <i class="bi bi-people me-1"></i>All Registered Users ({{ $allUsers->count() }})
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="tab-content" id="userTabsContent">

                    {{-- TAB 1: Approved Demo Requests --}}
                    <div class="tab-pane fade show active" id="demoPanel" role="tabpanel">
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="selectAllDemo" class="form-check-input" style="width:19px;height:19px;cursor:pointer;">
                                <label for="selectAllDemo" class="mb-0 fw-bold small text-dark cursor-pointer">Select All Approved/Linked</label>
                            </div>
                            <small class="text-secondary">Showing requests with status: <span class="badge bg-success">approved</span> <span class="badge bg-info text-dark">linked</span></small>
                        </div>

                        @if($demoRequests->isEmpty())
                            <div class="p-5 text-center text-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No approved or linked demo requests found.
                                <div class="mt-2"><a href="{{ route('admin.demo-accounts.index') }}">View all demo requests →</a></div>
                            </div>
                        @else
                            <div class="table-responsive" style="max-height:440px;overflow-y:auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>User</th>
                                            <th>Account #</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($demoRequests as $req)
                                            @if($req->user)
                                            <tr>
                                                <td>
                                                    <input type="checkbox"
                                                        name="user_ids[]"
                                                        value="{{ $req->user_id }}"
                                                        class="form-check-input user-checkbox demo-checkbox"
                                                        style="width:18px;height:18px;cursor:pointer;"
                                                        data-email="{{ $req->user->email }}"
                                                        data-name="{{ $req->user->name }}">
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark">{{ $req->user->name }}</div>
                                                    <small class="text-muted">{{ $req->user->email }}</small>
                                                </td>
                                                <td><code>{{ $req->exness_account_number ?? '—' }}</code></td>
                                                <td><span class="badge bg-light text-dark border">{{ ucfirst($req->account_type ?? 'demo') }}</span></td>
                                                <td>
                                                    @if($req->status === 'approved')
                                                        <span class="badge bg-success">Approved</span>
                                                    @elseif($req->status === 'linked')
                                                        <span class="badge bg-info text-dark">Linked</span>
                                                    @endif
                                                </td>
                                                <td><small class="text-muted">{{ $req->created_at->format('d M Y') }}</small></td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    {{-- TAB 2: All Registered Users --}}
                    <div class="tab-pane fade" id="allUsersPanel" role="tabpanel">
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="selectAllUsers" class="form-check-input" style="width:19px;height:19px;cursor:pointer;">
                                <label for="selectAllUsers" class="mb-0 fw-bold small text-dark cursor-pointer">Select All Users In Table</label>
                            </div>
                            {{-- Search Filter --}}
                            <form method="GET" action="{{ route('admin.mt5-bot.send-file') }}#allUsersPanel" class="d-flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="🔍 Search user name or email..." style="width:240px;">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.mt5-bot.send-file') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                @endif
                            </form>
                        </div>

                        @if($allUsers->isEmpty())
                            <div class="p-5 text-center text-secondary">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No users found matching your search.
                            </div>
                        @else
                            <div class="table-responsive" style="max-height:440px;overflow-y:auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Has Demo?</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allUsers as $user)
                                        <tr>
                                            <td>
                                                <input type="checkbox"
                                                    name="user_ids[]"
                                                    value="{{ $user->id }}"
                                                    class="form-check-input user-checkbox all-checkbox"
                                                    style="width:18px;height:18px;cursor:pointer;"
                                                    data-email="{{ $user->email }}"
                                                    data-name="{{ $user->name }}">
                                            </td>
                                            <td class="fw-bold text-dark">{{ $user->name }}</td>
                                            <td><small class="text-muted">{{ $user->email }}</small></td>
                                            <td>
                                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($user->status ?? 'active') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(in_array($user->id, $demoUserIds))
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25"><i class="bi bi-check-circle me-1"></i>Yes</span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-25 text-secondary border">No</span>
                                                @endif
                                            </td>
                                            <td><small class="text-muted">{{ $user->created_at->format('d M Y') }}</small></td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ALL USERS BANNER (Shown when Mode 2 is selected) --}}
            <div id="allUsersBanner" class="alert alert-info border-0 shadow-sm d-none mb-4 p-4" style="background:#e0f2fe;border-left:5px solid #0284c7 !important;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-broadcast fs-1 text-primary"></i>
                    <div>
                        <h5 class="fw-bold text-dark mb-1">🚀 Sending to ALL {{ $allUsers->count() }} Registered Users!</h5>
                        <p class="text-secondary mb-0 small">
                            You have selected <strong>All Registered Users</strong> mode. Every active registered user in your database will receive the MT5 bot file attached to their email.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Optional Admin Note --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label class="form-label fw-bold text-dark"><i class="bi bi-chat-left-text me-1 text-warning"></i>3. Admin Note <span class="text-muted fw-normal">(Optional — shown in email)</span></label>
                    <textarea name="admin_note" class="form-control" rows="2" maxlength="500" placeholder="e.g. Please install and configure the bot with your Exness MT5 account.">{{ old('admin_note') }}</textarea>
                    <small class="text-muted">This note will appear prominently inside the email body for users.</small>
                </div>
            </div>

            {{-- SEND BUTTON & SUMMARY BAR --}}
            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-3" style="background:#fffcf0;border-color:#fde047 !important;">
                <div>
                    <div id="selectionSummary" class="fw-bold text-dark fs-6">
                        <i class="bi bi-person-check-fill me-1 text-warning"></i>
                        <span id="summaryText">0 user(s) selected</span>
                    </div>
                    <small class="text-muted" id="selectedEmailsPreview" style="max-width:450px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></small>
                </div>
                <button type="submit"
                    id="sendBtn"
                    class="btn btn-warning fw-bold px-4 py-2 fs-6 shadow-sm"
                    {{ ($bot && $bot->hasBotFile()) ? '' : 'disabled' }}
                    onclick="return confirmSend(event)">
                    <i class="bi bi-send-fill me-2"></i>Send Bot File Email
                </button>
            </div>

        </form>
    </div>

    {{-- RIGHT COLUMN: Info & Upload --}}
    <div class="col-lg-4">

        {{-- Bot File Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold text-dark">
                <i class="bi bi-robot me-2 text-warning"></i>Current Bot File Info
            </div>
            <div class="card-body">
                @if($bot && $bot->hasBotFile())
                    <dl class="mb-0">
                        <dt class="text-secondary small">File Name</dt>
                        <dd class="fw-bold text-break text-dark">{{ $bot->bot_file_name }}</dd>
                        <dt class="text-secondary small">File Size</dt>
                        <dd>{{ $bot->bot_file_size }}</dd>
                        <dt class="text-secondary small">File Type</dt>
                        <dd><span class="badge bg-warning text-dark">{{ strtoupper($bot->bot_file_type ?? 'EX5') }}</span></dd>
                        <dt class="text-secondary small">Storage Status</dt>
                        <dd><span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Permanent DB Storage</span></dd>
                        <dt class="text-secondary small">Uploaded At</dt>
                        <dd class="mb-0">{{ $bot->bot_file_uploaded_at?->format('d M Y, h:i A') ?? 'Recently' }}</dd>
                    </dl>
                @else
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>No file uploaded yet.
                    </div>
                    <button type="button" class="btn btn-sm btn-primary w-100 fw-bold" onclick="toggleUploadBox()">
                        <i class="bi bi-upload me-1"></i>Upload Bot File (.ex5)
                    </button>
                @endif
            </div>
        </div>

        {{-- How It Works --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 fw-bold text-dark">
                <i class="bi bi-info-circle me-2 text-info"></i>How It Works
            </div>
            <div class="card-body">
                <ol class="mb-0 ps-3 text-secondary" style="font-size:13px;line-height:1.9;">
                    <li>Upload your <strong>.ex5</strong> or <strong>.ex4</strong> bot file.</li>
                    <li>Choose either <strong>Specific Selected Users</strong> or <strong>Send to ALL Registered Users</strong>.</li>
                    <li>Add an optional admin note for instructions.</li>
                    <li>Click <strong>Send Bot File Email</strong>.</li>
                    <li>Each user receives a clean, branded email with the file attached!</li>
                </ol>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const selected = new Map();
    const totalUsersCount = {{ $allUsers->count() }};
    const hasBot = {{ ($bot && $bot->hasBotFile()) ? 'true' : 'false' }};

    window.toggleUploadBox = function () {
        const box = document.getElementById('uploadBox');
        if (box) {
            box.classList.toggle('d-none');
        }
    };

    window.setTargetMode = function (mode) {
        if (mode === 'all') {
            document.getElementById('targetAll').checked = true;
        } else {
            document.getElementById('targetSelected').checked = true;
        }
        handleTargetChange();
    };

    window.handleTargetChange = function () {
        const isAll = document.getElementById('targetAll').checked;
        const userCard = document.getElementById('userSelectionCard');
        const allBanner = document.getElementById('allUsersBanner');
        const summaryText = document.getElementById('summaryText');
        const sendBtn = document.getElementById('sendBtn');
        const preview = document.getElementById('selectedEmailsPreview');

        const optSelected = document.getElementById('optionSelectedBox');
        const optAll = document.getElementById('optionAllBox');

        if (isAll) {
            userCard.classList.add('d-none');
            allBanner.classList.remove('d-none');
            summaryText.innerHTML = `<span class="text-success">🚀 ALL ${totalUsersCount} Registered Users Selected!</span>`;
            preview.textContent = 'Email will be sent to all active users in database.';
            sendBtn.innerHTML = `<i class="bi bi-send-fill me-2"></i>Send to ALL ${totalUsersCount} Users`;
            sendBtn.disabled = !hasBot;

            optAll.style.borderColor = '#0284c7';
            optAll.style.background = '#f0f9ff';
            optSelected.style.borderColor = '#dee2e6';
            optSelected.style.background = '#ffffff';
        } else {
            userCard.classList.remove('d-none');
            allBanner.classList.add('d-none');
            sendBtn.innerHTML = `<i class="bi bi-send-fill me-2"></i>Send Bot File Email`;

            optSelected.style.borderColor = '#f59e0b';
            optSelected.style.background = '#fffbeb';
            optAll.style.borderColor = '#dee2e6';
            optAll.style.background = '#ffffff';

            updateCount();
        }
    };

    function updateCount() {
        const isAll = document.getElementById('targetAll').checked;
        if (isAll) return;

        const count = selected.size;
        document.getElementById('summaryText').textContent = `${count} user(s) selected`;
        document.getElementById('sendBtn').disabled = count === 0 || !hasBot;

        const emails = [...selected.values()].map(u => u.email).join(', ');
        document.getElementById('selectedEmailsPreview').textContent = emails || '';
    }

    // Checkbox Listeners
    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = this.value;
            if (this.checked) {
                selected.set(id, { email: this.dataset.email, name: this.dataset.name });
            } else {
                selected.delete(id);
            }
            // Sync duplicates
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
                c.checked = this.checked;
            });
            updateCount();
        });
    });

    // Select All Demo
    document.getElementById('selectAllDemo').addEventListener('change', function () {
        document.querySelectorAll('.demo-checkbox').forEach(cb => {
            cb.checked = this.checked;
            const id = cb.value;
            if (this.checked) {
                selected.set(id, { email: cb.dataset.email, name: cb.dataset.name });
            } else {
                selected.delete(id);
            }
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => c.checked = this.checked);
        });
        updateCount();
    });

    // Select All Users
    document.getElementById('selectAllUsers').addEventListener('change', function () {
        document.querySelectorAll('.all-checkbox').forEach(cb => {
            cb.checked = this.checked;
            const id = cb.value;
            if (this.checked) {
                selected.set(id, { email: cb.dataset.email, name: cb.dataset.name });
            } else {
                selected.delete(id);
            }
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => c.checked = this.checked);
        });
        updateCount();
    });

    // Confirmation
    window.confirmSend = function (e) {
        const isAll = document.getElementById('targetAll').checked;
        if (isAll) {
            return confirm(`⚠️ Are you sure you want to broadcast the MT5 Bot file to ALL ${totalUsersCount} registered users?`);
        }
        const count = selected.size;
        if (count === 0) {
            e.preventDefault();
            alert('Please select at least one user from the list below.');
            return false;
        }
        return confirm(`Send the MT5 bot file to ${count} user(s)?`);
    };

    handleTargetChange();
})();
</script>
@endpush
