@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-shield-check me-2 text-primary"></i>Audit Log Viewer</h4>
        <small class="text-secondary">Track, search, and audit real-time system activities and administrative events</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold" title="Refresh logs">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark">
            <i class="bi bi-funnel text-primary me-2"></i>Filter Activity Logs
        </h6>
        @php
            $hasActiveFilters = filled(request('search')) ||
                                filled(request('user_id')) ||
                                filled(request('action')) ||
                                filled(request('model')) ||
                                filled(request('date_from')) ||
                                filled(request('date_to'));
        @endphp
        @if($hasActiveFilters)
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.72rem;">
            <i class="bi bi-check2-circle me-1"></i>Filters Active
        </span>
        @endif
    </div>
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-xl-3 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Search Keyword</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control"
                            value="{{ request('search') }}" placeholder="Description, IP, action...">
                    </div>
                </div>
                <div class="col-xl-2 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">User / Actor</label>
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
                    <label class="form-label small fw-semibold text-secondary">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $action)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Target Model</label>
                    <select name="model" class="form-select">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                        <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>
                            {{ class_basename($model) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-3 col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Date Range</label>
                    <div class="input-group">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="From date">
                        <span class="input-group-text bg-light text-muted px-2">to</span>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="To date">
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3 align-items-center justify-content-end flex-wrap">
                @if($hasActiveFilters)
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline-secondary btn-sm px-3" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i>Reset Filters
                </a>
                @endif
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Audit Logs Table Card --}}
<div class="card border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-dark">
                <i class="bi bi-list-columns-reverse text-primary me-2"></i>Activity Log Entries
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" style="font-size: 0.75rem;">
                {{ number_format($logs->total()) }} total
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width:60px;">#</th>
                        <th>User / Actor</th>
                        <th>Action</th>
                        <th>Target Model</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                        <th class="text-end pe-3" style="width:90px;">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.72rem;">
                                #{{ $log->id }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $userName = $log->user?->name ?? 'System';
                                    $avatarChar = strtoupper(substr($userName, 0, 1)) ?: 'S';
                                    $avatarBg = $log->user ? 'bg-primary' : 'bg-secondary';
                                @endphp
                                <div class="{{ $avatarBg }} text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width:30px;height:30px;font-size:0.75rem;">
                                    {{ $avatarChar }}
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-semibold text-dark small text-truncate" style="max-width:140px;">{{ $userName }}</div>
                                    @if($log->user)
                                        <small class="text-muted d-block text-truncate" style="max-width:140px;font-size:0.7rem;">{{ $log->user->email }}</small>
                                    @else
                                        <small class="text-muted d-block" style="font-size:0.7rem;">Automated Cron</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $actionLower = strtolower($log->action ?? '');
                                $badgeClass = match(true) {
                                    str_contains($actionLower, 'create') || str_contains($actionLower, 'store') => 'bg-success text-white',
                                    str_contains($actionLower, 'update') || str_contains($actionLower, 'edit') => 'bg-warning text-dark',
                                    str_contains($actionLower, 'delete') || str_contains($actionLower, 'destroy') || str_contains($actionLower, 'ban') => 'bg-danger text-white',
                                    str_contains($actionLower, 'login') => 'bg-info text-white',
                                    str_contains($actionLower, 'logout') => 'bg-secondary text-white',
                                    str_contains($actionLower, 'otp') => 'bg-primary text-white',
                                    str_contains($actionLower, 'trade') || str_contains($actionLower, 'signal') => 'bg-dark text-white',
                                    default => 'bg-primary-subtle text-primary border border-primary-subtle',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1" style="font-size: 0.72rem; letter-spacing: 0.3px;">
                                {{ strtoupper($log->action ?? 'EVENT') }}
                            </span>
                        </td>
                        <td>
                            @if($log->model)
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.72rem;">
                                <i class="bi bi-box-seam me-1 text-primary"></i>{{ class_basename($log->model) }}
                                @if($log->model_id)
                                <strong class="text-primary ms-1">#{{ $log->model_id }}</strong>
                                @endif
                            </span>
                            @else
                            <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td class="small text-secondary" style="max-width:280px;">
                            <span class="d-inline-block text-truncate w-100" title="{{ $log->description }}">
                                {{ $log->description ?? 'No description' }}
                            </span>
                        </td>
                        <td>
                            <code class="small text-secondary px-2 py-1 rounded bg-light border" style="font-size: 0.74rem;">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </code>
                        </td>
                        <td class="small text-secondary" style="white-space:nowrap;">
                            <div title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                <i class="bi bi-clock me-1 text-muted"></i>{{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="border-radius: 6px;"
                                onclick="viewLogModal({{ json_encode([
                                    'id' => $log->id,
                                    'user' => $log->user?->name ?? 'System',
                                    'action' => $log->action,
                                    'model' => $log->model ? class_basename($log->model) . ($log->model_id ? ' #' . $log->model_id : '') : 'N/A',
                                    'description' => $log->description,
                                    'ip' => $log->ip_address ?? 'N/A',
                                    'user_agent' => $log->user_agent ?? 'N/A',
                                    'old_values' => $log->old_values,
                                    'new_values' => $log->new_values,
                                    'created_at' => $log->created_at->format('M d, Y H:i:s \U\T\C'),
                                ]) }})" title="View Log Details">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-secondary py-5">
                            <i class="bi bi-journal-x fs-1 d-block mb-2 text-muted opacity-50"></i>
                            <h6 class="fw-bold text-dark mb-1">No Audit Logs Found</h6>
                            <p class="text-muted small mb-0">Try adjusting your search criteria or resetting filters.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white border-top py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
        <div class="small text-secondary">
            Showing <span class="fw-bold text-dark">{{ $logs->firstItem() ?? 0 }}</span> to <span class="fw-bold text-dark">{{ $logs->lastItem() ?? 0 }}</span> of <span class="fw-bold text-dark">{{ $logs->total() }}</span> logs
        </div>
        <div class="m-0">
            {{ $logs->links() }}
        </div>
    </div>
    @endif
</div>

{{-- Detail Inspection Modal --}}
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="logDetailModalLabel">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i>Audit Log Details <span id="modalLogId" class="badge bg-light text-secondary border ms-1"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-4">
                        <small class="text-secondary d-block fw-semibold">Actor / User</small>
                        <span id="modalUser" class="fw-bold text-dark"></span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <small class="text-secondary d-block fw-semibold">Action</small>
                        <span id="modalAction" class="badge bg-primary"></span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <small class="text-secondary d-block fw-semibold">Target Entity</small>
                        <span id="modalModel" class="badge bg-light text-dark border"></span>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <small class="text-secondary d-block fw-semibold">IP Address</small>
                        <code id="modalIp" class="text-dark"></code>
                    </div>
                    <div class="col-sm-6 col-md-8">
                        <small class="text-secondary d-block fw-semibold">Timestamp</small>
                        <span id="modalTimestamp" class="text-dark"></span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Description</label>
                    <div id="modalDescription" class="p-3 bg-light rounded-3 text-dark small border"></div>
                </div>

                <div id="modalDiffSection" class="d-none">
                    <label class="form-label small fw-semibold text-secondary">Changes / Payload</label>
                    <div class="row g-2">
                        <div class="col-md-6" id="oldValuesCol">
                            <div class="p-2 bg-light border rounded-3">
                                <small class="text-danger fw-bold d-block mb-1"><i class="bi bi-dash-circle me-1"></i>Old Values</small>
                                <pre id="modalOldValues" class="mb-0 small text-dark p-2 bg-white rounded border" style="max-height: 200px; overflow-y: auto; font-size: 0.75rem;"></pre>
                            </div>
                        </div>
                        <div class="col-md-6" id="newValuesCol">
                            <div class="p-2 bg-light border rounded-3">
                                <small class="text-success fw-bold d-block mb-1"><i class="bi bi-plus-circle me-1"></i>New Values</small>
                                <pre id="modalNewValues" class="mb-0 small text-dark p-2 bg-white rounded border" style="max-height: 200px; overflow-y: auto; font-size: 0.75rem;"></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <small class="text-secondary d-block fw-semibold">User Agent / Client</small>
                    <div id="modalUserAgent" class="p-2 bg-light rounded text-muted font-monospace border" style="font-size: 0.72rem; word-break: break-all;"></div>
                </div>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewLogModal(log) {
    document.getElementById('modalLogId').textContent = '#' + log.id;
    document.getElementById('modalUser').textContent = log.user;
    document.getElementById('modalAction').textContent = (log.action || '').toUpperCase();
    document.getElementById('modalModel').textContent = log.model;
    document.getElementById('modalIp').textContent = log.ip;
    document.getElementById('modalTimestamp').textContent = log.created_at;
    document.getElementById('modalDescription').textContent = log.description || 'No description provided.';
    document.getElementById('modalUserAgent').textContent = log.user_agent || 'Unknown Client';

    const diffSection = document.getElementById('modalDiffSection');
    const hasOld = log.old_values && Object.keys(log.old_values).length > 0;
    const hasNew = log.new_values && Object.keys(log.new_values).length > 0;

    if (hasOld || hasNew) {
        diffSection.classList.remove('d-none');
        document.getElementById('modalOldValues').textContent = hasOld ? JSON.stringify(log.old_values, null, 2) : 'None';
        document.getElementById('modalNewValues').textContent = hasNew ? JSON.stringify(log.new_values, null, 2) : 'None';
    } else {
        diffSection.classList.add('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('logDetailModal'));
    modal.show();
}
</script>
@endpush
@endsection
