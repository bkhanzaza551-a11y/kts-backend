@extends('layouts.app')
@section('title', 'Send MT5 Bot File via Email')

@section('content')
<div class="container-fluid px-0">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-envelope-arrow-up-fill fs-4 text-warning"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">Send MT5 Bot File via Email</h4>
                <p class="text-secondary small mb-0">Select recipients or broadcast to all registered users with the bot file attached.</p>
            </div>
        </div>
        <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius:8px;">
            <i class="bi bi-arrow-left"></i> Back to MT5 Bot
        </a>
    </div>

    {{-- 1. Active Bot File Banner --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius:12px; overflow:hidden;">
        <div class="card-body p-3.5">
            @if($bot && $bot->hasBotFile())
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                            <i class="bi bi-file-earmark-zip fs-4"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="mb-0 fw-bold text-dark fs-6">{{ $bot->bot_file_name ?? 'KTS10PipsBot.ex5' }}</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size:0.7rem;">
                                    <i class="bi bi-check2 me-0.5"></i>Ready to Send
                                </span>
                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill fw-bold" style="font-size:0.7rem;">
                                    {{ strtoupper($bot->bot_file_type ?? 'EX5') }}
                                </span>
                            </div>
                            <div class="text-secondary small mt-1 d-flex align-items-center gap-2 flex-wrap">
                                <span>Size: <strong>{{ $bot->bot_file_size ?? 'Ready' }}</strong></span>
                                <span>•</span>
                                <span>Uploaded {{ $bot->bot_file_uploaded_at?->diffForHumans() ?? 'Recently' }}</span>
                                <span>•</span>
                                <span class="text-success"><i class="bi bi-shield-check me-0.5"></i>Permanent Storage</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="toggleUploadBox()" style="border-radius:8px;">
                            <i class="bi bi-cloud-arrow-up"></i> Replace Bot File
                        </button>
                    </div>
                </div>
            @else
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-3 bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-danger">No Bot File Ready on Server</h6>
                            <small class="text-muted">Please upload your .ex5, .ex4, or .zip bot software file before sending.</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm px-3 py-1.5 fw-semibold" onclick="toggleUploadBox()" style="border-radius:8px;">
                        <i class="bi bi-upload me-1"></i> Upload Bot File
                    </button>
                </div>
            @endif

            {{-- Hidden Upload Box --}}
            <div id="uploadBox" class="{{ ($bot && $bot->hasBotFile()) ? 'd-none' : '' }} mt-3 pt-3 border-top">
                <form method="POST" action="{{ route('admin.mt5-bot.send-file.upload') }}" enctype="multipart/form-data" id="botFileUploadForm">
                    @csrf
                    <div class="row g-2 align-items-center">
                        <div class="col-md-9">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-secondary mb-0">Select MT5 Bot Software File (.ex5, .ex4, .zip):</label>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size:0.7rem;">
                                    <i class="bi bi-info-circle me-1"></i>Max File Size: 4.5 MB (Cloud Limit)
                                </span>
                            </div>
                            <input type="file" name="bot_file" id="botFileInput" class="form-control form-control-sm" accept=".ex5,.ex4,.zip,.mq5,.mq4,.rar" required>
                            <div id="botFileSizeFeedback" class="mt-1 small"></div>
                            <div class="form-text small text-muted" style="font-size:0.75rem;">
                                Supported: <code>.ex5, .ex4, .zip, .rar</code> &bull; Vercel serverless functions strictly require files to be under <strong>4.5 MB</strong>.
                            </div>
                        </div>
                        <div class="col-md-3 pt-md-2 align-self-start">
                            <button type="submit" id="botFileUploadBtn" class="btn btn-primary btn-sm w-100 fw-bold py-2 mt-md-3">
                                <i class="bi bi-upload me-1"></i> Upload & Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Main Send Form (Full Width) --}}
    <form method="POST" action="{{ route('admin.mt5-bot.send-file.post') }}" id="sendForm">
        @csrf

        {{-- 2. Recipient Mode Selector (Segmented Clean Cards) --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body p-3.5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="p-1 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                        <i class="bi bi-send-check fs-6"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-dark">Step 1: Choose Recipient Mode</h6>
                </div>
                <div class="row g-3">
                    {{-- Option 1: Selected Users --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 target-option cursor-pointer" id="optionSelectedBox" onclick="setTargetMode('selected')" style="border-color:#f59e0b !important; background:#fffbeb; transition:all 0.15s ease;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_target" id="targetSelected" value="selected" checked onchange="handleTargetChange()">
                                <label class="form-check-label fw-bold text-dark cursor-pointer fs-6" for="targetSelected">
                                    Specific Selected Users
                                </label>
                            </div>
                            <p class="text-secondary small mb-0 mt-1 ps-4">
                                Select individual approved demo traders or registered users from the list below.
                            </p>
                        </div>
                    </div>

                    {{-- Option 2: ALL USERS --}}
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 target-option cursor-pointer" id="optionAllBox" onclick="setTargetMode('all')" style="border-color:#e2e8f0; background:#ffffff; transition:all 0.15s ease;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="send_target" id="targetAll" value="all" onchange="handleTargetChange()">
                                <label class="form-check-label fw-bold text-success cursor-pointer fs-6" for="targetAll">
                                    🚀 Broadcast to ALL Registered Users ({{ $allUsers->count() }})
                                </label>
                            </div>
                            <p class="text-secondary small mb-0 mt-1 ps-4">
                                Dispatches the MT5 bot email to <strong>all {{ $allUsers->count() }} registered platform users</strong> in 1 click!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ALL USERS BANNER (Shown when Mode 2 is selected) --}}
        <div id="allUsersBanner" class="alert alert-info border-0 shadow-sm d-none mb-4 p-4" style="background:#f0f9ff; border-left:4px solid #0284c7 !important; border-radius:12px;">
            <div class="d-flex align-items-center gap-3">
                <div class="p-2.5 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                    <i class="bi bi-broadcast fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1">Broadcasting to ALL {{ $allUsers->count() }} Registered Platform Users</h6>
                    <p class="text-secondary mb-0 small">
                        Every single active registered user will receive the branded email with your bot file attached.
                    </p>
                </div>
            </div>
        </div>

        {{-- 3. Users Selection Table (Shown in Selected Mode) --}}
        <div class="card border-0 shadow-sm mb-4" id="userSelectionCard" style="border-radius:12px; overflow:hidden;">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-1 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:28px;height:28px;">
                        <i class="bi bi-people fs-6"></i>
                    </span>
                    <h6 class="mb-0 fw-bold text-dark">Step 2: Select Recipients</h6>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-pills gap-1" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold px-3 py-1.5 small" id="demo-tab" data-bs-toggle="pill" data-bs-target="#demoPanel" type="button" role="tab" style="border-radius:8px;">
                            <i class="bi bi-pc-display-horizontal me-1"></i>Approved Demo Requests ({{ $demoRequests->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold px-3 py-1.5 small" id="all-tab" data-bs-toggle="pill" data-bs-target="#allUsersPanel" type="button" role="tab" style="border-radius:8px;">
                            <i class="bi bi-people me-1"></i>All Registered Users ({{ $allUsers->count() }})
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="userTabsContent">
                {{-- TAB 1: Approved Demo Requests --}}
                <div class="tab-pane fade show active" id="demoPanel" role="tabpanel">
                    <div class="p-3 border-bottom bg-light d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <input type="checkbox" id="selectAllDemo" class="form-check-input" style="width:18px;height:18px;cursor:pointer;">
                            <label for="selectAllDemo" class="mb-0 fw-bold small text-dark cursor-pointer">Select All Approved/Linked</label>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="demoSearchFilter" class="form-control form-control-sm" placeholder="Filter demo users..." style="width:220px;" oninput="filterTableRows('demoTableBody', this.value)">
                        </div>
                    </div>

                    @if($demoRequests->isEmpty())
                        <div class="p-5 text-center text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                            <div class="fw-semibold text-dark">No approved demo requests found.</div>
                            <small class="text-muted"><a href="{{ route('admin.demo-accounts.index') }}">View demo requests tab →</a></small>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height:420px; overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top small text-secondary">
                                    <tr>
                                        <th style="width:44px;" class="ps-3"></th>
                                        <th>Trader</th>
                                        <th>Account #</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="demoTableBody">
                                    @foreach($demoRequests as $req)
                                        @if($req->user)
                                        <tr class="table-row-item">
                                            <td class="ps-3">
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
                                            <td><code class="fw-bold">{{ $req->exness_account_number ?? '—' }}</code></td>
                                            <td><span class="badge bg-light text-dark border">{{ ucfirst($req->account_type ?? 'Standard') }}</span></td>
                                            <td>
                                                @if($req->status === 'approved')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">Approved</span>
                                                @elseif($req->status === 'linked')
                                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5 rounded-pill">Linked</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($req->status) }}</span>
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
                            <input type="checkbox" id="selectAllUsers" class="form-check-input" style="width:18px;height:18px;cursor:pointer;">
                            <label for="selectAllUsers" class="mb-0 fw-bold small text-dark cursor-pointer">Select All Users In Table</label>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="text" id="allUsersSearchFilter" class="form-control form-control-sm" placeholder="Filter by name or email..." style="width:240px;" oninput="filterTableRows('allUsersTableBody', this.value)">
                        </div>
                    </div>

                    @if($allUsers->isEmpty())
                        <div class="p-5 text-center text-secondary">
                            <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                            <div class="fw-semibold text-dark">No registered users found.</div>
                        </div>
                    @else
                        <div class="table-responsive" style="max-height:420px; overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top small text-secondary">
                                    <tr>
                                        <th style="width:44px;" class="ps-3"></th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Demo Linked?</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody id="allUsersTableBody">
                                    @foreach($allUsers as $user)
                                    <tr class="table-row-item">
                                        <td class="ps-3">
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
                                            <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $user->status === 'active' ? 'success' : 'secondary' }} border px-2 py-0.5 rounded-pill">
                                                {{ ucfirst($user->status ?? 'active') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if(in_array($user->id, $demoUserIds))
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">
                                                    <i class="bi bi-check2 me-0.5"></i>Yes
                                                </span>
                                            @else
                                                <span class="text-muted small">No</span>
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

        {{-- 4. Admin Note --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;">
            <div class="card-body p-3.5">
                <label class="form-label fw-bold text-dark small mb-1.5 d-flex align-items-center gap-1.5">
                    <i class="bi bi-chat-left-text text-warning"></i>
                    <span>Step 3: Admin Instruction Note <span class="text-muted fw-normal">(Optional — shown in email body)</span></span>
                </label>
                <textarea name="admin_note" class="form-control" rows="2" maxlength="500" placeholder="e.g. Please install and configure the bot with your Exness MT5 account. Reach out on support chat if you have any questions.">{{ old('admin_note') }}</textarea>
                <div class="form-text text-muted small mt-1">This note will appear prominently inside the email body for the selected users.</div>
            </div>
        </div>

        {{-- 5. Sticky Bottom Action Bar --}}
        <div class="p-3.5 rounded-3 shadow-sm d-flex align-items-center justify-content-between flex-wrap gap-3 mb-5" style="background:#ffffff; border:1px solid #fde047 !important; border-radius:12px;">
            <div class="d-flex align-items-center gap-2">
                <div class="p-2 rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                    <i class="bi bi-person-check-fill fs-5"></i>
                </div>
                <div>
                    <div id="selectionSummary" class="fw-bold text-dark fs-6">
                        <span id="summaryText">0 user(s) selected</span>
                    </div>
                    <small class="text-muted" id="selectedEmailsPreview" style="max-width:550px; display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"></small>
                </div>
            </div>
            <button type="button"
                id="sendBtn"
                class="btn btn-warning fw-bold px-4 py-2.5 fs-6 shadow-sm d-inline-flex align-items-center gap-2"
                style="border-radius:9px;"
                {{ ($bot && $bot->hasBotFile()) ? '' : 'disabled' }}
                onclick="openConfirmModal()">
                <i class="bi bi-send-fill"></i> Send Bot File Email
            </button>
        </div>
    </form>
</div>

{{-- Confirmation & Live Progress Modal --}}
<div class="modal fade" id="sendConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                {{-- State 1: Review Before Sending --}}
                <div id="dispatchReviewState">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 64px; height: 64px; border-radius: 50%; background: #fffbeb; border: 2px solid #fde047;">
                        <i class="bi bi-send-check-fill text-warning fs-2"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1" id="modalTitle">Confirm Email Dispatch</h5>
                    <p class="text-secondary small mb-3" id="modalSubtitle">
                        Please review your dispatch details before sending.
                    </p>

                    <div class="p-3 bg-light rounded-3 text-start mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Target Mode:</span>
                            <span class="badge bg-primary px-2 py-1" id="modalTargetBadge">Selected Users</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Recipients:</span>
                            <span class="fw-bold text-dark" id="modalRecipientsCount">0 users</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Attached File:</span>
                            <span class="fw-bold text-success" id="modalFileName">
                                <i class="bi bi-paperclip me-1"></i>{{ $bot->bot_file_name ?? 'MT5 Bot File' }}
                            </span>
                        </div>
                        <div id="modalNotePreviewRow" style="display: none;">
                            <span class="text-muted small fw-semibold d-block mb-1">Admin Note:</span>
                            <div class="small text-secondary bg-white p-2 rounded border fst-italic" id="modalNoteText"></div>
                        </div>
                    </div>

                    <div id="modalAllWarning" class="alert alert-warning text-start small mb-3 border-0 py-2 px-3" style="display: none; border-radius: 10px; background: #FFFBEB; border: 1px solid #FCD34D !important;">
                        <div class="d-flex gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-dark">Broadcast to ALL Users:</strong>
                                <div class="text-secondary">Every single active user ({{ $allUsers->count() }} users) will receive this MT5 bot email.</div>
                            </div>
                        </div>
                    </div>

                    <div id="modalRecipientsWrapper" class="text-start mb-3" style="display: none;">
                        <span class="text-muted small fw-semibold d-block mb-1">Selected Recipients Preview:</span>
                        <div id="modalRecipientsList" class="d-flex flex-wrap gap-1 p-2 bg-white rounded border" style="max-height: 100px; overflow-y: auto;"></div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button type="button" class="btn btn-light px-4 py-2 fw-semibold text-secondary" data-bs-dismiss="modal" style="border-radius: 8px; border: 1px solid #E5E7EB;">
                            Cancel
                        </button>
                        <button type="button" id="confirmSendSubmitBtn" class="btn btn-warning px-4 py-2 fw-bold text-dark shadow-sm" style="border-radius: 8px;">
                            <i class="bi bi-send-fill me-1"></i>Yes, Send Bot File
                        </button>
                    </div>
                </div>

                {{-- State 2: Live Real-Time Batch Progress --}}
                <div id="dispatchProgressState" style="display: none;">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         id="progressIconBox"
                         style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; border: 2px solid #3b82f6;">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;" id="progressSpinner"></div>
                        <i class="bi bi-check-lg text-success fs-1 d-none" id="progressSuccessIcon"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1" id="progressTitle">Dispatching Bot Emails...</h5>
                    <p class="text-secondary small mb-3" id="progressSubtitle">
                        Delivering emails in safe background chunks to prevent serverless timeouts. Please keep this tab open.
                    </p>

                    <!-- Progress Bar & Stats -->
                    <div class="p-3 bg-light rounded-3 text-start mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small fw-bold text-dark" id="progressStatusLabel">Initializing pipeline...</span>
                            <span class="badge bg-primary px-2 py-1" id="progressPercentageBadge">0%</span>
                        </div>
                        <div class="progress mb-2" style="height: 16px; border-radius: 8px; background: #e2e8f0;">
                            <div id="dispatchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-warning text-dark fw-bold" role="progressbar" style="width: 0%; font-size: 11px;">0%</div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-2 pt-1 border-top">
                            <div><i class="bi bi-check-circle-fill text-success me-1"></i>Delivered: <strong class="text-dark" id="batchDeliveredCount">0</strong> / <span id="batchTotalCount">0</span></div>
                            <div><i class="bi bi-x-circle-fill text-danger me-1"></i>Failed: <strong class="text-danger" id="batchFailedCount">0</strong></div>
                        </div>
                    </div>

                    <!-- Live Delivery Feed -->
                    <div class="text-start mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-semibold">Live Delivery Stream:</span>
                            <span class="badge bg-secondary-subtle text-secondary small" id="batchCurrentBatchTag">Starting...</span>
                        </div>
                        <div id="batchLogFeed" class="p-2.5 bg-white rounded border small font-monospace" style="max-height: 130px; overflow-y: auto; font-size: 12px; line-height: 1.6;">
                            <div class="text-secondary fst-italic">Initiating secure batch pipeline...</div>
                        </div>
                    </div>

                    <!-- Done Button -->
                    <div id="progressDoneAction" style="display: none;" class="mt-3">
                        <button type="button" class="btn btn-success px-4 py-2 fw-bold text-white shadow-sm" onclick="window.location.reload()" style="border-radius: 8px;">
                            <i class="bi bi-check-circle-fill me-1.5"></i>Done & Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Validation Modal (0 users selected) --}}
<div class="modal fade" id="noSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg text-center p-3" style="border-radius: 16px;">
            <div class="modal-body">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                     style="width: 52px; height: 52px; border-radius: 50%; background: #FEE2E2; color: #DC2626;">
                    <i class="bi bi-exclamation-circle-fill fs-3"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No Users Selected</h6>
                <p class="text-secondary small mb-3">Please select at least one user from the list before sending.</p>
                <button type="button" class="btn btn-dark w-100 fw-bold py-1.5" data-bs-dismiss="modal" style="border-radius: 8px;">
                    Understood
                </button>
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
            summaryText.innerHTML = `<span class="text-success fw-bold">🚀 ALL ${totalUsersCount} Registered Users Selected</span>`;
            preview.textContent = 'Email with bot file will be broadcast to all active users.';
            sendBtn.innerHTML = `<i class="bi bi-send-fill me-1.5"></i>Send to ALL ${totalUsersCount} Users`;
            sendBtn.disabled = !hasBot;

            optAll.style.borderColor = '#0284c7';
            optAll.style.background = '#f0f9ff';
            optSelected.style.borderColor = '#e2e8f0';
            optSelected.style.background = '#ffffff';
        } else {
            userCard.classList.remove('d-none');
            allBanner.classList.add('d-none');
            sendBtn.innerHTML = `<i class="bi bi-send-fill me-1.5"></i>Send Bot File Email`;

            optSelected.style.borderColor = '#f59e0b';
            optSelected.style.background = '#fffbeb';
            optAll.style.borderColor = '#e2e8f0';
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

    // Client-side instant live table search filter
    window.filterTableRows = function(tbodyId, query) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        const term = (query || '').toLowerCase().trim();
        const rows = tbody.querySelectorAll('.table-row-item');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    };

    // Checkbox Listeners
    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.addEventListener('change', function () {
            const id = this.value;
            if (this.checked) {
                selected.set(id, { email: this.dataset.email, name: this.dataset.name });
            } else {
                selected.delete(id);
            }
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
                c.checked = this.checked;
            });
            updateCount();
        });
    });

    // Select All Demo
    document.getElementById('selectAllDemo').addEventListener('change', function () {
        document.querySelectorAll('.demo-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display === 'none') return; // ignore hidden filtered rows
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
            const row = cb.closest('tr');
            if (row && row.style.display === 'none') return;
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

    // Modal Confirmation
    window.openConfirmModal = function () {
        const isAll = document.getElementById('targetAll').checked;
        const note = document.querySelector('textarea[name="admin_note"]')?.value.trim() || '';

        const noteRow = document.getElementById('modalNotePreviewRow');
        const noteText = document.getElementById('modalNoteText');
        if (note) {
            noteRow.style.display = 'block';
            noteText.textContent = note;
        } else {
            noteRow.style.display = 'none';
        }

        if (isAll) {
            document.getElementById('modalTargetBadge').className = 'badge bg-success px-2 py-1';
            document.getElementById('modalTargetBadge').textContent = '🚀 Broadcast to ALL';
            document.getElementById('modalRecipientsCount').textContent = `${totalUsersCount} registered users`;
            document.getElementById('modalAllWarning').style.display = 'block';
            document.getElementById('modalRecipientsWrapper').style.display = 'none';
        } else {
            const count = selected.size;
            if (count === 0) {
                const noSelectModal = new bootstrap.Modal(document.getElementById('noSelectionModal'));
                noSelectModal.show();
                return;
            }

            document.getElementById('modalTargetBadge').className = 'badge bg-primary px-2 py-1';
            document.getElementById('modalTargetBadge').textContent = 'Selected Users';
            document.getElementById('modalRecipientsCount').textContent = `${count} user(s)`;
            document.getElementById('modalAllWarning').style.display = 'none';

            const listEl = document.getElementById('modalRecipientsList');
            listEl.innerHTML = '';
            selected.forEach(u => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-light text-dark border px-2 py-1';
                badge.innerHTML = `<i class="bi bi-person me-1 text-secondary"></i>${u.name || u.email}`;
                listEl.appendChild(badge);
            });
            document.getElementById('modalRecipientsWrapper').style.display = 'block';
        }

        const confirmModal = new bootstrap.Modal(document.getElementById('sendConfirmModal'));
        confirmModal.show();
    };

    const allUserIds = @json($allUsers->pluck('id'));
    const batchEndpoint = "{{ route('admin.mt5-bot.send-file.batch') }}";
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    function chunkArray(arr, size) {
        const res = [];
        for (let i = 0; i < arr.length; i += size) {
            res.push(arr.slice(i, i + size));
        }
        return res;
    }

    document.getElementById('confirmSendSubmitBtn').addEventListener('click', async function () {
        const isAll = document.getElementById('targetAll').checked;
        const note = document.querySelector('textarea[name="admin_note"]')?.value.trim() || '';

        let targetIds = [];
        if (isAll) {
            targetIds = allUserIds;
        } else {
            targetIds = Array.from(selected.keys());
        }

        if (targetIds.length === 0) {
            alert('Please select at least 1 user.');
            return;
        }

        // Switch modal to progress view
        document.getElementById('dispatchReviewState').style.display = 'none';
        document.getElementById('dispatchProgressState').style.display = 'block';

        // Chunk into safe batches of 8 users (each batch takes ~4-6 seconds, zero Vercel timeouts!)
        const BATCH_SIZE = 8;
        const chunks = chunkArray(targetIds, BATCH_SIZE);
        const totalUsers = targetIds.length;
        const totalBatches = chunks.length;

        document.getElementById('batchTotalCount').textContent = totalUsers;
        document.getElementById('batchDeliveredCount').textContent = '0';
        document.getElementById('batchFailedCount').textContent = '0';

        const progressBar = document.getElementById('dispatchProgressBar');
        const badgePercent = document.getElementById('progressPercentageBadge');
        const statusLabel = document.getElementById('progressStatusLabel');
        const logFeed = document.getElementById('batchLogFeed');
        const currentBatchTag = document.getElementById('batchCurrentBatchTag');

        logFeed.innerHTML = '';
        let totalSent = 0;
        let totalFailed = 0;

        window.onbeforeunload = function () {
            return "Emails are currently being dispatched. Are you sure you want to leave?";
        };

        for (let i = 0; i < totalBatches; i++) {
            const batchNum = i + 1;
            const currentChunk = chunks[i];
            
            statusLabel.textContent = `Dispatching batch ${batchNum} of ${totalBatches} (${currentChunk.length} users)...`;
            currentBatchTag.textContent = `Batch ${batchNum}/${totalBatches}`;

            try {
                const res = await fetch(batchEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        user_ids: currentChunk,
                        admin_note: note
                    })
                });

                if (!res.ok) {
                    throw new Error(`HTTP Error ${res.status}`);
                }

                const data = await res.json();
                if (data.success) {
                    totalSent += (data.sent_count || 0);
                    totalFailed += (data.failed_count || 0);

                    if (data.sent_users && data.sent_users.length) {
                        data.sent_users.forEach(u => {
                            const line = document.createElement('div');
                            line.className = 'text-success py-0.5';
                            line.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Delivered: <strong>${u.email}</strong>`;
                            logFeed.prepend(line);
                        });
                    }
                    if (data.failed_users && data.failed_users.length) {
                        data.failed_users.forEach(f => {
                            const line = document.createElement('div');
                            line.className = 'text-danger py-0.5';
                            line.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i>Failed: ${f.email} (${f.error})`;
                            logFeed.prepend(line);
                        });
                    }
                } else {
                    totalFailed += currentChunk.length;
                    const line = document.createElement('div');
                    line.className = 'text-danger py-0.5';
                    line.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>Batch ${batchNum} issue: ${data.message || 'Error'}`;
                    logFeed.prepend(line);
                }
            } catch (err) {
                totalFailed += currentChunk.length;
                const line = document.createElement('div');
                line.className = 'text-danger py-0.5';
                line.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>Network error on batch ${batchNum}: ${err.message}`;
                logFeed.prepend(line);
            }

            // Update progress
            const processedCount = totalSent + totalFailed;
            const pct = Math.min(100, Math.round((processedCount / totalUsers) * 100));

            progressBar.style.width = `${pct}%`;
            progressBar.textContent = `${pct}%`;
            badgePercent.textContent = `${pct}%`;
            document.getElementById('batchDeliveredCount').textContent = totalSent;
            document.getElementById('batchFailedCount').textContent = totalFailed;
        }

        window.onbeforeunload = null;

        // Completion state
        progressBar.classList.remove('progress-bar-animated', 'progress-bar-striped');
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-success');

        document.getElementById('progressSpinner').classList.add('d-none');
        document.getElementById('progressSuccessIcon').classList.remove('d-none');
        document.getElementById('progressIconBox').style.background = '#dcfce7';
        document.getElementById('progressIconBox').style.borderColor = '#22c55e';

        document.getElementById('progressTitle').textContent = '🎉 Email Dispatch Completed!';
        document.getElementById('progressSubtitle').innerHTML = `Successfully dispatched to <strong>${totalSent}</strong> user(s).${totalFailed > 0 ? ` (${totalFailed} failed)` : ''}`;
        statusLabel.textContent = `Completed (${totalSent}/${totalUsers} Delivered)`;
        currentBatchTag.className = 'badge bg-success';
        currentBatchTag.textContent = 'Finished';

        document.getElementById('progressDoneAction').style.display = 'block';
    });

    handleTargetChange();

    // Client-side File Upload Size Validation (Max 4.2 MB for Vercel Serverless payload safety)
    const botFileInput = document.getElementById('botFileInput');
    const botFileSizeFeedback = document.getElementById('botFileSizeFeedback');
    const botFileUploadForm = document.getElementById('botFileUploadForm');
    const MAX_FILE_BYTES = 4.2 * 1024 * 1024; // 4.2 MB

    if (botFileInput) {
        botFileInput.addEventListener('change', function () {
            if (!this.files || this.files.length === 0) {
                botFileSizeFeedback.innerHTML = '';
                return;
            }

            const file = this.files[0];
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);

            if (file.size > MAX_FILE_BYTES) {
                this.value = ''; // Reset input
                botFileSizeFeedback.innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mt-2 d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-x-octagon-fill fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>File size bohot badi hai (${sizeInMb} MB)!</strong><br>
                            Vercel cloud serverless par maximum allowed file size <strong>4.5 MB</strong> hai. Meharbani karke file ko compress (.zip / .rar) karke 4.5 MB se kam size me upload karein.
                        </div>
                    </div>
                `;

                if (window.Swal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'File Size Exceeds 4.5 MB!',
                        html: `
                            <div class="text-start">
                                <p class="mb-2">Aapki select ki hui file ka size <strong>${sizeInMb} MB</strong> hai jo limit se badi hai.</p>
                                <div class="alert alert-warning p-2 small mb-2">
                                    <strong>Vercel Serverless Rule:</strong> Vercel par maximum payload limit <strong>4.5 MB</strong> hoti hai.
                                </div>
                                <p class="small text-muted mb-0">💡 <strong>Solution:</strong> Bot file (.ex5, .zip, .rar) ko compress karke 4.5 MB se kam size me upload karein.</p>
                            </div>
                        `,
                        confirmButtonText: 'Theek Hai',
                        confirmButtonColor: '#0f172a',
                        customClass: { popup: 'custom-swal-modal', title: 'custom-swal-title' }
                    });
                }
            } else {
                botFileSizeFeedback.innerHTML = `
                    <div class="text-success small fw-semibold mt-1">
                        <i class="bi bi-check-circle-fill me-1"></i>Selected: <strong>${file.name}</strong> (${sizeInMb} MB - Valid Size)
                    </div>
                `;
            }
        });
    }

    if (botFileUploadForm) {
        botFileUploadForm.addEventListener('submit', function (e) {
            if (botFileInput && botFileInput.files && botFileInput.files.length > 0) {
                if (botFileInput.files[0].size > MAX_FILE_BYTES) {
                    e.preventDefault();
                    alert('File size is too large for Vercel serverless. Maximum allowed size is 4.5 MB.');
                    return false;
                }
            }
            const submitBtn = document.getElementById('botFileUploadBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status"></span>Uploading...`;
            }
        });
    }
})();
</script>
@endpush
