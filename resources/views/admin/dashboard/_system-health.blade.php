<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card health-card border-0">
            <div class="card-header d-flex justify-content-between align-items-center rounded-top">
                <h6 class="text-dark mb-0"><i class="bi bi-heart-pulse me-2"></i>System Health</h6>
                <span class="badge {{ $healthScore >= 80 ? 'bg-success' : ($healthScore >= 50 ? 'bg-warning text-dark' : 'bg-danger') }} px-3 py-2" style="font-size:0.8rem;">
                    <i class="bi bi-shield-check me-1"></i>Score: {{ $healthScore }}/100
                </span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-code-slash text-primary me-2"></i>
                                <small class="text-secondary">PHP Version</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ $health['php_version'] }}</h6>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-database text-info me-2"></i>
                                <small class="text-secondary">{{ ucfirst($health['db_driver']) }} Version</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ $health['db_version'] }}</h6>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-hdd text-warning me-2"></i>
                                <small class="text-secondary">Disk Usage</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ $health['disk_usage']['used_formatted'] }} / {{ $health['disk_usage']['total_formatted'] }}</h6>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar {{ $health['disk_usage']['used_percent'] > 90 ? 'bg-danger' : ($health['disk_usage']['used_percent'] > 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $health['disk_usage']['used_percent'] }}%"></div>
                            </div>
                            <small class="text-secondary" style="font-size:0.65rem;">{{ $health['disk_usage']['used_percent'] }}% used</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-memory text-success me-2"></i>
                                <small class="text-secondary">Memory Usage</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ $health['memory_usage']['used_formatted'] }} / {{ $health['memory_usage']['total_formatted'] }}</h6>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar {{ $health['memory_usage']['used_percent'] > 90 ? 'bg-danger' : ($health['memory_usage']['used_percent'] > 70 ? 'bg-warning' : 'bg-success') }}" style="width: {{ $health['memory_usage']['used_percent'] }}%"></div>
                            </div>
                            <small class="text-secondary" style="font-size:0.65rem;">{{ $health['memory_usage']['used_percent'] }}% used</small>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-database-check text-success me-2"></i>
                                <small class="text-secondary">Cache</small>
                            </div>
                            <h6 class="{{ $health['cache_status'] ? 'text-success' : 'text-danger' }} fw-semibold mb-0">
                                <i class="bi {{ $health['cache_status'] ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }} me-1"></i>
                                {{ $health['cache_status'] ? 'Operational' : 'Down' }}
                            </h6>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-list-task text-info me-2"></i>
                                <small class="text-secondary">Queue</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ $health['queue_status'] }}</h6>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-people-fill text-primary me-2"></i>
                                <small class="text-secondary">Active Sessions</small>
                            </div>
                            <h6 class="text-dark mb-0 fw-semibold">{{ number_format($health['active_sessions']) }}</h6>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="p-3 rounded border">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-tools text-secondary me-2"></i>
                                <small class="text-secondary">Maintenance</small>
                            </div>
                            <h6 class="{{ $health['maintenance_mode'] ? 'text-warning' : 'text-success' }} fw-semibold mb-0">
                                <i class="bi {{ $health['maintenance_mode'] ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' }} me-1"></i>
                                {{ $health['maintenance_mode'] ? 'Active' : 'Off' }}
                            </h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
