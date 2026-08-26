@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-bell me-2 text-primary"></i>Notifications</h4>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('notifications_send'))
        <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Send Notification</a>
        @endif
        <a href="{{ route('admin.notifications.templates') }}" class="btn btn-outline-info btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Templates</a>
        <a href="{{ route('admin.notifications.tips') }}" class="btn btn-outline-success btn-sm"><i class="bi bi-lightbulb me-1"></i>AI Tips</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total_sent']) }}</h3><small class="text-secondary">Total Sent</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['today_sent']) }}</h3><small class="text-secondary">Today Sent</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-warning mb-0 fw-bold">{{ $stats['pending'] }}</h3><small class="text-secondary">Pending</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-secondary mb-0 fw-bold">{{ $stats['templates'] }}</h3><small class="text-secondary">Templates</small>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Title</th><th>Type</th><th>Target</th><th>Recipients</th><th>Sent By</th><th>Date</th></tr></thead>
                <tbody>
                    @forelse($notifications as $n)
                    <tr>
                        <td class="ps-3"><span class="fw-semibold text-dark">{{ $n->title }}</span><div class="text-secondary small">{{ Str::limit($n->body, 40) }}</div></td>
                        <td><span class="badge bg-{{ $n->type === 'danger' ? 'danger' : ($n->type === 'warning' ? 'warning' : ($n->type === 'success' ? 'success' : 'info')) }}">{{ ucfirst($n->type) }}</span></td>
                        <td><span class="badge bg-secondary">{{ ucfirst($n->target) }}</span></td>
                        <td class="text-dark">{{ number_format($n->sent_count) }}</td>
                        <td class="text-secondary">{{ $n->sender->name ?? '-' }}</td>
                        <td class="text-secondary">{{ $n->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4"><i class="bi bi-bell fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No notifications sent yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($notifications->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $notifications->firstItem() }}-{{ $notifications->lastItem() }} of {{ $notifications->total() }}</small>
    {{ $notifications->links() }}
</div>
@endif
@endsection
