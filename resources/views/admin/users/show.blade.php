@extends('layouts.app')

@section('title', 'User - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-person me-2 text-primary"></i>{{ $user->name }}</h4>
        <small class="text-secondary">{{ $user->email }}</small>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('users_edit') && !$user->isSuperAdmin())
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center py-4">
                @if($user->avatar)
                <img src="{{ \Storage::disk('public')->url($user->avatar) }}" alt="{{ $user->name }}" class="rounded-circle mb-3" style="width:80px;height:80px;object-fit:cover;">
                @else
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width:80px;height:80px;">
                    <span class="text-white fw-bold" style="font-size:2rem;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                </div>
                @endif
                <h5 class=" mb-1">{{ $user->name }}</h5>
                <p class="text-secondary mb-3">{{ $user->email }}</p>

                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @php
                        $statusClass = match($user->status) {
                            'active' => 'bg-success',
                            'inactive' => 'bg-secondary',
                            'suspended' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ ucfirst($user->status) }}</span>
                    @if($user->is_banned)
                    <span class="badge bg-danger"><i class="bi bi-slash-circle me-1"></i>Banned</span>
                    @endif
                    @if($user->is_premium)
                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Premium</span>
                    @endif
                    @foreach($user->roles as $role)
                    <span class="badge bg-info">{{ $role->name }}</span>
                    @endforeach
                </div>
            </div>
            <div class="card-body border-top">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-secondary d-block">User ID</small>
                        <span class="text-dark">#{{ $user->id }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Phone</small>
                        <span class="text-dark">{{ $user->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Joined</small>
                        <span class="text-dark">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Last Login</small>
                        <span class="text-dark">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Last IP</small>
                        <span class="text-dark"><code>{{ $user->last_login_ip ?? 'N/A' }}</code></span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Premium Expires</small>
                        <span class="text-dark">{{ $user->premium_expires_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
            @if(auth()->user()->hasPermission('users_edit') && !$user->isSuperAdmin())
            <div class="card-body border-top">
                <div class="d-flex gap-2 flex-wrap">
                    @php
                        $banConfirm = $user->is_banned ? 'Unban this user?' : 'Ban this user? They will be logged out immediately.';
                        $premiumConfirm = $user->is_premium ? 'Remove premium from this user?' : 'Activate 30-day premium for this user?';
                    @endphp
                    <form method="POST" action="{{ route('admin.users.toggle-ban', $user) }}" class="d-inline" onsubmit="return confirm('{{ $banConfirm }}')">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_banned ? 'btn-outline-success' : 'btn-outline-danger' }}">
                            <i class="bi bi-{{ $user->is_banned ? 'check-lg' : 'slash-circle' }} me-1"></i>
                            {{ $user->is_banned ? 'Unban' : 'Ban' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.toggle-premium', $user) }}" class="d-inline" onsubmit="return confirm('{{ $premiumConfirm }}')">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $user->is_premium ? 'btn-outline-secondary' : 'btn-outline-warning' }}">
                            <i class="bi bi-{{ $user->is_premium ? 'star' : 'star-fill' }} me-1"></i>
                            {{ $user->is_premium ? 'Remove Premium' : 'Make Premium' }}
                        </button>
                    </form>
                    @if(auth()->user()->hasPermission('users_delete'))
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Delete this user permanently? This action cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Trading Account Details --}}
        @if($user->demo_account_id || $user->real_account_id || $user->broker_name)
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-robot me-2"></i>Trading Account Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @if($user->broker_name)
                    <div class="col-md-4">
                        <small class="text-secondary d-block">Broker</small>
                        <span class="text-dark fw-semibold">{{ $user->broker_name }}</span>
                    </div>
                    @endif
                    @if($user->demo_account_id)
                    <div class="col-md-4">
                        <small class="text-secondary d-block">Demo Account ID</small>
                        <span class="text-info fw-semibold"><i class="bi bi-pc-display me-1"></i>{{ $user->demo_account_id }}</span>
                    </div>
                    @endif
                    @if($user->demo_account_server)
                    <div class="col-md-4">
                        <small class="text-secondary d-block">Demo Server</small>
                        <span class="text-dark">{{ $user->demo_account_server }}</span>
                    </div>
                    @endif
                    @if($user->real_account_id)
                    <div class="col-md-4">
                        <small class="text-secondary d-block">Real Account ID</small>
                        <span class="text-success fw-semibold"><i class="bi bi-credit-card me-1"></i>{{ $user->real_account_id }}</span>
                    </div>
                    @endif
                    @if($user->real_account_server)
                    <div class="col-md-4">
                        <small class="text-secondary d-block">Real Server</small>
                        <span class="text-dark">{{ $user->real_account_server }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Activity Log (Last 50)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Action</th>
                                <th>Description</th>
                                <th>IP</th>
                                <th class="pe-3">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->activityLogs as $log)
                            <tr>
                                <td class="ps-3">
                                    @php
                                        $badgeClass = match(true) {
                                            str_contains($log->action, 'create') => 'bg-success',
                                            str_contains($log->action, 'update') || str_contains($log->action, 'edit') => 'bg-warning text-dark',
                                            str_contains($log->action, 'delete') => 'bg-danger',
                                            str_contains($log->action, 'login') => 'bg-info',
                                            default => 'bg-primary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($log->action) }}</span>
                                </td>
                                <td class="small text-secondary">{{ Str::limit($log->description ?? 'No description', 60) }}</td>
                                <td><code class="small text-secondary">{{ $log->ip_address ?? 'N/A' }}</code></td>
                                <td class="pe-3"><span class="small text-secondary">{{ $log->created_at->diffForHumans() }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-4">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No activity recorded for this user.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
