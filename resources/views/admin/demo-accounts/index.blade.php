@extends('layouts.app')

@section('title', 'Demo Account Requests')

@section('content')
<style>
.stat-card {
    border-radius: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08) !important;
}
.ring-active-warning {
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25) !important;
}
.ring-active-success {
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25) !important;
}
.ring-active-danger {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25) !important;
}
.ring-active-info {
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.25) !important;
}
</style>

{{-- Top Header & Navigation --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary">Dashboard</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Demo Account Requests</li>
            </ol>
        </nav>
        <h4 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="bi bi-pc-display-horizontal me-2 text-primary"></i>Demo Account Requests
        </h4>
        <p class="text-secondary small mb-0">Review client demo applications, verify Exness account IDs, and dispatch MT5 bot files.</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6 fw-bold">
            <i class="bi bi-layers-fill me-1"></i>{{ $stats['total'] }} Total Requests
        </span>
        <a href="{{ route('admin.demo-settings.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold" style="border-radius:10px;">
            <i class="bi bi-gear-fill me-1 text-primary"></i>Demo Settings
        </a>
        <a href="{{ route('admin.mt5-bot.send-file') }}" class="btn btn-primary btn-sm px-3 py-2 fw-semibold shadow-sm" style="border-radius:10px;">
            <i class="bi bi-send-fill me-1"></i>Send Bot File
        </a>
    </div>
</div>

{{-- Modern Interactive Stats Cards --}}
<div class="row g-3 mb-4">
    {{-- 1. Pending Card --}}
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.demo-accounts.index', ['status' => 'pending']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 stat-card {{ request('status') === 'pending' ? 'ring-active-warning' : '' }}" 
                 style="border: 1px solid {{ request('status') === 'pending' ? '#f59e0b' : '#e2e8f0' }} !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary text-uppercase fw-bold d-block mb-1" style="font-size:0.75rem; letter-spacing:0.5px;">Pending Review</span>
                        <h2 class="mb-1 fw-bold text-dark">{{ $stats['pending'] }}</h2>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 small">
                            <i class="bi bi-hourglass-split me-1"></i>Requires Action
                        </span>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width:56px; height:56px; background:#fffbeb; color:#d97706; border:1px solid #fef3c7;">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- 2. Approved Card --}}
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.demo-accounts.index', ['status' => 'approved']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 stat-card {{ request('status') === 'approved' ? 'ring-active-success' : '' }}" 
                 style="border: 1px solid {{ request('status') === 'approved' ? '#10b981' : '#e2e8f0' }} !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary text-uppercase fw-bold d-block mb-1" style="font-size:0.75rem; letter-spacing:0.5px;">Approved Accounts</span>
                        <h2 class="mb-1 fw-bold text-dark">{{ $stats['approved'] }}</h2>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 small">
                            <i class="bi bi-check2-circle me-1"></i>Active Traders
                        </span>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width:56px; height:56px; background:#ecfdf5; color:#059669; border:1px solid #d1fae5;">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- 3. Rejected Card --}}
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.demo-accounts.index', ['status' => 'rejected']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 stat-card {{ request('status') === 'rejected' ? 'ring-active-danger' : '' }}" 
                 style="border: 1px solid {{ request('status') === 'rejected' ? '#ef4444' : '#e2e8f0' }} !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary text-uppercase fw-bold d-block mb-1" style="font-size:0.75rem; letter-spacing:0.5px;">Rejected Requests</span>
                        <h2 class="mb-1 fw-bold text-dark">{{ $stats['rejected'] }}</h2>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 small">
                            <i class="bi bi-x-circle me-1"></i>Declined
                        </span>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width:56px; height:56px; background:#fef2f2; color:#dc2626; border:1px solid #fee2e2;">
                        <i class="bi bi-x-octagon fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>

    {{-- 4. Linked Card --}}
    <div class="col-xl-3 col-sm-6">
        <a href="{{ route('admin.demo-accounts.index', ['status' => 'linked']) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 stat-card {{ request('status') === 'linked' ? 'ring-active-info' : '' }}" 
                 style="border: 1px solid {{ request('status') === 'linked' ? '#0ea5e9' : '#e2e8f0' }} !important;">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-secondary text-uppercase fw-bold d-block mb-1" style="font-size:0.75rem; letter-spacing:0.5px;">Linked to MT5</span>
                        <h2 class="mb-1 fw-bold text-dark">{{ $stats['linked'] }}</h2>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 small">
                            <i class="bi bi-link-45deg me-1"></i>Connected
                        </span>
                    </div>
                    <div class="rounded-4 d-flex align-items-center justify-content-center shadow-sm" 
                         style="width:56px; height:56px; background:#f0f9ff; color:#0284c7; border:1px solid #e0f2fe;">
                        <i class="bi bi-cpu fs-3"></i>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Modern Filter Box with Status Tabs --}}
<div class="card mb-4 border-0 shadow-sm" style="border-radius:16px; border:1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        {{-- Quick Status Filter Pills --}}
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="small fw-bold text-secondary text-uppercase me-1" style="font-size: 0.75rem;">Status Filter:</span>
            <a href="{{ route('admin.demo-accounts.index', array_merge(request()->except('status', 'page'), ['status' => ''])) }}" 
               class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }} px-3 py-1" style="border-radius:20px;">
                All ({{ $stats['total'] }})
            </a>
            <a href="{{ route('admin.demo-accounts.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
               class="btn btn-sm {{ request('status') === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }} px-3 py-1" style="border-radius:20px;">
                Pending ({{ $stats['pending'] }})
            </a>
            <a href="{{ route('admin.demo-accounts.index', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}" 
               class="btn btn-sm {{ request('status') === 'approved' ? 'btn-success fw-bold' : 'btn-outline-secondary' }} px-3 py-1" style="border-radius:20px;">
                Approved ({{ $stats['approved'] }})
            </a>
            <a href="{{ route('admin.demo-accounts.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}" 
               class="btn btn-sm {{ request('status') === 'rejected' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }} px-3 py-1" style="border-radius:20px;">
                Rejected ({{ $stats['rejected'] }})
            </a>
            <a href="{{ route('admin.demo-accounts.index', array_merge(request()->except('status', 'page'), ['status' => 'linked'])) }}" 
               class="btn btn-sm {{ request('status') === 'linked' ? 'btn-info fw-bold' : 'btn-outline-secondary' }} px-3 py-1" style="border-radius:20px;">
                Linked ({{ $stats['linked'] }})
            </a>
        </div>

        @php
            $hasActiveFilters = filled(request('search')) ||
                                filled(request('status')) ||
                                filled(request('date_from')) ||
                                filled(request('date_to'));
        @endphp
        @if($hasActiveFilters)
        <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-sm btn-outline-danger px-3 py-1" style="border-radius:8px;" title="Clear all filters">
            <i class="bi bi-x-circle me-1"></i>Clear Filters
        </a>
        @endif
    </div>

    <div class="card-body p-3 bg-light bg-opacity-25">
        <form method="GET" class="row g-2 align-items-end" id="demoAccountFilterForm">
            <input type="hidden" name="status" value="{{ request('status') }}">

            <div class="col-xl-5 col-lg-5 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search Applications</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius:10px 0 0 10px;">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="Search by client name, email, phone, Exness account..." 
                           value="{{ request('search') }}"
                           style="border-radius:0 10px 10px 0;">
                </div>
            </div>

            <div class="col-xl-4 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Requested Date Range</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary"><i class="bi bi-calendar3"></i></span>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                    <span class="input-group-text bg-light text-muted">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4 py-2 w-100 fw-semibold" style="border-radius:10px;">
                    <i class="bi bi-funnel-fill me-1"></i>Apply Filters
                </button>
                @if($hasActiveFilters)
                <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-outline-secondary px-3 py-2" style="border-radius:10px;" title="Reset filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Requests Table --}}
<div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="bi bi-table text-primary me-2"></i>Application Records
            <span class="badge bg-secondary-subtle text-secondary ms-2">{{ $requests->total() ?? count($requests) }} records</span>
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary text-uppercase small" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>User & Client Details</th>
                    <th>Demo Contact</th>
                    <th>Exness Account</th>
                    <th>Account Type</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr style="{{ $req->status === 'pending' ? 'background: #fffbeb !important;' : '' }}">
                    <td class="ps-4">
                        <span class="badge bg-light text-dark border fw-bold">#{{ $req->id }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold flex-shrink-0" style="width:38px;height:38px;font-size:14px;">
                                {{ strtoupper(substr($req->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-0">{{ $req->user->name ?? 'Deleted User' }}</div>
                                <small class="text-secondary">{{ $req->user->email ?? '-' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($req->demo_email)
                            <div class="d-flex align-items-center text-dark small"><i class="bi bi-envelope text-secondary me-1"></i>{{ $req->demo_email }}</div>
                        @endif
                        @if($req->demo_phone)
                            <div class="d-flex align-items-center text-secondary small"><i class="bi bi-telephone text-secondary me-1"></i>{{ $req->demo_phone }}</div>
                        @endif
                        @if(!$req->demo_email && !$req->demo_phone)
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        @if($req->exness_account_number)
                            <code class="px-2 py-1 bg-light border rounded fw-bold text-primary">{{ $req->exness_account_number }}</code>
                        @else
                            <span class="text-muted small">Not provided</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                            {{ ucfirst($req->account_type ?? 'Standard') }}
                        </span>
                    </td>
                    <td>
                        @switch($req->status)
                            @case('pending')
                                <span class="badge bg-warning bg-opacity-25 text-warning-emphasis border border-warning px-2 py-1">
                                    <i class="bi bi-clock-history me-1"></i>Pending
                                </span>
                                @break
                            @case('approved')
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>Approved
                                </span>
                                @break
                            @case('rejected')
                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1">
                                    <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                </span>
                                @break
                            @case('linked')
                                <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 px-2 py-1">
                                    <i class="bi bi-link-45deg me-1"></i>Linked
                                </span>
                                @break
                        @endswitch
                    </td>
                    <td>
                        <small class="text-dark d-block fw-medium">{{ $req->created_at->format('M d, Y') }}</small>
                        <small class="text-secondary" style="font-size:0.7rem;">{{ $req->created_at->diffForHumans() }}</small>
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('admin.demo-accounts.show', $req) }}" class="btn btn-sm btn-outline-primary" title="View Application Details" style="border-radius:8px;">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($req->status === 'pending')
                            <form method="POST" action="{{ route('admin.demo-accounts.approve', $req) }}" class="d-inline" onsubmit="return confirm('Approve this Demo Account Request?')">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success" title="Approve Request" style="border-radius:8px;">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-secondary">
                        <div class="py-4">
                            <i class="bi bi-inbox text-muted display-4 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">No demo account requests found</h6>
                            <p class="text-secondary small mb-0">Try adjusting your search criteria or filter status.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
