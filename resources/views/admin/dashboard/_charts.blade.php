<div class="row g-3 mb-4">
    <div class="col-xl-8 col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="text-dark mb-0"><i class="bi bi-graph-up me-2"></i>User Growth (30 Days)</h6>
                <span class="badge bg-primary-subtle text-primary px-3 py-2" style="font-size:0.75rem;">
                    <i class="bi bi-plus-circle me-1"></i>{{ number_format($stats['new_this_month']) }} new this month
                </span>
            </div>
            <div class="card-body">
                <div style="height: 280px;">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-5">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="text-dark mb-0"><i class="bi bi-pie-chart me-2"></i>User Status</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="text-dark mb-0"><i class="bi bi-bar-chart me-2"></i>Monthly Registrations</h6>
            </div>
            <div class="card-body">
                <div style="height: 200px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const growthData = @json($chartData['user_growth']);
    const statusData = @json($chartData['status_distribution']);
    const monthlyData = @json($chartData['monthly_registrations']);

    const gridColor = 'rgba(0,0,0,0.06)';
    const tickColor = '#6b7280';

    new Chart(document.getElementById('userGrowthChart'), {
        type: 'line',
        data: {
            labels: growthData.map(d => d.date),
            datasets: [{
                label: 'New Users',
                data: growthData.map(d => d.count),
                borderColor: '#0d6efd',
                backgroundColor: (ctx) => {
                    const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, ctx.chart.height);
                    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.2)');
                    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.01)');
                    return gradient;
                },
                fill: true,
                tension: 0.4,
                pointRadius: 0,
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
                    titleColor: '#fff',
                    bodyColor: '#adb5bd',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, maxTicksLimit: 10, font: { size: 11 } } },
                y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Suspended'],
            datasets: [{
                data: [statusData.active, statusData.inactive, statusData.suspended],
                backgroundColor: ['#198754', '#6c757d', '#dc3545'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { color: '#6b7280', padding: 16, usePointStyle: true, pointStyleWidth: 10 } },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 10,
                    cornerRadius: 8,
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthlyData.map(d => d.month),
            datasets: [{
                label: 'Registrations',
                data: monthlyData.map(d => d.count),
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: '#0d6efd',
                borderWidth: 0,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(13, 110, 253, 0.8)'
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
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickColor, font: { size: 11 } } },
                y: { grid: { color: gridColor, drawBorder: false }, ticks: { color: tickColor, font: { size: 11 } }, beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
