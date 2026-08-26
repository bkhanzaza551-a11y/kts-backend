<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="text-dark mb-0"><i class="bi bi-clock-history me-2"></i>Recent Activity</h6>
        <span class="badge bg-secondary">Last 30</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:400px; overflow-y:auto;">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th class="pe-3">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentActivity as $log)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                @php
                                    $userName = $log->user?->name ?? 'System';
                                    $avatarChar = strtoupper(substr($userName, 0, 1)) ?: 'S';
                                    $avatarColor = $log->user ? 'bg-primary' : 'bg-secondary';
                                @endphp
                                <div class="{{ $avatarColor }} rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:30px;height:30px;">
                                    <span class="text-white small fw-bold">{{ $avatarChar }}</span>
                                </div>
                                <span class="small text-truncate" style="max-width:120px;">{{ $userName }}</span>
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
                        <td class="small text-secondary" style="max-width:200px;">
                            {{ Str::limit($log->description ?? 'No description', 50) }}
                        </td>
                        <td><code class="small text-secondary">{{ $log->ip_address ?? 'N/A' }}</code></td>
                        <td class="pe-3"><span class="small text-secondary">{{ $log->created_at->diffForHumans() }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">No activity recorded yet.</p>
                            <small class="text-muted">Activity will appear here as staff members use the system.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
