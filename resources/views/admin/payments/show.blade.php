@extends('layouts.app')
@section('title', 'Transaction Details')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Transaction #{{ $transaction->transaction_id }}</h4>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Transaction Details</h6>
                <span class="badge bg-{{ $transaction->status_color }} fs-6">{{ ucfirst($transaction->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><small class="text-secondary d-block">User</small><span class="fw-semibold text-dark">{{ $transaction->user->name ?? 'Deleted' }}</span><div class="text-secondary small">{{ $transaction->user->email ?? '' }}</div></div>
                    <div class="col-md-6"><small class="text-secondary d-block">Amount</small><span class="text-success fw-bold fs-5">{!! \App\Services\CurrencyService::formatAmount($transaction->amount) !!}</span></div>
                    <div class="col-md-4"><small class="text-secondary d-block">Gateway</small><span class="badge bg-{{ $transaction->gateway_color }}">{{ ucfirst(str_replace('_', ' ', $transaction->gateway)) }}</span></div>
                    <div class="col-md-4"><small class="text-secondary d-block">Plan</small><span class="text-dark">{{ ucfirst($transaction->plan_type) }} ({{ $transaction->plan_duration_days }} days)</span></div>
                    <div class="col-md-4"><small class="text-secondary d-block">Currency</small><span class="text-dark">{{ $transaction->currency }}</span></div>
                    <div class="col-md-6"><small class="text-secondary d-block">Description</small><span class="text-dark">{{ $transaction->description ?: '-' }}</span></div>
                    <div class="col-md-6"><small class="text-secondary d-block">Created</small><span class="text-dark">{{ $transaction->created_at->format('M d, Y H:i') }}</span></div>
                </div>
            </div>
        </div>

        @if($transaction->admin_notes)
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Admin Notes</h6></div>
            <div class="card-body"><span class="text-secondary">{{ $transaction->admin_notes }}</span></div>
        </div>
        @endif
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Actions</h6></div>
            <div class="card-body">
                @if(auth()->user()->hasPermission('transactions_manage') && $transaction->status === 'pending')
                <form method="POST" action="{{ route('admin.payments.approve', $transaction) }}">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Admin Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="2" maxlength="1000" placeholder="Optional notes...">{{ $transaction->admin_notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mb-2" onclick="return confirm('Approve this payment and activate premium?')"><i class="bi bi-check-lg me-1"></i>Approve & Activate</button>
                </form>
                <form method="POST" action="{{ route('admin.payments.reject', $transaction) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="admin_notes" value="{{ $transaction->admin_notes }}">
                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Reject this payment?')"><i class="bi bi-x-lg me-1"></i>Reject</button>
                </form>
                @elseif($transaction->status !== 'pending')
                <div class="text-center text-secondary">
                    <p class="mb-1">Processed by: <span class="text-dark">{{ $transaction->approver->name ?? '-' }}</span></p>
                    <p class="mb-0">At: {{ $transaction->approved_at?->format('M d, Y H:i') ?? '-' }}</p>
                </div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header"><h6 class="mb-0">User Status</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Premium:</span><span class="badge bg-{{ $transaction->user?->is_premium ? 'success' : 'secondary' }}">{{ $transaction->user?->is_premium ? 'Active' : 'Inactive' }}</span></div>
                <div class="d-flex justify-content-between"><span class="text-secondary">Expires:</span><span class="text-dark">{{ $transaction->user?->premium_expires_at?->format('M d, Y') ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
