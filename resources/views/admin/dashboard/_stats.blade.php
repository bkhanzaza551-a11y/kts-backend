<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Total Users</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['total_users']) }}</h4>
                        <div class="d-flex align-items-center mt-1">
                            <span class="badge bg-secondary me-2">
                                <i class="bi bi-arrow-up"></i> {{ $stats['new_today'] }}
                            </span>
                            <small class="text-secondary" style="font-size:0.75rem;">today</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Active Users</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['active_users']) }}</h4>
                        <div class="d-flex align-items-center mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">
                                {{ $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100, 1) : 0 }}% of total
                            </small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Premium Users</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['premium_users']) }}</h4>
                        <div class="d-flex align-items-center mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">
                                {{ $stats['total_users'] > 0 ? round(($stats['premium_users'] / $stats['total_users']) * 100, 1) : 0 }}% conversion
                            </small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-star"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">MT5 Bot Users</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['mt5_bot_users']) }}</h4>
                        <div class="d-flex align-items-center mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">Active connections</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-robot"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">New This Week</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['new_this_week']) }}</h4>
                        <div class="mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">Last 7 days</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-calendar-week"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">New This Month</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['new_this_month']) }}</h4>
                        <div class="mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">{{ now()->format('F Y') }}</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Total Staff</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['total_staff']) }}</h4>
                        <div class="mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">Team members</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Banned Users</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ number_format($stats['banned_users']) }}</h4>
                        <div class="mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">Blocked accounts</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-shield-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Total Revenue</p>
                        <h4 class="text-dark mb-1 fw-bold">{!! \App\Services\CurrencyService::formatAmount($stats['total_revenue']) !!}</h4>
                        <div class="d-flex align-items-center mt-1">
                            @if($stats['revenue_change'] > 0)
                            <span class="badge bg-success me-2">
                                <i class="bi bi-arrow-up"></i> {{ $stats['revenue_change'] }}%
                            </span>
                            @elseif($stats['revenue_change'] < 0)
                            <span class="badge bg-danger me-2">
                                <i class="bi bi-arrow-down"></i> {{ abs($stats['revenue_change']) }}%
                            </span>
                            @else
                            <span class="badge bg-secondary me-2">0%</span>
                            @endif
                            <small class="text-secondary" style="font-size:0.75rem;">vs last month</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">This Month Revenue</p>
                        <h4 class="text-dark mb-1 fw-bold">{!! \App\Services\CurrencyService::formatAmount($stats['this_month_revenue']) !!}</h4>
                        <div class="mt-1">
                            <small class="text-secondary" style="font-size:0.75rem;">{{ now()->format('F Y') }}</small>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Pending Payments</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ $stats['pending_transactions'] }}</h4>
                        <div class="d-flex align-items-center mt-1">
                            <span class="badge bg-warning text-dark me-2">
                                {!! \App\Services\CurrencyService::formatAmount($stats['pending_amount']) !!}
                            </span>
                        </div>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
