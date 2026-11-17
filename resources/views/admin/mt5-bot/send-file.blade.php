@extends('layouts.app')
@section('title', 'Send MT5 Bot File via Email')

@section('content')

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-envelope-arrow-up me-2 text-warning"></i>Send MT5 Bot File</h4>
        <p class="text-secondary small mb-0">Select users from approved demo requests or any registered user, then send the bot file via email.</p>
    </div>
    <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Bot
    </a>
</div>

{{-- Alerts --}}
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

<div class="row g-4">

    {{-- LEFT: User Selection --}}
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.mt5-bot.send-file.post') }}" id="sendForm">
            @csrf

            {{-- File Status Banner --}}
            @if($bot && $bot->hasBotFile())
                <div class="alert alert-success border-0 d-flex align-items-center gap-3 mb-4" style="background:rgba(25,135,84,0.1);border-left:4px solid #198754 !important;border-radius:10px;">
                    <div style="width:42px;height:42px;background:#198754;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-file-earmark-binary text-white fs-5"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark">{{ $bot->bot_file_name }}</div>
                        <small class="text-secondary">{{ $bot->bot_file_size }} &nbsp;•&nbsp; {{ strtoupper($bot->bot_file_type) }} file &nbsp;•&nbsp; Uploaded {{ $bot->bot_file_uploaded_at?->diffForHumans() }}</small>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success px-3 py-2">Ready to Send</span>
                    </div>
                </div>
            @else
                <div class="alert alert-danger border-0 d-flex align-items-center gap-3 mb-4" style="border-left:4px solid #dc3545 !important;border-radius:10px;">
                    <i class="bi bi-exclamation-octagon-fill text-danger fs-3"></i>
                    <div>
                        <div class="fw-bold">No Bot File Uploaded!</div>
                        <small>Please <a href="{{ route('admin.mt5-bot.index') }}">upload a bot file</a> first before sending emails.</small>
                    </div>
                </div>
            @endif

            {{-- Tabs --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-semibold" id="demo-tab" data-bs-toggle="tab" data-bs-target="#demoPanel" type="button" role="tab">
                                <i class="bi bi-pc-display-horizontal me-1"></i>
                                Approved Demo Requests
                                <span class="badge bg-warning text-dark ms-1">{{ $demoRequests->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-semibold" id="all-tab" data-bs-toggle="tab" data-bs-target="#allUsersPanel" type="button" role="tab">
                                <i class="bi bi-people me-1"></i>
                                All Registered Users
                                <span class="badge bg-primary ms-1">{{ $allUsers->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="userTabsContent">

                    {{-- TAB 1: Demo Requests --}}
                    <div class="tab-pane fade show active" id="demoPanel" role="tabpanel">
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="selectAllDemo" class="form-check-input" style="width:18px;height:18px;cursor:pointer;">
                                <label for="selectAllDemo" class="mb-0 fw-semibold small text-secondary cursor-pointer">Select All Approved/Linked</label>
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
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>User</th>
                                            <th>Demo Email</th>
                                            <th>Account #</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
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
                                                    <div class="fw-semibold">{{ $req->user->name }}</div>
                                                    <small class="text-muted">{{ $req->user->email }}</small>
                                                </td>
                                                <td><small class="text-muted">{{ $req->demo_email ?? '—' }}</small></td>
                                                <td><code class="small">{{ $req->exness_account_number ?? '—' }}</code></td>
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

                    {{-- TAB 2: All Users --}}
                    <div class="tab-pane fade" id="allUsersPanel" role="tabpanel">
                        <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="selectAllUsers" class="form-check-input" style="width:18px;height:18px;cursor:pointer;">
                                <label for="selectAllUsers" class="mb-0 fw-semibold small text-secondary cursor-pointer">Select All</label>
                            </div>
                            {{-- Search --}}
                            <form method="GET" action="{{ route('admin.mt5-bot.send-file') }}#allUsersPanel" class="d-flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="🔍 Search name or email..." style="width:240px;">
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
                            <div class="table-responsive" style="max-height:420px;overflow-y:auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th style="width:40px;"></th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Demo Req?</th>
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
                                            <td class="fw-semibold">{{ $user->name }}</td>
                                            <td><small class="text-muted">{{ $user->email }}</small></td>
                                            <td>
                                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($user->status ?? 'active') }}
                                                </span>
                                            </td>
                                            <td><small class="text-muted">{{ $user->created_at->format('d M Y') }}</small></td>
                                            <td>
                                                @if(in_array($user->id, $demoUserIds))
                                                    <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25"><i class="bi bi-check-circle me-1"></i>Yes</span>
                                                @else
                                                    <span class="badge bg-secondary bg-opacity-25 text-secondary border">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                </div>{{-- end tab-content --}}
            </div>{{-- end card --}}

            {{-- Admin Note --}}
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <label class="form-label fw-semibold"><i class="bi bi-chat-left-text me-1 text-warning"></i>Admin Note <span class="text-muted fw-normal">(optional — shown in email)</span></label>
                    <textarea name="admin_note" class="form-control" rows="2" maxlength="500" placeholder="e.g. Please install and configure with your Exness account...">{{ old('admin_note') }}</textarea>
                    <small class="text-muted">This note will appear in the email body for the user to read.</small>
                </div>
            </div>

            {{-- Send Button --}}
            <div class="d-flex align-items-center justify-content-between mt-4 p-3 rounded-3 border" style="background:#fffbf0;">
                <div>
                    <div id="selectionCount" class="fw-bold text-dark">
                        <i class="bi bi-person-check me-1 text-warning"></i>
                        <span id="countNum">0</span> user(s) selected
                    </div>
                    <small class="text-muted" id="selectionEmails" style="max-width:450px;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"></small>
                </div>
                <button type="submit"
                    id="sendBtn"
                    class="btn btn-warning fw-bold px-4 py-2"
                    {{ ($bot && $bot->hasBotFile()) ? '' : 'disabled' }}
                    onclick="return confirmSend(event)">
                    <i class="bi bi-send me-2"></i>Send Bot File Email
                </button>
            </div>

        </form>
    </div>

    {{-- RIGHT: Info Panel --}}
    <div class="col-lg-4">

        {{-- Bot File Card --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold"><i class="bi bi-robot me-2 text-warning"></i>Bot File Info</div>
            <div class="card-body">
                @if($bot && $bot->hasBotFile())
                    <dl class="mb-0">
                        <dt class="text-secondary small">File Name</dt>
                        <dd class="fw-semibold text-break">{{ $bot->bot_file_name }}</dd>
                        <dt class="text-secondary small">File Size</dt>
                        <dd>{{ $bot->bot_file_size }}</dd>
                        <dt class="text-secondary small">File Type</dt>
                        <dd><span class="badge bg-warning text-dark">{{ strtoupper($bot->bot_file_type) }}</span></dd>
                        <dt class="text-secondary small">Uploaded</dt>
                        <dd class="mb-0">{{ $bot->bot_file_uploaded_at?->format('d M Y, h:i A') }}</dd>
                    </dl>
                @else
                    <p class="text-danger mb-2"><i class="bi bi-exclamation-triangle me-1"></i>No file uploaded yet.</p>
                    <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-sm btn-outline-warning">Upload Bot File →</a>
                @endif
            </div>
        </div>

        {{-- How it works --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header fw-bold"><i class="bi bi-info-circle me-2 text-info"></i>How This Works</div>
            <div class="card-body">
                <ol class="mb-0 ps-3" style="font-size:13px;line-height:1.9;">
                    <li>Select users from <strong>Demo Requests</strong> or <strong>All Users</strong> tab</li>
                    <li>Add an optional admin note</li>
                    <li>Click <strong>Send Bot File Email</strong></li>
                    <li>Each user gets a branded email with the <strong>.ex5/.ex4 file attached</strong></li>
                    <li>Email includes MT5 installation instructions</li>
                </ol>
            </div>
        </div>

        {{-- Stats --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header fw-bold"><i class="bi bi-bar-chart me-2 text-success"></i>Quick Stats</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-secondary small">Approved Requests</span>
                    <span class="fw-bold text-success">{{ $demoRequests->where('status', 'approved')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-secondary small">Linked Requests</span>
                    <span class="fw-bold text-info">{{ $demoRequests->where('status', 'linked')->count() }}</span>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-secondary small">Total Users</span>
                    <span class="fw-bold text-primary">{{ $allUsers->count() }}</span>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    // Track selected user IDs to prevent duplicates across tabs
    const selected = new Map(); // id => {email, name}

    function updateCount() {
        const count = selected.size;
        document.getElementById('countNum').textContent = count;
        document.getElementById('sendBtn').disabled = count === 0 {{ $bot && $bot->hasBotFile() ? '' : '|| true' }};

        const emails = [...selected.values()].map(u => u.email).join(', ');
        document.getElementById('selectionEmails').textContent = emails || '';
    }

    // Handle individual checkboxes
    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = this.value;
            if (this.checked) {
                selected.set(id, { email: this.dataset.email, name: this.dataset.name });
            } else {
                selected.delete(id);
            }
            // Sync duplicate checkboxes (same user might appear in both tabs)
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
            // Sync all tabs
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

    // Confirm before send
    window.confirmSend = function (e) {
        const count = selected.size;
        if (count === 0) {
            e.preventDefault();
            alert('Please select at least one user.');
            return false;
        }
        return confirm(`Send the MT5 bot file to ${count} user(s)? They will each receive an email with the file attached.`);
    };

    updateCount();
})();
</script>
@endpush
