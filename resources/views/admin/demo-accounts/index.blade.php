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

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-secondary small">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, email, phone, account..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="linked" {{ request('status') === 'linked' ? 'selected' : '' }}>Linked</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.demo-accounts.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
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
