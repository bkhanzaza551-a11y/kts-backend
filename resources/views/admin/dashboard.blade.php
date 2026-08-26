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
    <div class="d-flex gap-2 align-items-center">
        <small class="text-secondary" id="lastUpdated"></small>
        <button onclick="location.reload()" class="btn btn-outline-secondary btn-sm" title="Refresh Dashboard">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
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

@push('scripts')
<script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
        const el = document.getElementById('liveClock');
        if (el) el.textContent = time;
    }
    setInterval(updateClock, 1000);

    const lastUpdated = document.getElementById('lastUpdated');
    if (lastUpdated) {
        lastUpdated.textContent = 'Updated just now';
        let seconds = 0;
        setInterval(() => {
            seconds++;
            if (seconds < 60) lastUpdated.textContent = `Updated ${seconds}s ago`;
            else if (seconds < 3600) lastUpdated.textContent = `Updated ${Math.floor(seconds/60)}m ago`;
        }, 1000);
    }
</script>
@endpush
@endsection
