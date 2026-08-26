@extends('layouts.app')

@section('title', 'Roles & Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2" style="color:var(--text-secondary);"></i>Roles & Permissions</h4>
    @if(auth()->user()->hasPermission('roles_create'))
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Add Role
    </a>
    @endif
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Role Name</th>
                        <th>Slug</th>
                        <th>Permissions</th>
                        <th>Users</th>
                        <th>System</th>
                        <th class="pe-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td class="ps-3"><span class="text-secondary">{{ $role->id }}</span></td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $role->name }}</span>
                            @if($role->description)
                            <br><small class="text-secondary">{{ Str::limit($role->description, 50) }}</small>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary font-monospace">{{ $role->slug }}</span></td>
                        <td><span class="badge bg-secondary">{{ $role->permissions_count }} permissions</span></td>
                        <td><span class="badge bg-secondary">{{ $role->users_count ?? 0 }} users</span></td>
                        <td>
                            @if($role->is_system)
                            <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i>System</span>
                            @else
                            <span class="badge bg-secondary bg-opacity-50 text-secondary border">Custom</span>
                            @endif
                        </td>
                        <td class="pe-3 text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                @if(auth()->user()->hasPermission('roles_edit'))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                @if(auth()->user()->hasPermission('roles_delete') && !$role->is_system)
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-secondary text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No roles found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $roles->links() }}
</div>
@endsection
