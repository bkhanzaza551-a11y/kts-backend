@extends('layouts.app')
@section('title', 'Banned Users')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-person-x me-2 text-danger"></i>Banned Users</h4>
    <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">User</th><th>Banned By</th><th>Reason</th><th>Expires</th><th>Status</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($bans as $ban)
                    <tr>
                        <td class="ps-3"><span class="fw-semibold text-dark">{{ $ban->user->name ?? 'Deleted' }}</span><div class="text-secondary small">{{ $ban->user->email ?? '' }}</div></td>
                        <td class="text-secondary">{{ $ban->banner->name ?? 'Deleted' }}</td>
                        <td style="max-width:200px;" class="text-secondary">{{ Str::limit($ban->reason ?: '-', 50) }}</td>
                        <td class="text-secondary">{{ $ban->expires_at ? $ban->expires_at->format('M d, Y') : 'Permanent' }}</td>
                        <td><span class="badge bg-{{ $ban->isCurrentlyBanned() ? 'danger' : 'success' }}">{{ $ban->isCurrentlyBanned() ? 'Active' : 'Expired' }}</span></td>
                        <td class="pe-3">
                            <form method="POST" action="{{ route('admin.chat.unban-user', $ban) }}" class="d-inline" onsubmit="return confirm('Unban this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-success"><i class="bi bi-person-check me-1"></i>Unban</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">No banned users.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($bans->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $bans->firstItem() }}-{{ $bans->lastItem() }} of {{ $bans->total() }}</small>
    {{ $bans->links() }}
</div>
@endif
@endsection
