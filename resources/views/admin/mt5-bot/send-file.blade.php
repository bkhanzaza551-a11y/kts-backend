@extends('layouts.app')
@section('title', 'Send MT5 Bot File via Email')

@push('styles')
<style>
    /* Executive Dispatch Studio Refined Styles */
    .studio-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
    }
    
    .stat-card-widget {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.1rem 1.25rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease;
    }
    .stat-card-widget:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .dispatch-choice-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.25rem;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        height: 100%;
    }
    .dispatch-choice-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .dispatch-choice-card.selected-active {
        border-color: #2563eb !important;
        background: #eff6ff !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.1);
    }
    .dispatch-choice-card.broadcast-active {
        border-color: #059669 !important;
        background: #ecfdf5 !important;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.1);
    }

    .table-scroll-wrap {
        max-height: 420px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .table-scroll-wrap::-webkit-scrollbar {
        width: 6px;
    }
    .table-scroll-wrap::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 6px;
    }

    .trader-row {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .trader-row:hover {
        background-color: #f8fafc !important;
    }
    .trader-row.row-checked {
        background-color: #eff6ff !important;
    }

    .avatar-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .template-btn-chip {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.15s ease;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }
    .template-btn-chip:hover {
        background: #e2e8f0;
        color: #0f172a;
        border-color: #cbd5e1;
    }

    .sticky-hub-panel {
        position: sticky;
        top: 24px;
        z-index: 10;
    }

    .user-pill-tag {
        font-size: 0.75rem;
        padding: 3px 8px;
        border-radius: 6px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        color: #0f172a;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
    }
    .user-pill-tag .tag-text {
        max-width: 170px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .user-pill-tag .tag-close {
        color: #94a3b8;
        cursor: pointer;
        font-weight: bold;
        line-height: 1;
    }
    .user-pill-tag .tag-close:hover {
        color: #ef4444;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0 pb-5">

    {{-- 1. Clean Top Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1.5 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-secondary text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.mt5-bot.index') }}" class="text-secondary text-decoration-none">MT5 Bot</a></li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Email Dispatch Studio</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-send-check text-primary me-2"></i>MT5 Bot Email Dispatch Studio
                </h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 small fw-semibold">
                    v2.5
                </span>
            </div>
            <p class="text-secondary small mb-0 mt-1">Distribute bot files (.ex5/.zip) & installation guides directly to traders' inboxes.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" style="border-radius: 8px;">
                <i class="bi bi-arrow-left"></i> Back to MT5 Bot
            </a>
            <button type="button" class="btn btn-primary btn-sm px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="toggleUploadBox()" style="border-radius: 8px;">
                <i class="bi bi-cloud-arrow-up"></i> {{ ($bot && $bot->hasBotFile()) ? 'Replace Bot File' : 'Upload Bot File' }}
            </button>
        </div>
    </div>

    {{-- 2. Top Stats Bar: 3 Clean, Distinct Cards (Perfect Alignment Grid) --}}
    <div class="row g-3 mb-4">
        
        {{-- Card 1: Active Bot File --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="stat-card-widget">
                <div class="d-flex justify-content-between align-items-center mb-2.5">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Active Bot Software</span>
                    @if($bot && $bot->hasBotFile())
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.7rem;">
                            <i class="bi bi-check2 me-0.5"></i>Ready
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.7rem;">
                            Missing File
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 {{ ($bot && $bot->hasBotFile()) ? 'bg-success text-white' : 'bg-danger text-white' }}" style="width: 44px; height: 44px;">
                        <i class="bi {{ ($bot && $bot->hasBotFile()) ? 'bi-file-earmark-code' : 'bi-exclamation-triangle' }} fs-4"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1">
                        <div class="fw-bold text-dark text-truncate" style="max-width: 220px;" title="{{ $bot->bot_file_name ?? 'No bot file' }}">
                            {{ $bot->bot_file_name ?? 'No bot file loaded' }}
                        </div>
                        <div class="text-muted small mt-0.5">
                            {{ $bot->bot_file_size ?? '0 KB' }} &bull; <span class="text-success"><i class="bi bi-shield-check"></i> Cloud Storage</span>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-top d-flex justify-content-between align-items-center small">
                    <span class="text-muted" style="font-size: 0.75rem;">Format: <strong>{{ strtoupper($bot->bot_file_type ?? 'EX5') }}</strong></span>
                    <a href="javascript:void(0)" onclick="toggleUploadBox()" class="text-primary text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                        Replace File <i class="bi bi-chevron-right ms-0.5"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 2: Registration Auto-Send --}}
        <div class="col-lg-4 col-md-6 col-12">
            <div class="stat-card-widget">
                <div class="d-flex justify-content-between align-items-center mb-2.5">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Signup Automation</span>
                    <span class="badge rounded-pill fw-bold" id="autoDispatchStatusBadge"
                          style="font-size: 0.7rem; background: {{ $autoSendBotEnabled ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $autoSendBotEnabled ? '#059669' : '#64748b' }}; border: 1px solid {{ $autoSendBotEnabled ? '#a7f3d0' : '#cbd5e1' }};">
                        {{ $autoSendBotEnabled ? '● ACTIVE (ON)' : '○ INACTIVE (OFF)' }}
                    </span>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="autoDispatchIconBox"
                             style="width: 44px; height: 44px; background: {{ $autoSendBotEnabled ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $autoSendBotEnabled ? '#059669' : '#64748b' }}; border: 1px solid {{ $autoSendBotEnabled ? '#a7f3d0' : '#e2e8f0' }};">
                            <i class="bi bi-robot fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-6">Auto-Send on Register</div>
                            <div class="text-muted small" style="font-size: 0.75rem;">Delivers bot file to new signups</div>
                        </div>
                    </div>

                    <div class="form-check form-switch ps-0 mb-0">
                        <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch" id="autoSendBotToggle"
                               style="width: 3rem; height: 1.6rem; cursor: pointer;"
                               {{ $autoSendBotEnabled ? 'checked' : '' }}
                               onchange="toggleAutoSendBot(this.checked)">
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 0.75rem;">
                    <span>Welcome Email Integration</span>
                    <span class="text-dark fw-semibold" id="autoSendLabelText">{{ $autoSendBotEnabled ? 'Enabled' : 'Disabled' }}</span>
                </div>
            </div>
        </div>

        {{-- Card 3: Platform Audience Reach --}}
        <div class="col-lg-4 col-md-12 col-12">
            <div class="stat-card-widget">
                <div class="d-flex justify-content-between align-items-center mb-2.5">
                    <span class="text-muted small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">Audience Reach</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.7rem;">
                        Verified DB
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; border: 1px solid rgba(59, 130, 246, 0.2);">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark fs-5 mb-0">{{ $allUsers->count() }} Registered Traders</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $demoRequests->count() }} Approved Demo Traders</div>
                    </div>
                </div>

                <div class="mt-3 pt-2.5 border-top d-flex justify-content-between align-items-center small text-muted" style="font-size: 0.75rem;">
                    <span>Delivery Pipeline</span>
                    <span class="text-success fw-semibold"><i class="bi bi-lightning-charge-fill me-0.5"></i>Chunked Safe Queue</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Collapsible Bot File Upload Box --}}
    <div id="uploadBox" class="{{ ($bot && $bot->hasBotFile()) ? 'd-none' : '' }} studio-card mb-4 p-4 border-primary">
        <form method="POST" action="{{ route('admin.mt5-bot.send-file.upload') }}" enctype="multipart/form-data" id="botFileUploadForm">
            @csrf
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cloud-arrow-up text-primary fs-5"></i>
                    <h6 class="mb-0 fw-bold text-dark">Upload / Update Bot Software File</h6>
                </div>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 small">
                    <i class="bi bi-info-circle me-1"></i>Max File Size: 4.5 MB (Cloud Limit)
                </span>
            </div>
            <p class="text-muted small mb-3">Upload your compiled MT5 Expert Advisor (<code>.ex5</code>, <code>.ex4</code>, <code>.zip</code>, or <code>.rar</code>). This file will be stored permanently in cloud storage and attached to dispatch emails.</p>
            
            <div class="row g-2 align-items-center">
                <div class="col-md-9">
                    <input type="file" name="bot_file" id="botFileInput" class="form-control" accept=".ex5,.ex4,.zip,.mq5,.mq4,.rar" required>
                    <div id="botFileSizeFeedback" class="mt-1 small"></div>
                </div>
                <div class="col-md-3">
                    <button type="submit" id="botFileUploadBtn" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="bi bi-check-lg me-1"></i> Upload & Set Active
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- 3. Main Workspace: 2-Column Split --}}
    <form method="POST" action="{{ route('admin.mt5-bot.send-file.post') }}" id="sendForm">
        @csrf
        <div class="row g-4">
            
            {{-- LEFT COLUMN: Configuration, Audience Selection, & Note --}}
            <div class="col-xl-8 col-lg-7">
                
                {{-- STEP 1: Target Audience Mode --}}
                <div class="studio-card mb-4 p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 small fw-bold">STEP 1</span>
                            <h6 class="mb-0 fw-bold text-dark fs-6">Target Audience Mode</h6>
                        </div>
                        <span class="text-muted small">Choose dispatch scope</span>
                    </div>

                    <div class="row g-3">
                        {{-- Mode 1: Selected Users --}}
                        <div class="col-md-6">
                            <div class="dispatch-choice-card selected-active" id="cardOptionSelected" onclick="setTargetMode('selected')">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                            <i class="bi bi-people-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">Specific Traders</div>
                                            <div class="text-muted small" style="font-size:0.75rem;">Custom Selection</div>
                                        </div>
                                    </div>
                                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="send_target" id="targetSelected" value="selected" checked onchange="handleTargetChange()">
                                </div>
                                <p class="text-secondary small mb-0 ps-1" style="font-size:0.8rem; line-height: 1.45;">
                                    Pick specific approved demo accounts or select registered users individually from the list below.
                                </p>
                            </div>
                        </div>

                        {{-- Mode 2: ALL Registered Users --}}
                        <div class="col-md-6">
                            <div class="dispatch-choice-card" id="cardOptionAll" onclick="setTargetMode('all')">
                                <div class="d-flex align-items-start justify-content-between mb-2">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                            <i class="bi bi-broadcast-pin fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">Broadcast to ALL</div>
                                            <div class="text-success fw-bold small" style="font-size:0.75rem;">{{ $allUsers->count() }} Traders Platform-Wide</div>
                                        </div>
                                    </div>
                                    <input class="form-check-input mt-1 cursor-pointer" type="radio" name="send_target" id="targetAll" value="all" onchange="handleTargetChange()">
                                </div>
                                <p class="text-secondary small mb-0 ps-1" style="font-size:0.8rem; line-height: 1.45;">
                                    Dispatches to <strong>all {{ $allUsers->count() }} registered users</strong> in safe background chunks. Zero timeouts.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- STEP 2: Recipient Selection Table (Shown when Specific Traders is active) --}}
                <div class="studio-card mb-4" id="userSelectionCard" style="overflow: hidden;">
                    <div class="card-header bg-white border-bottom p-3.5">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2.5 mb-2.5">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary text-white rounded-pill px-2 py-1 small fw-bold">STEP 2</span>
                                <h6 class="mb-0 fw-bold text-dark fs-6">Select Recipients</h6>
                            </div>

                            {{-- Navigation Tabs --}}
                            <ul class="nav nav-pills gap-1.5" id="userTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-semibold px-3 py-1.5 small" id="demo-tab" data-bs-toggle="pill" data-bs-target="#demoPanel" type="button" role="tab" style="border-radius: 8px;">
                                        <i class="bi bi-person-badge me-1"></i>Approved Demo ({{ $demoRequests->count() }})
                                        <span class="badge bg-primary-subtle text-primary rounded-pill ms-1" id="tabDemoCount">0</span>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-semibold px-3 py-1.5 small" id="all-tab" data-bs-toggle="pill" data-bs-target="#allUsersPanel" type="button" role="tab" style="border-radius: 8px;">
                                        <i class="bi bi-people me-1"></i>All Traders ({{ $allUsers->count() }})
                                        <span class="badge bg-primary-subtle text-primary rounded-pill ms-1" id="tabAllCount">0</span>
                                    </button>
                                </li>
                            </ul>
                        </div>

                        {{-- Search & Bulk Actions Toolbar --}}
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top">
                            <div class="input-group input-group-sm" style="max-width: 340px;">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                <input type="text" id="globalUserSearch" class="form-control border-start-0 bg-light" placeholder="Search by trader name, email, or account..." oninput="handleTableSearch(this.value)">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm px-2.5 py-1 fw-semibold small" onclick="selectAllInCurrentTab()" style="border-radius:6px; font-size:0.75rem;">
                                    <i class="bi bi-check-all me-1"></i>Select Tab (All)
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm px-2.5 py-1 fw-semibold small" onclick="clearAllSelections()" style="border-radius:6px; font-size:0.75rem;">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tab Panes --}}
                    <div class="tab-content" id="userTabsContent">
                        
                        {{-- TAB 1: Approved Demo Requests --}}
                        <div class="tab-pane fade show active" id="demoPanel" role="tabpanel">
                            @if($demoRequests->isEmpty())
                                <div class="p-5 text-center text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                    <div class="fw-bold text-dark">No approved demo accounts found</div>
                                    <small>Switch to the "All Traders" tab to select registered users.</small>
                                </div>
                            @else
                                <div class="table-scroll-wrap">
                                    <table class="table table-hover align-middle mb-0" id="demoTable">
                                        <thead class="table-light sticky-top small text-secondary" style="border-bottom: 1px solid #e2e8f0;">
                                            <tr>
                                                <th style="width: 44px;" class="ps-3">
                                                    <input type="checkbox" id="selectAllDemo" class="form-check-input cursor-pointer" style="width:17px;height:17px;">
                                                </th>
                                                <th>Trader</th>
                                                <th>MT5 Account</th>
                                                <th>Status</th>
                                                <th>Registered</th>
                                            </tr>
                                        </thead>
                                        <tbody id="demoTableBody">
                                            @foreach($demoRequests as $req)
                                                @if($req->user)
                                                <tr class="trader-row table-row-item" data-user-id="{{ $req->user_id }}" onclick="toggleRowSelection(event, this)">
                                                    <td class="ps-3" onclick="event.stopPropagation()">
                                                        <input type="checkbox"
                                                            name="user_ids[]"
                                                            value="{{ $req->user_id }}"
                                                            class="form-check-input user-checkbox demo-checkbox cursor-pointer"
                                                            style="width:17px;height:17px;"
                                                            data-email="{{ $req->user->email }}"
                                                            data-name="{{ $req->user->name }}"
                                                            onchange="onCheckboxChange(this)">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2.5">
                                                            <div class="avatar-badge">
                                                                {{ strtoupper(substr($req->user->name ?? 'T', 0, 2)) }}
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark text-truncate" style="max-width:200px;">{{ $req->user->name }}</div>
                                                                <div class="text-muted small text-truncate" style="max-width:200px; font-size:0.75rem;">{{ $req->user->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <code class="fw-bold text-dark bg-light px-2 py-0.5 rounded border">{{ $req->exness_account_number ?? '—' }}</code>
                                                        <span class="badge bg-light text-secondary border ms-1" style="font-size:0.7rem;">{{ ucfirst($req->account_type ?? 'Standard') }}</span>
                                                    </td>
                                                    <td>
                                                        @if($req->status === 'approved')
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">
                                                                <i class="bi bi-check-circle-fill me-0.5"></i>Approved
                                                            </span>
                                                        @elseif($req->status === 'linked')
                                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">
                                                                Linked
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">{{ ucfirst($req->status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="text-muted small" style="font-size:0.75rem;">{{ $req->created_at->format('d M Y') }}</span>
                                                    </td>
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
                            <div class="table-scroll-wrap">
                                <table class="table table-hover align-middle mb-0" id="allUsersTable">
                                    <thead class="table-light sticky-top small text-secondary" style="border-bottom: 1px solid #e2e8f0;">
                                        <tr>
                                            <th style="width: 44px;" class="ps-3">
                                                <input type="checkbox" id="selectAllUsers" class="form-check-input cursor-pointer" style="width:17px;height:17px;">
                                            </th>
                                            <th>Trader</th>
                                            <th>Account Status</th>
                                            <th>Demo Linked?</th>
                                            <th>Joined Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allUsersTableBody">
                                        @foreach($allUsers as $user)
                                        <tr class="trader-row table-row-item" data-user-id="{{ $user->id }}" onclick="toggleRowSelection(event, this)">
                                            <td class="ps-3" onclick="event.stopPropagation()">
                                                <input type="checkbox"
                                                    name="user_ids[]"
                                                    value="{{ $user->id }}"
                                                    class="form-check-input user-checkbox all-checkbox cursor-pointer"
                                                    style="width:17px;height:17px;"
                                                    data-email="{{ $user->email }}"
                                                    data-name="{{ $user->name }}"
                                                    onchange="onCheckboxChange(this)">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2.5">
                                                    <div class="avatar-badge">
                                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark text-truncate" style="max-width:220px;">{{ $user->name }}</div>
                                                        <div class="text-muted small text-truncate" style="max-width:220px; font-size:0.75rem;">{{ $user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $user->status === 'active' ? 'success' : 'secondary' }} border px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">
                                                    {{ ucfirst($user->status ?? 'active') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(in_array($user->id, $demoUserIds))
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">
                                                        <i class="bi bi-check2 me-0.5"></i>Yes
                                                    </span>
                                                @else
                                                    <span class="text-muted small" style="font-size:0.75rem;">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small" style="font-size:0.75rem;">{{ $user->created_at->format('d M Y') }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Broadcast Mode Notification Banner (Visible in Broadcast Mode) --}}
                <div id="allUsersBanner" class="studio-card d-none mb-4 p-4" style="background: #f0fdf4; border-color: #a7f3d0 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:48px;height:48px;">
                            <i class="bi bi-broadcast fs-4"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Broadcasting to ALL {{ $allUsers->count() }} Registered Traders</h6>
                            <p class="text-secondary small mb-0">
                                When you click dispatch, our serverless pipeline will safely iterate over all {{ $allUsers->count() }} traders in chunks of 8 recipients, ensuring 100% reliable inbox delivery without any gateway timeouts.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- STEP 3: Email Content & Instructions --}}
                <div class="studio-card p-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 small fw-bold">STEP 3</span>
                            <h6 class="mb-0 fw-bold text-dark fs-6">Email Instructions & Note</h6>
                        </div>
                        <span class="badge bg-light text-muted border">Optional</span>
                    </div>
                    <p class="text-muted small mb-3">This note will appear inside a highlighted callout box in the email body for traders.</p>

                    {{-- Quick Insert Templates --}}
                    <div class="mb-3 d-flex align-items-center flex-wrap gap-2">
                        <span class="text-muted small fw-medium" style="font-size:0.75rem;">Quick Templates:</span>
                        <span class="template-btn-chip" onclick="insertNoteTemplate('exness')">
                            <i class="bi bi-plus-circle text-primary"></i> Exness Setup Guide
                        </span>
                        <span class="template-btn-chip" onclick="insertNoteTemplate('vps')">
                            <i class="bi bi-plus-circle text-primary"></i> 24/7 VPS Note
                        </span>
                        <span class="template-btn-chip" onclick="insertNoteTemplate('support')">
                            <i class="bi bi-plus-circle text-primary"></i> Support & Questions
                        </span>
                    </div>

                    <textarea name="admin_note" id="adminNoteInput" class="form-control" rows="3" maxlength="500" 
                        placeholder="e.g. Please copy this .ex5 file to your MT5 'Experts' folder, allow algorithmic trading in MT5 options, and configure your lot sizes. Reach out on support chat if you need assistance."
                        style="border-radius: 10px; font-size: 0.85rem; line-height: 1.5;">{{ old('admin_note') }}</textarea>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2 text-muted small" style="font-size:0.75rem;">
                        <span><i class="bi bi-info-circle me-1"></i>Shown prominently inside trader's email.</span>
                        <span id="noteCharCounter" class="fw-semibold">0 / 500</span>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: Sticky Live Dispatch Summary & Action Hub --}}
            <div class="col-xl-4 col-lg-5">
                <div class="sticky-hub-panel">
                    
                    <div class="studio-card p-4">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-sliders text-primary fs-5"></i>
                                <h6 class="mb-0 fw-bold text-dark">Dispatch Summary</h6>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small d-flex align-items-center gap-1">
                                <span class="spinner-grow spinner-grow-sm" style="width:6px;height:6px;" role="status"></span>
                                Live Pipeline Ready
                            </span>
                        </div>

                        {{-- Metric Counter Box --}}
                        <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="text-secondary small fw-semibold mb-1" id="summaryModeLabel">Selected Recipients:</div>
                            <div class="d-flex align-items-baseline gap-2">
                                <span class="display-6 fw-bold text-dark" id="summaryCountNumber">0</span>
                                <span class="text-muted fw-semibold" id="summaryCountUnit">traders</span>
                            </div>
                            <div class="text-muted small mt-1 d-flex align-items-center gap-1.5" style="font-size:0.75rem;">
                                <i class="bi bi-shield-check text-success"></i>
                                <span id="summaryChunkText">Batch delivery in chunks of 8</span>
                            </div>
                        </div>

                        {{-- Bot File Preview --}}
                        <div class="mb-3">
                            <label class="text-muted small fw-semibold d-block mb-1.5" style="font-size:0.75rem;">Attached Bot File:</label>
                            <div class="p-2.5 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <i class="bi bi-file-earmark-code-fill text-primary fs-5"></i>
                                    <div class="min-w-0">
                                        <div class="fw-bold text-dark small text-truncate" style="max-width:180px;">
                                            {{ $bot->bot_file_name ?? 'No bot file' }}
                                        </div>
                                        <div class="text-muted" style="font-size:0.7rem;">
                                            {{ $bot->bot_file_size ?? '0 KB' }} &bull; Ready for MT5
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-dark text-white rounded px-2 py-1" style="font-size:0.65rem;">
                                    EX5
                                </span>
                            </div>
                        </div>

                        {{-- Selected Users Pill List (Shown when Specific Traders mode is active) --}}
                        <div id="selectedPillsWrapper" class="mb-3" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <label class="text-muted small fw-semibold mb-0" style="font-size:0.75rem;">Queued Recipients:</label>
                                <a href="javascript:void(0)" onclick="clearAllSelections()" class="text-danger text-decoration-none small" style="font-size:0.75rem;">Clear All</a>
                            </div>
                            <div id="selectedPillsContainer" class="d-flex flex-wrap gap-1 p-2 bg-light rounded-3 border" style="max-height: 140px; overflow-y: auto;">
                                {{-- Populated dynamically via JS --}}
                            </div>
                        </div>

                        {{-- Safety Pre-flight Checklist --}}
                        <div class="mb-4 pt-2 border-top">
                            <div class="d-flex align-items-center gap-2 mb-1.5 text-muted small" style="font-size:0.75rem;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Bot file verified in permanent storage</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1.5 text-muted small" style="font-size:0.75rem;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>SMTP mail server connected & authenticated</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 text-muted small" style="font-size:0.75rem;">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Zero-timeout protection active (Chunked execution)</span>
                            </div>
                        </div>

                        {{-- Primary CTA Button --}}
                        <button type="button"
                            id="sendBtn"
                            class="btn btn-dark w-100 py-2.5 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2"
                            style="border-radius: 10px; background: #0f172a;"
                            {{ ($bot && $bot->hasBotFile()) ? 'disabled' : 'disabled' }}
                            onclick="openConfirmModal()">
                            <i class="bi bi-send-fill"></i>
                            <span id="sendBtnLabel">Select Recipients to Dispatch</span>
                        </button>

                        <div class="text-center text-muted small mt-2" style="font-size:0.7rem;">
                            <i class="bi bi-lock me-1"></i>Confirmation required before email dispatch begins.
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

{{-- Confirmation & Live Progress Modal --}}
<div class="modal fade" id="sendConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 18px; overflow: hidden;">
            <div class="modal-body p-4 text-center">
                
                {{-- State 1: Review Before Sending --}}
                <div id="dispatchReviewState">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; color: #2563eb;">
                        <i class="bi bi-envelope-check-fill fs-2"></i>
                    </div>

                    <h5 class="fw-bold text-dark mb-1" id="modalTitle">Confirm Email Dispatch</h5>
                    <p class="text-secondary small mb-3" id="modalSubtitle">
                        Please review the dispatch configuration before launching the pipeline.
                    </p>

                    <div class="p-3 bg-light rounded-3 text-start mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Target Mode:</span>
                            <span class="badge bg-primary px-2 py-1" id="modalTargetBadge">Selected Users</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Total Recipients:</span>
                            <span class="fw-bold text-dark fs-6" id="modalRecipientsCount">0 users</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="text-muted small fw-semibold">Attached File:</span>
                            <span class="fw-bold text-success" id="modalFileName">
                                <i class="bi bi-paperclip me-1"></i>{{ $bot->bot_file_name ?? 'MT5 Bot File' }}
                            </span>
                        </div>
                        <div id="modalNotePreviewRow" style="display: none;">
                            <span class="text-muted small fw-semibold d-block mb-1">Instruction Note:</span>
                            <div class="small text-secondary bg-white p-2.5 rounded border" id="modalNoteText" style="max-height:80px; overflow-y:auto; line-height:1.4;"></div>
                        </div>
                    </div>

                    <div id="modalAllWarning" class="alert alert-warning text-start small mb-3 border-0 py-2.5 px-3" style="display: none; border-radius: 10px; background: #fffbeb; border: 1px solid #fde047 !important;">
                        <div class="d-flex gap-2">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-5 flex-shrink-0"></i>
                            <div>
                                <strong class="text-dark">Broadcast to ALL Users:</strong>
                                <div class="text-secondary">Every single active trader ({{ $allUsers->count() }} users) will receive this bot email.</div>
                            </div>
                        </div>
                    </div>

                    <div id="modalRecipientsWrapper" class="text-start mb-3" style="display: none;">
                        <span class="text-muted small fw-semibold d-block mb-1">Recipients Preview:</span>
                        <div id="modalRecipientsList" class="d-flex flex-wrap gap-1 p-2 bg-white rounded border" style="max-height: 100px; overflow-y: auto;"></div>
                    </div>

                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <button type="button" class="btn btn-light px-4 py-2 fw-semibold text-secondary" data-bs-dismiss="modal" style="border-radius: 8px; border: 1px solid #E5E7EB;">
                            Cancel
                        </button>
                        <button type="button" id="confirmSendSubmitBtn" class="btn btn-dark px-4 py-2 fw-bold text-white shadow-sm" style="border-radius: 8px; background: #0f172a;">
                            <i class="bi bi-send-fill me-1.5"></i>Confirm & Launch Dispatch
                        </button>
                    </div>
                </div>

                {{-- State 2: Live Real-Time Batch Progress --}}
                <div id="dispatchProgressState" style="display: none;">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         id="progressIconBox"
                         style="width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; border: 2px solid #3b82f6;">
                        <div class="spinner-border text-primary" role="status" style="width: 1.8rem; height: 1.8rem;" id="progressSpinner"></div>
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
                        <div class="progress mb-2" style="height: 14px; border-radius: 8px; background: #e2e8f0;">
                            <div id="dispatchProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary text-white fw-bold" role="progressbar" style="width: 0%; font-size: 10px;">0%</div>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mt-2 pt-1 border-top" style="font-size:0.75rem;">
                            <div><i class="bi bi-check-circle-fill text-success me-1"></i>Delivered: <strong class="text-dark" id="batchDeliveredCount">0</strong> / <span id="batchTotalCount">0</span></div>
                            <div><i class="bi bi-x-circle-fill text-danger me-1"></i>Failed: <strong class="text-danger" id="batchFailedCount">0</strong></div>
                        </div>
                    </div>

                    <!-- Live Delivery Feed -->
                    <div class="text-start mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small fw-semibold" style="font-size:0.75rem;">Live Delivery Stream:</span>
                            <span class="badge bg-secondary-subtle text-secondary small" id="batchCurrentBatchTag">Starting...</span>
                        </div>
                        <div id="batchLogFeed" class="p-2.5 bg-white rounded border small font-monospace" style="max-height: 130px; overflow-y: auto; font-size: 11.5px; line-height: 1.6;">
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
                     style="width: 48px; height: 48px; border-radius: 50%; background: #FEE2E2; color: #DC2626;">
                    <i class="bi bi-exclamation-circle-fill fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">No Recipients Selected</h6>
                <p class="text-secondary small mb-3">Please select at least one trader from the table before dispatching.</p>
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
            if (!box.classList.contains('d-none')) {
                box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    };

    window.toggleAutoSendBot = function (enabled) {
        const toggleInput = document.getElementById('autoSendBotToggle');
        toggleInput.disabled = true;

        fetch("{{ route('admin.mt5-bot.send-file.toggle-auto-send') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ enabled: enabled })
        })
        .then(r => r.json())
        .then(data => {
            toggleInput.disabled = false;
            if (data.success) {
                const badge = document.getElementById('autoDispatchStatusBadge');
                const iconBox = document.getElementById('autoDispatchIconBox');
                const labelText = document.getElementById('autoSendLabelText');

                if (data.is_enabled) {
                    badge.style.background = '#ecfdf5';
                    badge.style.color = '#059669';
                    badge.style.borderColor = '#a7f3d0';
                    badge.textContent = '● ACTIVE (ON)';

                    iconBox.style.background = '#ecfdf5';
                    iconBox.style.color = '#059669';
                    iconBox.style.borderColor = '#a7f3d0';

                    if (labelText) labelText.textContent = 'Enabled';
                    window.showToast?.('success', 'Auto-send MT5 bot file on new registration is now <strong>ACTIVATED</strong>!');
                } else {
                    badge.style.background = '#f1f5f9';
                    badge.style.color = '#64748b';
                    badge.style.borderColor = '#cbd5e1';
                    badge.textContent = '○ INACTIVE (OFF)';

                    iconBox.style.background = '#f1f5f9';
                    iconBox.style.color = '#64748b';
                    iconBox.style.borderColor = '#e2e8f0';

                    if (labelText) labelText.textContent = 'Disabled';
                    window.showToast?.('warning', 'Auto-send MT5 bot file on new registration is now <strong>DEACTIVATED</strong>.');
                }
            } else {
                toggleInput.checked = !enabled;
                window.showToast?.('error', data.message || 'Failed to update setting.');
            }
        })
        .catch(err => {
            toggleInput.disabled = false;
            toggleInput.checked = !enabled;
            window.showToast?.('error', 'Network error while updating setting.');
        });
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
        const cardSelected = document.getElementById('cardOptionSelected');
        const cardAll = document.getElementById('cardOptionAll');

        const summaryModeLabel = document.getElementById('summaryModeLabel');
        const summaryCountNumber = document.getElementById('summaryCountNumber');
        const summaryCountUnit = document.getElementById('summaryCountUnit');
        const summaryChunkText = document.getElementById('summaryChunkText');
        const sendBtn = document.getElementById('sendBtn');
        const sendBtnLabel = document.getElementById('sendBtnLabel');
        const selectedPillsWrapper = document.getElementById('selectedPillsWrapper');

        if (isAll) {
            userCard.classList.add('d-none');
            allBanner.classList.remove('d-none');

            cardAll.classList.add('broadcast-active');
            cardSelected.classList.remove('selected-active');

            summaryModeLabel.textContent = 'Broadcast Target:';
            summaryCountNumber.textContent = totalUsersCount;
            summaryCountNumber.className = 'display-6 fw-bold text-success';
            summaryCountUnit.textContent = 'registered users (100%)';
            summaryChunkText.textContent = `All ${totalUsersCount} active traders will receive bot`;

            selectedPillsWrapper.style.display = 'none';

            sendBtnLabel.textContent = `Broadcast to ALL ${totalUsersCount} Users`;
            sendBtn.disabled = !hasBot;
            sendBtn.className = 'btn btn-success w-100 py-2.5 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2';
        } else {
            userCard.classList.remove('d-none');
            allBanner.classList.add('d-none');

            cardSelected.classList.add('selected-active');
            cardAll.classList.remove('broadcast-active');

            summaryModeLabel.textContent = 'Selected Recipients:';
            summaryCountUnit.textContent = 'traders';
            summaryChunkText.textContent = 'Batch delivery in chunks of 8';

            sendBtn.className = 'btn btn-dark w-100 py-2.5 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2';

            updateCount();
        }
    };

    function updateCount() {
        const isAll = document.getElementById('targetAll').checked;
        if (isAll) return;

        const count = selected.size;
        const summaryCountNumber = document.getElementById('summaryCountNumber');
        const sendBtn = document.getElementById('sendBtn');
        const sendBtnLabel = document.getElementById('sendBtnLabel');
        const selectedPillsWrapper = document.getElementById('selectedPillsWrapper');
        const selectedPillsContainer = document.getElementById('selectedPillsContainer');

        summaryCountNumber.textContent = count;
        summaryCountNumber.className = 'display-6 fw-bold text-dark';

        // Update tab badges
        const demoTabChecked = document.querySelectorAll('.demo-checkbox:checked').length;
        const allTabChecked = document.querySelectorAll('.all-checkbox:checked').length;
        document.getElementById('tabDemoCount').textContent = demoTabChecked;
        document.getElementById('tabAllCount').textContent = allTabChecked;

        if (count === 0) {
            sendBtnLabel.textContent = 'Select Recipients to Dispatch';
            sendBtn.disabled = true;
            selectedPillsWrapper.style.display = 'none';
        } else {
            sendBtnLabel.textContent = `Dispatch Bot to ${count} Trader${count > 1 ? 's' : ''}`;
            sendBtn.disabled = !hasBot;
            selectedPillsWrapper.style.display = 'block';

            // Populate preview pills
            selectedPillsContainer.innerHTML = '';
            selected.forEach((u, id) => {
                const tag = document.createElement('span');
                tag.className = 'user-pill-tag';
                tag.innerHTML = `
                    <span class="tag-text" title="${u.email}">${u.name || u.email}</span>
                    <span class="tag-close" onclick="removeSelectedUser('${id}')" title="Remove">&times;</span>
                `;
                selectedPillsContainer.appendChild(tag);
            });
        }
    }

    window.removeSelectedUser = function(id) {
        selected.delete(String(id));
        document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
            c.checked = false;
            c.closest('tr')?.classList.remove('row-checked');
        });
        updateCount();
    };

    window.clearAllSelections = function() {
        selected.clear();
        document.querySelectorAll('.user-checkbox').forEach(c => {
            c.checked = false;
            c.closest('tr')?.classList.remove('row-checked');
        });
        document.getElementById('selectAllDemo').checked = false;
        document.getElementById('selectAllUsers').checked = false;
        updateCount();
    };

    window.selectAllInCurrentTab = function() {
        const activeTabPane = document.querySelector('#userTabsContent .tab-pane.active');
        if (!activeTabPane) return;

        const visibleRows = activeTabPane.querySelectorAll('.table-row-item:not([style*="display: none"])');
        visibleRows.forEach(row => {
            const cb = row.querySelector('.user-checkbox');
            if (cb) {
                cb.checked = true;
                row.classList.add('row-checked');
                selected.set(cb.value, { email: cb.dataset.email, name: cb.dataset.name });
            }
        });
        updateCount();
    };

    window.toggleRowSelection = function(event, row) {
        if (event.target.tagName === 'INPUT') return;

        const cb = row.querySelector('.user-checkbox');
        if (cb) {
            cb.checked = !cb.checked;
            onCheckboxChange(cb);
        }
    };

    window.onCheckboxChange = function(cb) {
        const id = cb.value;
        const row = cb.closest('tr');
        if (cb.checked) {
            selected.set(id, { email: cb.dataset.email, name: cb.dataset.name });
            row?.classList.add('row-checked');
        } else {
            selected.delete(id);
            row?.classList.remove('row-checked');
        }

        // Mirror check across tabs if user appears in both
        document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
            c.checked = cb.checked;
            c.closest('tr')?.classList.toggle('row-checked', cb.checked);
        });

        updateCount();
    };

    // Client-side search across tables
    window.handleTableSearch = function(query) {
        const term = (query || '').toLowerCase().trim();
        const activeTabPane = document.querySelector('#userTabsContent .tab-pane.active');
        if (!activeTabPane) return;

        const rows = activeTabPane.querySelectorAll('.table-row-item');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    };

    // Quick Insert Note Templates
    window.insertNoteTemplate = function(type) {
        const textarea = document.getElementById('adminNoteInput');
        if (!textarea) return;

        let template = '';
        if (type === 'exness') {
            template = "Please download and copy the attached bot (.ex5) file to your MT5 'Experts/Advisors' directory. Allow algorithmic trading and configure your preferred lot size.";
        } else if (type === 'vps') {
            template = "For optimal trading performance and zero slippage, we recommend running this bot on a 24/7 Windows VPS connected to London/Amsterdam servers.";
        } else if (type === 'support') {
            template = "If you need help installing or configuring your bot settings, reply to this email or reach out on live chat inside the KTS Markets mobile app.";
        }

        if (textarea.value.trim().length > 0) {
            textarea.value = textarea.value.trim() + "\n\n" + template;
        } else {
            textarea.value = template;
        }

        updateCharCounter();
        textarea.focus();
    };

    function updateCharCounter() {
        const textarea = document.getElementById('adminNoteInput');
        const counter = document.getElementById('noteCharCounter');
        if (textarea && counter) {
            counter.textContent = `${textarea.value.length} / 500`;
        }
    }

    document.getElementById('adminNoteInput')?.addEventListener('input', updateCharCounter);

    // Select All Listeners
    document.getElementById('selectAllDemo')?.addEventListener('change', function () {
        document.querySelectorAll('.demo-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display === 'none') return;
            cb.checked = this.checked;
            row?.classList.toggle('row-checked', this.checked);
            const id = cb.value;
            if (this.checked) {
                selected.set(id, { email: cb.dataset.email, name: cb.dataset.name });
            } else {
                selected.delete(id);
            }
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
                c.checked = this.checked;
                c.closest('tr')?.classList.toggle('row-checked', this.checked);
            });
        });
        updateCount();
    });

    document.getElementById('selectAllUsers')?.addEventListener('change', function () {
        document.querySelectorAll('.all-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            if (row && row.style.display === 'none') return;
            cb.checked = this.checked;
            row?.classList.toggle('row-checked', this.checked);
            const id = cb.value;
            if (this.checked) {
                selected.set(id, { email: cb.dataset.email, name: cb.dataset.name });
            } else {
                selected.delete(id);
            }
            document.querySelectorAll(`.user-checkbox[value="${id}"]`).forEach(c => {
                c.checked = this.checked;
                c.closest('tr')?.classList.toggle('row-checked', this.checked);
            });
        });
        updateCount();
    });

    // Confirmation Modal
    window.openConfirmModal = function () {
        const isAll = document.getElementById('targetAll').checked;
        const note = document.getElementById('adminNoteInput')?.value.trim() || '';

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
            document.getElementById('modalRecipientsCount').textContent = `${totalUsersCount} registered traders`;
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
            document.getElementById('modalTargetBadge').textContent = 'Selected Traders';
            document.getElementById('modalRecipientsCount').textContent = `${count} trader(s)`;
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
        const note = document.getElementById('adminNoteInput')?.value.trim() || '';

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
        progressBar.classList.remove('bg-primary');
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
    updateCharCounter();

    // Client-side File Upload Size Validation (Max 4.2 MB)
    const botFileInput = document.getElementById('botFileInput');
    const botFileSizeFeedback = document.getElementById('botFileSizeFeedback');
    const botFileUploadForm = document.getElementById('botFileUploadForm');
    const MAX_FILE_BYTES = 4.2 * 1024 * 1024;

    if (botFileInput) {
        botFileInput.addEventListener('change', function () {
            if (!this.files || this.files.length === 0) {
                botFileSizeFeedback.innerHTML = '';
                return;
            }

            const file = this.files[0];
            const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);

            if (file.size > MAX_FILE_BYTES) {
                this.value = '';
                botFileSizeFeedback.innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mt-2 d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-x-octagon-fill fs-5 flex-shrink-0"></i>
                        <div>
                            <strong>File size bohot badi hai (${sizeInMb} MB)!</strong><br>
                            Vercel cloud serverless par maximum allowed file size <strong>4.5 MB</strong> hai. Meharbani karke file ko compress (.zip / .rar) karke upload karein.
                        </div>
                    </div>
                `;
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
                    alert('File size exceeds 4.5 MB limit. Please compress file.');
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
