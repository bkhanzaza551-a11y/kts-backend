<div class="row g-3 mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="text-dark mb-0"><i class="bi bi-currency-dollar me-2 text-success"></i>Monthly Revenue</h6>
                <span class="badge bg-success-subtle text-success px-3 py-2" style="font-size:0.75rem;">
                    <i class="bi bi-cash-stack me-1"></i>{!! \App\Services\CurrencyService::formatAmount($stats['this_month_revenue']) !!}
                </span>
            </div>
            <div class="card-body">
                <div style="height: 260px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="text-dark mb-0"><i class="bi bi-wallet2 me-2 text-info"></i>Revenue by Gateway</h6>
            </div>
            <div class="card-body">
                @php
                    $gatewayData = $chartData['gateway_breakdown'] ?? [];
                    $totalGateway = collect($gatewayData)->sum('total');
                @endphp
                @forelse($gatewayData as $gw)
                @php
                    $percent = $totalGateway > 0 ? round(($gw['total'] / $totalGateway) * 100, 1) : 0;
                    $color = match(strtolower($gw['gateway'])) {
                        'Jazzcash' => 'danger',
                        'Easypaisa' => 'success',
                        'Bank Transfer' => 'primary',
                        default => 'secondary',
                    };
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-{{ $color }} bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center me-2" style="width:40px;height:40px;">
                            <i class="bi bi-wallet2 text-{{ $color }}"></i>
                        </div>
                        <div>
                            <small class="text-dark fw-semibold">{{ $gw['gateway'] }}</small><br>
                            <small class="text-secondary" style="font-size:0.7rem;">{{ $gw['count'] }} transactions</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="text-dark fw-bold small">{!! \App\Services\CurrencyService::formatAmount($gw['total']) !!}</span><br>
                        <small class="text-{{ $color }}">{{ $percent }}%</small>
                    </div>
                </div>
                <div class="progress mb-3" style="height: 4px;">
                    <div class="progress-bar bg-{{ $color }}" style="width: {{ $percent }}%"></div>
                </div>
                @empty
                <div class="text-center text-secondary py-3">
                    <i class="bi bi-wallet2 fs-2 d-block mb-2 opacity-50"></i>
                    <p class="mb-0 small">No approved transactions yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const revenueData = @json($chartData['revenue']);
    const gatewayData = @json($chartData['gateway_breakdown']);
    const currencySymbol = '{{ \App\Services\CurrencyService::getCurrencyInfo()[\App\Services\CurrencyService::getCurrentCurrency()]["symbol"] ?? "$" }}';

    const gridColor = 'rgba(0,0,0,0.06)';
    const tickColor = '#6b7280';

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: revenueData.map(d => d.month),
            datasets: [{
                label: 'Revenue (' + currencySymbol + ')',
                data: revenueData.map(d => d.revenue),
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    gradient.addColorStop(0, 'rgba(25, 135, 84, 0.7)');
                    gradient.addColorStop(1, 'rgba(25, 135, 84, 0.1)');
                    return gradient;
                },
                borderColor: '#198754',
                borderWidth: 0,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(25, 135, 84, 0.9)'
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
                            return currencySymbol + ' ' + ctx.raw.toLocaleString('en-US', {minimumFractionDigits: 2});
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: {
                        color: tickColor,
                        font: { size: 11 },
                        callback: function(val) {
                            return currencySymbol + ' ' + (val >= 1000 ? (val/1000).toFixed(0) + 'K' : val);
                        }
                    },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
