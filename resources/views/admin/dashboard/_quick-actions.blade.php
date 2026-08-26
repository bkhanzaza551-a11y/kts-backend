<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="text-dark mb-0"><i class="bi bi-lightning-fill text-warning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if(auth()->user()->hasPermission('staff_create'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <a href="{{ route('admin.staff.create') }}" class="btn btn-outline-primary w-100 py-3 btn-quick-action">
                            <i class="bi bi-person-plus fs-4 d-block mb-1"></i>
                            <small class="fw-medium">Add Staff</small>
                        </a>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('roles_create'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-success w-100 py-3 btn-quick-action">
                            <i class="bi bi-shield-plus fs-4 d-block mb-1"></i>
                            <small class="fw-medium">Create Role</small>
                        </a>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('staff_view'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-info w-100 py-3 btn-quick-action">
                            <i class="bi bi-people fs-4 d-block mb-1"></i>
                            <small class="fw-medium">View Staff</small>
                        </a>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('permissions_view'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-primary w-100 py-3 btn-quick-action">
                            <i class="bi bi-key fs-4 d-block mb-1"></i>
                            <small class="fw-medium">Permissions</small>
                        </a>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('settings_view'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <button class="btn btn-outline-warning w-100 py-3 btn-quick-action" disabled title="Coming soon - Module 11">
                            <i class="bi bi-gear fs-4 d-block mb-1"></i>
                            <small class="fw-medium">Settings</small>
                            <span class="badge bg-secondary mt-1" style="font-size:0.55rem;">SOON</span>
                        </button>
                    </div>
                    @endif

                    @if(auth()->user()->hasPermission('backup_create'))
                    <div class="col-xl-2 col-md-4 col-6">
                        <button class="btn btn-outline-secondary w-100 py-3 btn-quick-action" disabled title="Coming soon - Module 11">
                            <i class="bi bi-download fs-4 d-block mb-1"></i>
                            <small class="fw-medium">Backup</small>
                            <span class="badge bg-secondary mt-1" style="font-size:0.55rem;">SOON</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
