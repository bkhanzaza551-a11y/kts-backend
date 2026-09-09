@extends('layouts.app')
@section('title', 'Chat Badges')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-award me-2 text-warning"></i>Chat Badges</h4>
    <a href="{{ route('admin.chat.rooms') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="bi bi-award me-2"></i>Assign Trader Badge & Verification</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chat.update-badge') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label text-secondary">User <span class="text-danger">*</span></label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Select User --</option>
                    @foreach($allUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) {{ $u->is_verified ? '✓' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary">Badge Title</label>
                <input type="text" name="chat_badge" class="form-control" placeholder="e.g. KTS TRADER, VIP, PRO" maxlength="50">
            </div>
            <div class="col-md-2">
                <label class="form-label text-secondary">Badge Color</label>
                <select name="badge_color" class="form-select">
                    <option value="warning">Gold (👑 VIP)</option>
                    <option value="success">Green (✅ Pro)</option>
                    <option value="primary">Blue (🛡️ Official)</option>
                    <option value="danger">Red (🔥 Elite)</option>
                    <option value="info">Cyan (⚡ Fast)</option>
                    <option value="secondary">Gray (Secondary)</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_verified" value="0">
                    <input type="checkbox" name="is_verified" value="1" class="form-check-input" id="assignVerified" checked>
                    <label class="form-check-label small fw-semibold" for="assignVerified">
                        <i class="bi bi-patch-check-fill text-success"></i> Verified
                    </label>
                </div>
            </div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-warning text-dark"><i class="bi bi-check-lg me-1"></i>Save Badge</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">User</th><th>Email</th><th>Verified Tick</th><th>Badge</th><th>Premium</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-3">
                            <span class="fw-semibold text-dark">{{ $user->name }}</span>
                            @if($user->is_verified)
                                <i class="bi bi-patch-check-fill text-success ms-1" title="Verified Trader"></i>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $user->email }}</td>
                        <td>
                            @if($user->is_verified)
                                <span class="badge bg-success-subtle text-success border border-success px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                            @else
                                <span class="text-secondary small">Not Verified</span>
                            @endif
                        </td>
                        <td>
                            @if($user->chat_badge)
                                <span class="badge bg-{{ $user->badge_color }}">{{ $user->chat_badge }}</span>
                            @else
                                <span class="text-secondary">No badge</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_premium)
                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Premium</span>
                            @else
                                <span class="text-secondary">Free</span>
                            @endif
                        </td>
                        <td class="pe-3">
                            <form method="POST" action="{{ route('admin.chat.update-badge') }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <input type="hidden" name="is_verified" value="0">
                                <input type="hidden" name="chat_badge" value="">
                                <input type="hidden" name="badge_color" value="primary">
                                @if($user->chat_badge || $user->is_verified)
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove badge & verification from {{ $user->name }}?')"><i class="bi bi-x-lg"></i> Clear</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4"><i class="bi bi-award fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No users with badges or verification yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($users->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}</small>
    {{ $users->links() }}
</div>
@endif
@endsection
