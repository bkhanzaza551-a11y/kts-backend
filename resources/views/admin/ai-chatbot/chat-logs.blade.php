@extends('layouts.app')
@section('title', 'AI Chat Logs')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-chat-left-text me-2 text-primary"></i>AI Chat Logs</h4>
    <a href="{{ route('admin.ai-chatbot.settings') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Settings</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-white mb-0 fw-bold">{{ number_format($stats['total_conversations']) }}</h3><small class="text-secondary">User Queries</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['total_messages']) }}</h3><small class="text-secondary">Total Messages</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">{{ number_format($stats['total_tokens']) }}</h3><small class="text-secondary">Tokens Used</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ $stats['avg_response_time'] ? number_format($stats['avg_response_time'], 0) . 'ms' : '-' }}</h3><small class="text-secondary">Avg Response</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-warning mb-0 fw-bold">{{ $stats['flagged'] }}</h3><small class="text-secondary">Flagged</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-secondary mb-0 fw-bold">{{ number_format($stats['today_conversations']) }}</h3><small class="text-secondary">Today</small>
        </div></div>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end" id="aiChatFilterForm">
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Message or user..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="assistant" {{ request('role') === 'assistant' ? 'selected' : '' }}>Assistant</option>
                    <option value="system" {{ request('role') === 'system' ? 'selected' : '' }}>System</option>
                </select>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Flagged</label>
                <select name="flagged" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Flagged Only</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Created Between</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                    <span class="input-group-text bg-light text-muted">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>
            </div>
            <div class="col-auto d-flex gap-2 ms-auto pt-2 pt-md-0">
                <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-funnel me-1"></i>Filter</button>
                @php
                    $hasActiveFilters = filled(request('search')) ||
                                        filled(request('role')) ||
                                        filled(request('flagged')) ||
                                        filled(request('date_from')) ||
                                        filled(request('date_to'));
                @endphp
                @if($hasActiveFilters)
                <a href="{{ route('admin.ai-chatbot.chat-logs') }}" class="btn btn-sm btn-outline-secondary px-3" title="Clear all filters"><i class="bi bi-x-circle me-1"></i>Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">User</th><th>Role</th><th>Message</th><th>Model</th><th>Tokens</th><th>Time</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="{{ $log->is_flagged ? 'table-warning' : '' }}">
                        <td class="ps-3"><span class="fw-semibold text-dark">{{ $log->user->name ?? 'Deleted' }}</span></td>
                        <td><span class="badge bg-{{ $log->role === 'user' ? 'primary' : ($log->role === 'assistant' ? 'success' : 'secondary') }}">{{ ucfirst($log->role) }}</span></td>
                        <td style="max-width:300px;">{{ Str::limit($log->message, 80) }}</td>
                        <td class="text-secondary">{{ $log->model_used ?? '-' }}</td>
                        <td class="text-secondary">{{ $log->tokens_used }}</td>
                        <td class="text-secondary">{{ $log->created_at->diffForHumans() }}</td>
                        <td class="pe-3">
                            <form method="POST" action="{{ route('admin.ai-chatbot.toggle-flag', $log) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $log->is_flagged ? 'btn-warning' : 'btn-outline-warning' }}"><i class="bi bi-flag"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4"><i class="bi bi-chat-left-text fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No chat logs.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($logs->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $logs->firstItem() }}-{{ $logs->lastItem() }} of {{ $logs->total() }}</small>
    {{ $logs->links() }}
</div>
@endif
@endsection
