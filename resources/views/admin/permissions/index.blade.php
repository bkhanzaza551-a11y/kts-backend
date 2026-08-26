@extends('layouts.app')

@section('title', 'Permissions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-key me-2"></i>System Permissions</h4>
</div>

<div class="card">
    <div class="card-body">
        @foreach($grouped as $module => $modulePermissions)
        <div class="mb-4">
            <h5 class="text-info text-uppercase">{{ $module }}</h5>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Permission</th>
                            <th>Slug</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($modulePermissions as $permission)
                        <tr>
                            <td><span class="badge bg-secondary">{{ ucfirst($permission->action) }}</span></td>
                            <td><code>{{ $permission->slug }}</code></td>
                            <td class="text-secondary">{{ $permission->description ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
