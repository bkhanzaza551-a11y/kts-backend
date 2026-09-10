@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-people me-2" style="color:var(--text-secondary);"></i>User Management</h4>
    @if(auth()->user()->hasPermission('users_create'))
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add User
    </a>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                <small class="text-secondary">Total Users</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['active']) }}</h4>
                <small class="text-secondary">Active</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['inactive']) }}</h4>
                <small class="text-secondary">Inactive</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['suspended']) }}</h4>
                <small class="text-secondary">Suspended</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['banned']) }}</h4>
                <small class="text-secondary">Banned</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h4 class="text-dark mb-0 fw-bold">{{ number_format($stats['premium']) }}</h4>
                <small class="text-secondary">Premium</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end" id="userFilterForm">
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, email, phone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->slug }}" {{ request('role') === $r->slug ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-xl-1 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Banned</label>
                <select name="is_banned" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('is_banned') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('is_banned') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-xl-1 col-lg-2 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Premium</label>
                <select name="is_premium" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('is_premium') === '1' ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ request('is_premium') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Joined Between</label>
                <div class="input-group input-group-sm">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                    <span class="input-group-text bg-light text-muted">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>
            </div>
            <div class="col-auto d-flex gap-2 ms-auto pt-2 pt-md-0">
                <button type="submit" class="btn btn-sm btn-primary px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @php
                    $hasActiveFilters = filled(request('search')) ||
                                        filled(request('status')) ||
                                        filled(request('role')) ||
                                        (request('is_banned') !== null && request('is_banned') !== '') ||
                                        (request('is_premium') !== null && request('is_premium') !== '') ||
                                        filled(request('date_from')) ||
                                        filled(request('date_to'));
                @endphp
                @if($hasActiveFilters)
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary px-3" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

@php
    $currentSort = request('sort', 'created_at');
    $currentDir = request('dir', 'desc');
    $sortLink = function ($column) use ($currentSort, $currentDir) {
        $newDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
        return route('admin.users.index', array_merge(request()->query(), ['sort' => $column, 'dir' => $newDir]));
    };
@endphp

<form method="POST" action="{{ route('admin.users.bulk-action') }}" id="bulkForm">
    @csrf
    <input type="hidden" name="action" id="bulkAction" value="">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" class="form-check-input" id="selectAll" title="Select all">
                <span class="text-secondary small" id="selectedCount">0 selected</span>
            </div>
            @if(auth()->user()->hasAnyPermission(['users_edit', 'users_delete']))
            <div class="d-flex gap-2" id="bulkActions" style="display:none !important;">
                @if(auth()->user()->hasPermission('users_edit'))
                <button type="submit" class="btn btn-sm btn-outline-success" onclick="document.getElementById('bulkAction').value='activate'">
                    <i class="bi bi-check-lg me-1"></i>Activate
                </button>
                <button type="submit" class="btn btn-sm btn-outline-warning" onclick="document.getElementById('bulkAction').value='suspend'">
                    <i class="bi bi-pause-lg me-1"></i>Suspend
                </button>
                @endif
                @if(auth()->user()->hasPermission('users_delete'))
                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('bulkAction').value='delete'; return confirm('Delete selected users?')">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                @endif
                <button type="submit" class="btn btn-sm btn-outline-info" onclick="document.getElementById('bulkAction').value='export'">
                    <i class="bi bi-download me-1"></i>Export CSV
                </button>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th>
                                <a href="{{ $sortLink('id') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                    ID
                                    @if($currentSort === 'id')
                                    <i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                    @else
                                    <i class="bi bi-arrow-down-up small opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortLink('name') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                    User
                                    @if($currentSort === 'name')
                                    <i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                    @else
                                    <i class="bi bi-arrow-down-up small opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Premium</th>
                            <th>
                                <a href="{{ $sortLink('created_at') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                    Joined
                                    @if($currentSort === 'created_at')
                                    <i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                    @else
                                    <i class="bi bi-arrow-down-up small opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="{{ $sortLink('last_login_at') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                    Last Login
                                    @if($currentSort === 'last_login_at')
                                    <i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                    @else
                                    <i class="bi bi-arrow-down-up small opacity-50"></i>
                                    @endif
                                </a>
                            </th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                @if(!$user->isSuperAdmin())
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="form-check-input user-checkbox">
                                @endif
                            </td>
                            <td><span class="text-secondary">{{ $user->id }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0 {{ $user->is_banned ? 'bg-danger' : '' }}" style="width:34px;height:34px;{{ $user->is_banned ? '' : 'background:var(--primary);' }}">
                                        <span class="text-white small fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-dark text-decoration-none fw-semibold">{{ $user->name }}</a>
                                        <div class="text-secondary" style="font-size:0.7rem;">{{ $user->email }}</div>
                                    </div>
                                    @if($user->is_banned)
                                    <span class="badge bg-danger ms-2" style="font-size:0.6rem;">BANNED</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $user->phone ?? '-' }}</td>
                            <td>
                                @php
                                    $statusClass = match($user->status) {
                                        'active' => 'bg-success',
                                        'inactive' => 'bg-secondary',
                                        'suspended' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                            </td>
                            <td>
                                @if($user->is_premium)
                                    @if($user->premium_expires_at && $user->premium_expires_at->isFuture())
                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Active</span>
                                    @else
                                    <span class="badge bg-secondary">Expired</span>
                                    @endif
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                <span title="{{ $user->created_at->format('M d, Y H:i:s') }}" class="text-dark">
                                    {{ $user->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td class="text-secondary small">
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at->format('M d, Y H:i:s') }}" class="text-dark fw-semibold">
                                        <i class="bi bi-circle-fill text-success me-1" style="font-size:0.45rem;"></i>{{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted fst-italic">Never</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Profile">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if(auth()->user()->hasPermission('users_edit'))
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary py-1 px-2" title="Edit User">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-secondary py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                <p class="mb-0">No users found.</p>
                                @if(request()->hasAny(['search', 'status', 'is_banned', 'is_premium']))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Clear Filters</a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

@if($users->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }} users</small>
    {{ $users->withQueryString()->links() }}
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const countEl = document.getElementById('selectedCount');
    const bulkActions = document.getElementById('bulkActions');

    function updateBulk() {
        const checked = document.querySelectorAll('.user-checkbox:checked').length;
        countEl.textContent = checked + ' selected';
        bulkActions.style.display = checked > 0 ? 'flex' : 'none';
    }

    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulk();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulk));
});
</script>
@endpush
@endsection
