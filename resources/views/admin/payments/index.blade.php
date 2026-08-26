@extends('layouts.app')
@section('title', 'Payments & Transactions')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Payments & Transactions</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">₨{{ number_format($stats['total_revenue'], 2) }}</h3><small class="text-secondary">Total Revenue</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">₨{{ number_format($stats['today_revenue'], 2) }}</h3><small class="text-secondary">Today</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-warning mb-0 fw-bold">{{ $stats['pending'] }}</h3><small class="text-secondary">Pending</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">{{ $stats['approved'] }}</h3><small class="text-secondary">Approved</small>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-secondary">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Transaction ID, user..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Gateway</label>
                <select name="gateway" class="form-select">
                    <option value="">All</option>
                    <option value="jazzcash" {{ request('gateway') === 'jazzcash' ? 'selected' : '' }}>JazzCash</option>
                    <option value="easypaisa" {{ request('gateway') === 'easypaisa' ? 'selected' : '' }}>EasyPaisa</option>
                    <option value="bank_transfer" {{ request('gateway') === 'bank_transfer' ? 'selected' : '' }}>Bank</option>
                    <option value="manual" {{ request('gateway') === 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button></div>
        </form>
        @if(request()->hasAny(['search','status','gateway']))
        <div class="mt-2"><a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a></div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">User</th><th>Transaction ID</th><th>Amount</th><th>Gateway</th><th>Plan</th><th>Status</th><th>Date</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td class="ps-3"><span class="fw-semibold text-dark">{{ $t->user->name ?? 'Deleted' }}</span><div class="text-secondary small">{{ $t->user->email ?? '' }}</div></td>
                        <td class="text-secondary"><code>{{ $t->transaction_id }}</code></td>
                        <td class="text-dark fw-semibold">{!! \App\Services\CurrencyService::formatAmount($t->amount) !!}</td>
                        <td><span class="badge bg-{{ $t->gateway_color }}">{{ ucfirst(str_replace('_', ' ', $t->gateway)) }}</span></td>
                        <td class="text-secondary">{{ ucfirst($t->plan_type) }} ({{ $t->plan_duration_days }}d)</td>
                        <td><span class="badge bg-{{ $t->status_color }}">{{ ucfirst($t->status) }}</span></td>
                        <td class="text-secondary">{{ $t->created_at->format('M d, Y') }}</td>
                        <td class="pe-3">
                            <a href="{{ route('admin.payments.show', $t) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-secondary py-4"><i class="bi bi-credit-card fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No transactions found.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($transactions->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $transactions->firstItem() }}-{{ $transactions->lastItem() }} of {{ $transactions->total() }}</small>
    {{ $transactions->links() }}
</div>
@endif
@endsection
