@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-speedometer2 me-2 text-primary"></i>Master Dashboard</h4>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <small class="text-secondary">Welcome back, <span class="fw-semibold text-dark">{{ auth()->user()->name }}</span></small>
            <small class="text-secondary">|</small>
            <small class="text-secondary"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('l, F j, Y') }}</small>
            <small class="text-secondary">|</small>
            <small class="text-secondary"><i class="bi bi-clock me-1"></i><span id="liveClock">{{ now()->format('h:i:s A') }}</span></small>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1 py-1 px-2" style="font-size:0.75rem; border-radius: 20px;">
            <span class="spinner-grow spinner-grow-sm text-success" style="width:0.45rem;height:0.45rem;"></span> Live Realtime
        </span>
        <small class="text-secondary" id="lastUpdated">Updated just now</small>
        <button id="refreshDashboardBtn" onclick="refreshDashboard(false)" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" title="Instant Live Sync">
            <i class="bi bi-arrow-clockwise me-1" id="refreshIcon"></i>Refresh
        </button>
    </div>
</div>

@include('admin.dashboard._stats')
@include('admin.dashboard._charts')
@include('admin.dashboard._revenue-section')
@include('admin.dashboard._pending-approvals')
@include('admin.dashboard._quick-actions')
@include('admin.dashboard._system-health')
@include('admin.dashboard._recent-activity')

@push('styles')
<style>
    @keyframes spinSync { 100% { transform: rotate(360deg); } }
    .spin-animation { animation: spinSync 0.75s linear infinite; display: inline-block; }
</style>
@endpush

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        const el = document.getElementById('liveClock');
        if (el) el.textContent = time;
    }
    setInterval(updateClock, 1000);

    let seconds = 0;
    const lastUpdated = document.getElementById('lastUpdated');
    const refreshBtn = document.getElementById('refreshDashboardBtn');
    const refreshIcon = document.getElementById('refreshIcon');

    async function refreshDashboard(isAuto = false) {
        if (!isAuto && refreshIcon) {
            refreshIcon.classList.add('spin-animation');
            if (refreshBtn) refreshBtn.disabled = true;
        }

        try {
            const res = await fetch('{{ route('admin.dashboard.stats') }}?fresh=1', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            const setText = (id, val) => {
                const el = document.getElementById(id);
                if (el && val !== undefined && val !== null) el.textContent = val;
            };
            const setHtml = (id, val) => {
                const el = document.getElementById(id);
                if (el && val !== undefined && val !== null) el.innerHTML = val;
            };

            const fmt = (n) => Number(n || 0).toLocaleString();

            setText('stat-total-users', fmt(data.total_users));
            setText('stat-active-users', fmt(data.active_users));
            setText('stat-premium-users', fmt(data.premium_users));
            setText('stat-mt5-users', fmt(data.mt5_bot_users));
            setText('stat-new-week', fmt(data.new_this_week));
            setText('stat-new-month', fmt(data.new_this_month));
            setText('stat-total-staff', fmt(data.total_staff));
            setText('stat-banned-users', fmt(data.banned_users));
            setText('stat-pending-tx', data.pending_transactions);

            const activePct = data.total_users > 0 ? ((data.active_users / data.total_users) * 100).toFixed(1) : 0;
            setText('stat-active-pct', `${activePct}% of total`);

            const premiumPct = data.total_users > 0 ? ((data.premium_users / data.total_users) * 100).toFixed(1) : 0;
            setText('stat-premium-pct', `${premiumPct}% conversion`);

            if (data.new_today !== undefined) {
                setHtml('stat-new-today', `<i class="bi bi-arrow-up"></i> ${data.new_today}`);
            }

            if (data.formatted_total_revenue) {
                setHtml('stat-total-revenue', data.formatted_total_revenue);
            }
            if (data.formatted_this_month_revenue) {
                setHtml('stat-month-revenue', data.formatted_this_month_revenue);
            }
            if (data.formatted_pending_amount) {
                setHtml('stat-pending-amount', data.formatted_pending_amount);
            }

            seconds = 0;
            if (lastUpdated) lastUpdated.textContent = 'Updated just now';
        } catch (err) {
            console.error('Dashboard live sync failed:', err);
        } finally {
            if (!isAuto && refreshIcon) {
                refreshIcon.classList.remove('spin-animation');
                if (refreshBtn) refreshBtn.disabled = false;
            }
        }
    }

    // Auto-poll in background every 25 seconds (No page reload needed!)
    setInterval(() => {
        refreshDashboard(true);
    }, 25000);

    // Live seconds ago counter
    setInterval(() => {
        seconds++;
        if (lastUpdated) {
            if (seconds < 60) lastUpdated.textContent = `Updated ${seconds}s ago`;
            else if (seconds < 3600) lastUpdated.textContent = `Updated ${Math.floor(seconds/60)}m ago`;
        }
    }, 1000);
</script>
@endpush
@endsection
