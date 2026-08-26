@extends('layouts.app')

@section('title', 'Signal Performance Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-graph-up me-2 text-success"></i>Signal Performance Analytics</h4>
        <small class="text-secondary">Win rate, pips analysis, and signal performance</small>
    </div>
    <a href="{{ route('admin.signals.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Signals
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Win Rate</p>
                        <h3 class="text-success mb-1 fw-bold">{{ $overall['win_rate'] }}%</h3>
                        <small class="text-secondary" style="font-size:0.7rem;">{{ $overall['wins'] }}W / {{ $overall['losses'] }}L</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bi bi-trophy text-success fs-4"></i>
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
                        <p class="text-secondary mb-1 small fw-medium">Total Pips</p>
                        <h3 class="{{ $overall['total_pips'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 fw-bold">
                            {{ $overall['total_pips'] >= 0 ? '+' : '' }}{{ $overall['total_pips'] }}
                        </h3>
                        <small class="text-secondary" style="font-size:0.7rem;">Avg: {{ $overall['avg_pips'] }} pips</small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bi bi-arrow-up-right text-info fs-4"></i>
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
                        <h3 class="{{ $overall['best_pips'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 fw-bold">
                            {{ $overall['best_pips'] >= 0 ? '+' : '' }}{{ $overall['best_pips'] }}
                        </h3>
                        <small class="text-secondary" style="font-size:0.7rem;">Highest pips</small>
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
                        <p class="text-secondary mb-1 small fw-medium">Worst Trade</p>
                        <h3 class="{{ $overall['worst_pips'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 fw-bold">
                            {{ $overall['worst_pips'] > 0 ? '+' : '' }}{{ $overall['worst_pips'] }}
                        </h3>
                        <small class="text-secondary" style="font-size:0.7rem;">Lowest pips</small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bi bi-arrow-down-right text-danger fs-4"></i>
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
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly Performance</h6>
            </div>
            <div class="card-body">
                <div style="height: 300px;">
                    <canvas id="monthlyPerformanceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Win/Loss Distribution</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="winLossChart"></canvas>
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
                                <th>Signals</th>
                                <th>Win Rate</th>
                                <th>Total Pips</th>
                                <th class="pe-3">Avg Pips</th>
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
                                    <span class="{{ $s['total_pips'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $s['total_pips'] >= 0 ? '+' : '' }}{{ $s['total_pips'] }}
                                    </span>
                                </td>
                                <td class="pe-3">
                                    <span class="{{ $s['avg_pips'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $s['avg_pips'] >= 0 ? '+' : '' }}{{ $s['avg_pips'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">No closed signals yet.</td>
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
                <h6 class="mb-0"><i class="bi bi-tags me-2"></i>Performance by Category</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Category</th>
                                <th>Signals</th>
                                <th>Win Rate</th>
                                <th class="pe-3">Total Pips</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byCategory as $cat)
                            <tr>
                                <td class="ps-3"><span class="fw-semibold text-dark">{{ $cat['category'] }}</span></td>
                                <td><span class="text-dark">{{ $cat['total'] }}</span></td>
                                <td>
                                    @php $color = $cat['win_rate'] >= 60 ? 'success' : ($cat['win_rate'] >= 40 ? 'warning' : 'danger'); @endphp
                                    <span class="text-{{ $color }}">{{ $cat['win_rate'] }}%</span>
                                </td>
                                <td class="pe-3">
                                    <span class="{{ $cat['total_pips'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $cat['total_pips'] >= 0 ? '+' : '' }}{{ $cat['total_pips'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">No categorized signals yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Closed Signals</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Symbol</th>
                        <th>Direction</th>
                        <th>Result</th>
                        <th>Pips</th>
                        <th class="pe-3">Closed At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentSignals as $signal)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.signals.show', $signal) }}" class="text-dark text-decoration-none fw-medium">
                                {{ Str::limit($signal->title, 30) }}
                            </a>
                        </td>
                        <td><span class="badge bg-secondary">{{ $signal->symbol }}</span></td>
                        <td>
                            <span class="badge bg-{{ $signal->direction === 'buy' ? 'success' : 'danger' }}">
                                {{ strtoupper($signal->direction) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $signal->result === 'win' ? 'success' : 'danger' }}">
                                {{ strtoupper($signal->result ?? 'N/A') }}
                            </span>
                        </td>
                        <td>
                            @if($signal->pips_result !== null)
                            <span class="{{ $signal->pips_result >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                {{ $signal->pips_result >= 0 ? '+' : '' }}{{ $signal->pips_result }}
                            </span>
                            @else
                            <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="pe-3 small text-secondary">{{ $signal->closed_at?->diffForHumans() ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No closed signals yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = @json($monthlyPerformance);
    const overallData = @json($overall);

    const gridColor = 'rgba(255,255,255,0.06)';
    const tickColor = '#6c757d';

    new Chart(document.getElementById('monthlyPerformanceChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [
                {
                    label: 'Win Rate %',
                    data: monthlyData.map(d => d.win_rate),
                    backgroundColor: 'rgba(25, 135, 84, 0.6)',
                    borderColor: '#198754',
                    borderWidth: 0,
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Total Pips',
                    data: monthlyData.map(d => d.pips),
                    type: 'line',
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#0d6efd',
                    borderWidth: 2,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { labels: { color: '#adb5bd', usePointStyle: true } },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 10, cornerRadius: 8 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                y: {
                    position: 'left',
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: tickColor, font: { size: 11 }, callback: v => v + '%' },
                    beginAtZero: true,
                    max: 100,
                    title: { display: true, text: 'Win Rate %', color: tickColor }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: tickColor, font: { size: 11 } },
                    title: { display: true, text: 'Pips', color: tickColor }
                }
            }
        }
    });

    new Chart(document.getElementById('winLossChart'), {
        type: 'doughnut',
        data: {
            labels: ['Wins', 'Losses', 'Pending'],
            datasets: [{
                data: [overallData.wins, overallData.losses, overallData.pending],
                backgroundColor: ['#198754', '#dc3545', '#6c757d'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { color: '#adb5bd', padding: 16, usePointStyle: true } },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 10, cornerRadius: 8 }
            }
        }
    });
});
</script>
@endpush
