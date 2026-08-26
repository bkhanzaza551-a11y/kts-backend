@if($stats['pending_transactions'] > 0)
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header border-warning d-flex justify-content-between align-items-center">
                <h6 class="text-warning mb-0">
                    <i class="bi bi-hourglass-split me-2"></i>Pending Approvals
                    <span class="badge bg-warning text-dark ms-2">{{ $stats['pending_transactions'] }}</span>
                </h6>
                <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-eye me-1"></i>View All
                </a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="p-3 rounded border text-center">
                            <h3 class="text-warning mb-1 fw-bold">{{ $stats['pending_transactions'] }}</h3>
                            <small class="text-secondary">Pending Transactions</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border text-center">
                            <h3 class="text-info mb-1 fw-bold">{!! \App\Services\CurrencyService::formatAmount($stats['pending_amount']) !!}</h3>
                            <small class="text-secondary">Pending Amount</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border text-center">
                            <h3 class="text-success mb-1 fw-bold">{!! \App\Services\CurrencyService::formatAmount($stats['total_revenue']) !!}</h3>
                            <small class="text-secondary">Total Approved Revenue</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
