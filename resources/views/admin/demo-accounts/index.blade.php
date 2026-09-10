@extends('layouts.app')

@section('title', 'Demo Account Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-pc-display-horizontal me-2 text-primary"></i>Demo Account Requests</h4>
    <span class="badge bg-primary fs-6">{{ $stats['total'] }} Total</span>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-warning">{{ $stats['pending'] }}</div>
                <small class="text-secondary">Pending</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-success">{{ $stats['approved'] }}</div>
                <small class="text-secondary">Approved</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-danger">{{ $stats['rejected'] }}</div>
                <small class="text-secondary">Rejected</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="fs-3 fw-bold text-info">{{ $stats['linked'] }}</div>
                <small class="text-secondary">Linked</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end" id="demoAccountFilterForm">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, email, phone, account #..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="linked" {{ request('status') === 'linked' ? 'selected' : '' }}>Linked</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Requested Between</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                    <span class="input-group-text bg-light text-muted">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>
            </div>
            <div class="col-auto d-flex gap-2 ms-auto pt-2 pt-md-0">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @php
                    $hasActiveFilters = filled(request('search')) ||
                                        filled(request('status')) ||
                                        filled(request('date_from')) ||
                                        filled(request('date_to'));
                @endphp
                @if($hasActiveFilters)
                <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-sm btn-outline-secondary px-3" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Requests Table --}}
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Demo Email / Phone</th>
                    <th>Exness Account</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr class="{{ $req->status === 'pending' ? 'table-warning' : '' }}">
                    <td><strong>#{{ $req->id }}</strong></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width:32px;height:32px;font-size:13px;">
                                {{ strtoupper(substr($req->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $req->user->name ?? 'Deleted' }}</div>
                                <small class="text-secondary">{{ $req->user->email ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($req->demo_email)<div>{{ $req->demo_email }}</div>@endif
                        @if($req->demo_phone)<small class="text-secondary">{{ $req->demo_phone }}</small>@endif
                    </td>
                    <td><code>{{ $req->exness_account_number ?? 'N/A' }}</code></td>
                    <td>
                        <span class="badge bg-light text-dark">{{ ucfirst($req->account_type) }}</span>
                    </td>
                    <td>
                        @switch($req->status)
                            @case('pending')
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                                @break
                            @case('approved')
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                @break
                            @case('rejected')
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                @break
                            @case('linked')
                                <span class="badge bg-info"><i class="bi bi-link-45deg me-1"></i>Linked</span>
                                @break
                        @endswitch
                    </td>
                    <td><small class="text-secondary">{{ $req->created_at->format('M d, Y') }}</small></td>
                    <td class="text-end">
                        <a href="{{ route('admin.demo-accounts.show', $req) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                        @if($req->status === 'pending')
                        <form method="POST" action="{{ route('admin.demo-accounts.approve', $req) }}" class="d-inline" onsubmit="return confirm('Approve this request?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-secondary">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No demo account requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="card-footer bg-white">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
