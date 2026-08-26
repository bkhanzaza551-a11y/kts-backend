@extends('layouts.app')

@section('title', 'MT5 Bot Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-robot me-2 text-info"></i>MT5 Bot Analytics</h4>
        <small class="text-secondary">Equity curve, P&L analysis, and trade performance</small>
    </div>
    <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to MT5 Bot
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Net Profit</p>
                        <h3 class="{{ $overall['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 fw-bold">
                            ${{ number_format($overall['net_profit'], 2) }}
                        </h3>
                        <small class="text-secondary" style="font-size:0.7rem;">{{ $overall['total_trades'] }} trades</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bi bi-cash-stack text-success fs-4"></i>
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
                        <p class="text-secondary mb-1 small fw-medium">Win Rate</p>
                        <h3 class="text-success mb-1 fw-bold">{{ $overall['win_rate'] }}%</h3>
                        <small class="text-secondary" style="font-size:0.7rem;">{{ $overall['wins'] }}W / {{ $overall['losses'] }}L</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bi bi-trophy text-info fs-4"></i>
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
                        <p class="text-secondary mb-1 small fw-medium">Best Trade</p>
                        <h3 class="text-success mb-1 fw-bold">+${{ number_format($overall['best_trade'], 2) }}</h3>
                        <small class="text-secondary" style="font-size:0.7rem;">Highest single profit</small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bi bi-star text-warning fs-4"></i>
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
                        <p class="text-secondary mb-1 small fw-medium">Open Trades</p>
                        <h3 class="text-primary mb-1 fw-bold">{{ $overall['open_trades'] }}</h3>
                        <small class="text-secondary" style="font-size:0.7rem;">Unrealized: ${{ number_format($overall['open_profit'], 2) }}</small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bi bi-hourglass-split text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Equity Curve</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="equityCurveChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly P&L</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="monthlyPnlChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-currency-exchange me-2"></i>Performance by Symbol</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Symbol</th>
                                <th>Trades</th>
                                <th>Win Rate</th>
                                <th>Net Profit</th>
                                <th class="pe-3">Avg Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bySymbol as $s)
                            <tr>
                                <td class="ps-3"><span class="badge bg-secondary">{{ $s['symbol'] }}</span></td>
                                <td><span class="text-dark">{{ $s['total'] }}</span></td>
                                <td>
                                    @php $color = $s['win_rate'] >= 60 ? 'success' : ($s['win_rate'] >= 40 ? 'warning' : 'danger'); @endphp
                                    <span class="text-{{ $color }}">{{ $s['win_rate'] }}%</span>
                                </td>
                                <td>
                                    <span class="{{ $s['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($s['total_profit'], 2) }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <span class="{{ $s['avg_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($s['avg_profit'], 2) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No trades yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Performance by Strategy</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Strategy</th>
                                <th>Trades</th>
                                <th>Win Rate</th>
                                <th class="pe-3">Net Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byStrategy as $strat)
                            <tr>
                                <td class="ps-3"><span class="fw-semibold text-dark">{{ $strat['strategy'] }}</span></td>
                                <td><span class="text-dark">{{ $strat['total'] }}</span></td>
                                <td>
                                    @php $color = $strat['win_rate'] >= 60 ? 'success' : ($strat['win_rate'] >= 40 ? 'warning' : 'danger'); @endphp
                                    <span class="text-{{ $color }}">{{ $strat['win_rate'] }}%</span>
                                </td>
                                <td class="pe-3">
                                    <span class="{{ $strat['total_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        ${{ number_format($strat['total_profit'], 2) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">No strategy data yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-3 col-md-6">
        <div class="card text-center p-3">
            <small class="text-secondary">Total Trades</small>
            <h4 class="text-dark fw-bold mb-0">{{ number_format($overall['total_trades']) }}</h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-center p-3">
            <small class="text-secondary">Total Commission</small>
            <h4 class="text-warning fw-bold mb-0">${{ number_format($overall['total_commission'], 2) }}</h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-center p-3">
            <small class="text-secondary">Total Swap</small>
            <h4 class="text-info fw-bold mb-0">${{ number_format($overall['total_swap'], 2) }}</h4>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card text-center p-3">
            <small class="text-secondary">Breakeven Trades</small>
            <h4 class="text-secondary fw-bold mb-0">{{ $overall['breakeven'] }}</h4>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const equityData = @json($equityCurve);
    const monthlyData = @json($monthlyPnl);

    const gridColor = 'rgba(255,255,255,0.06)';
    const tickColor = '#6c757d';

    new Chart(document.getElementById('equityCurveChart'), {
        type: 'line',
        data: {
            labels: equityData.map(d => d.date),
            datasets: [{
                label: 'Equity ($)',
                data: equityData.map(d => d.equity),
                borderColor: '#0d6efd',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.2)');
                    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');
                    return gradient;
                },
                fill: true,
                tension: 0.3,
                pointRadius: equityData.length > 50 ? 0 : 3,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#0d6efd',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2,
                borderWidth: 2.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            return 'Equity: $' + ctx.raw.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, maxTicksLimit: 12, font: { size: 11 } } },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '$' + v.toLocaleString() },
                    beginAtZero: false
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyPnlChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Profit ($)',
                data: monthlyData.map(d => d.profit),
                backgroundColor: monthlyData.map(d => d.profit >= 0 ? 'rgba(25, 135, 84, 0.6)' : 'rgba(220, 53, 69, 0.6)'),
                borderColor: monthlyData.map(d => d.profit >= 0 ? '#198754' : '#dc3545'),
                borderWidth: 0,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(ctx) {
                            return '$' + ctx.raw.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 10 } } },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => '$' + v },
                    beginAtZero: false
                }
            }
        }
    });
});
</script>
@endpush
