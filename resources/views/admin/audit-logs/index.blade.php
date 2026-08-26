@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-journal-text me-2 text-primary"></i>Audit Log Viewer</h4>
        <small class="text-secondary">Browse all system activity logs</small>
    </div>
    <div class="d-flex gap-2">
        <button onclick="exportLogs()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-download me-1"></i>Export CSV
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">Search</label>
                    <input type="text" name="search" class="form-control"
                        value="{{ request('search') }}" placeholder="Description, IP...">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">Model</label>
                    <select name="model" class="form-select">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ class_basename($model) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">Date From</label>
                    <input type="date" name="date_from" class="form-control"
                        value="{{ request('date_from') }}">
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small text-secondary">Date To</label>
                    <input type="date" name="date_to" class="form-control"
                        value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i>Apply Filters
                </button>
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>Activity Logs
            <span class="badge bg-primary ms-2">{{ $logs->total() }}</span>
        </h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:50px;">#</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Model</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th class="pe-3">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-3"><code class="text-secondary">{{ $log->id }}</code></td>
                        <td>
                            <div class="d-flex align-items-center">
                                @php
                                    $userName = $log->user?->name ?? 'System';
                                    $avatarChar = strtoupper(substr($userName, 0, 1)) ?: 'S';
                                    $avatarColor = $log->user ? 'bg-primary' : 'bg-secondary';
                                @endphp
                                <div class="{{ $avatarColor }} rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:28px;height:28px;">
                                    <span class="text-white small fw-bold">{{ $avatarChar }}</span>
                                </div>
                                <span class="small text-truncate" style="max-width:100px;">{{ $userName }}</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    str_contains($log->action, 'create') => 'bg-success',
                                    str_contains($log->action, 'update') || str_contains($log->action, 'edit') => 'bg-warning text-dark',
                                    str_contains($log->action, 'delete') => 'bg-danger',
                                    str_contains($log->action, 'login') => 'bg-info',
                                    str_contains($log->action, 'logout') => 'bg-secondary',
                                    default => 'bg-primary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($log->action) }}</span>
                        </td>
                        <td>
                            @if($log->model)
                            <span class="badge bg-secondary">
                                {{ class_basename($log->model) }}
                                @if($log->model_id)
                                <span class="text-secondary ms-1">#{{ $log->model_id }}</span>
                                @endif
                            </span>
                            @else
                            <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="small text-secondary" style="max-width:250px;">
                            {{ Str::limit($log->description ?? 'No description', 60) }}
                        </td>
                        <td><code class="small text-secondary">{{ $log->ip_address ?? 'N/A' }}</code></td>
                        <td class="pe-3">
                            <span class="small text-secondary" title="{{ $log->created_at }}">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">No audit logs found.</p>
                            <small class="text-muted">Try adjusting your filters.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function exportLogs() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', '1');
    window.location.href = '{{ route("admin.audit-logs.index") }}?' + params.toString();
}
</script>
@endpush
