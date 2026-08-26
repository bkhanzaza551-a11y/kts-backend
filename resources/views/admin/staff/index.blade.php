@extends('layouts.app')

@section('title', 'Staff Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-people me-2" style="color:var(--text-secondary);"></i>Staff Management</h4>
        <small class="text-secondary">Manage your team members and their roles</small>
    </div>
    @if(auth()->user()->hasPermission('staff_create'))
    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary px-4 py-2 fw-semibold">
        <i class="bi bi-plus-lg me-1"></i>Add Staff
    </a>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Total Staff</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ $staff->total() }}</h4>
                        <small class="text-secondary" style="font-size:0.75rem;">All team members</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Active</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ $staff->where('status', 'active')->count() }}</h4>
                        <small class="text-secondary" style="font-size:0.75rem;">Currently active</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Inactive</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ $staff->where('status', 'inactive')->count() }}</h4>
                        <small class="text-secondary" style="font-size:0.75rem;">Need activation</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-secondary mb-1 small fw-medium">Suspended</p>
                        <h4 class="text-dark mb-1 fw-bold">{{ $staff->where('status', 'suspended')->count() }}</h4>
                        <small class="text-secondary" style="font-size:0.75rem;">Blocked accounts</small>
                    </div>
                    <div class="stat-icon">
                        <i class="bi bi-shield-x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-lg-5 col-md-6">
                <label class="form-label small text-secondary fw-medium">Search</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-lg-3 col-md-4">
                <label class="form-label small text-secondary fw-medium">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search me-1"></i>Search
                </button>
                @if(request('search') || request('status'))
                <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary" title="Clear">
                    <i class="bi bi-x-lg"></i>
                </a>
                @endif
            </div>
            <div class="col-lg-2 col-md-12 text-lg-end">
                <small class="text-secondary">{{ $staff->total() }} results</small>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Staff Members</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width:50px;">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                    <tr>
                        <td class="ps-3"><code class="text-secondary">{{ $member->id }}</code></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:36px;height:36px;background:var(--primary);">
                                    <span class="text-white fw-bold" style="font-size:0.85rem;">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <span class="fw-semibold text-dark">{{ $member->name }}</span>
                                    @if($member->isSuperAdmin())
                                    <br><small class="text-warning" style="font-size:0.65rem;"><i class="bi bi-star-fill me-1"></i>Super Admin</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><span class="text-secondary">{{ $member->email }}</span></td>
                        <td>
                            @foreach($member->roles as $role)
                            <span class="badge bg-secondary">
                                <i class="bi bi-shield-lock me-1"></i>{{ $role->name }}
                            </span>
                            @endforeach
                        </td>
                        <td>
                            @if($member->status === 'active')
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle-fill me-1"></i>Active
                            </span>
                            @elseif($member->status === 'inactive')
                            <span class="badge bg-secondary">
                                <i class="bi bi-pause-circle-fill me-1"></i>Inactive
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle-fill me-1"></i>Suspended
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($member->last_login_at)
                            <span class="text-secondary small">{{ $member->last_login_at->diffForHumans() }}</span>
                            @else
                            <span class="text-muted small fst-italic">Never logged in</span>
                            @endif
                        </td>
                        <td class="pe-3 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @if(auth()->user()->hasPermission('staff_edit'))
                                <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('staff_delete') && !$member->isSuperAdmin())
                                <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary text-danger" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">
                            <i class="bi bi-people fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">No staff members found.</p>
                            <small class="text-muted">Try adjusting your search or filters.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($staff->hasPages())
    <div class="card-footer d-flex justify-content-center bg-white border-top-0 pt-0">
        {{ $staff->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
