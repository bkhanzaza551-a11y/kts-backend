@extends('layouts.app')
@section('title', 'Chat Moderation')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-chat-left-dots-fill me-2 text-primary"></i>Chat Moderation</h4>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.chat.rooms') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-door-open-fill me-1"></i>Rooms</a>
        <a href="{{ route('admin.chat.badges') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-award-fill me-1"></i>Badges</a>
        <a href="{{ route('admin.chat.restricted-words') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-file-word me-1"></i>Restricted Words</a>
        <a href="{{ route('admin.chat.banned-users') }}" class="btn btn-outline-danger btn-sm"><i class="bi bi-person-x-fill me-1"></i>Banned Users</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="mb-0 fw-bold" style="color:var(--text-primary);">{{ number_format($stats['total_messages']) }}</h3><small class="text-secondary">Total Messages</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">{{ number_format($stats['today_messages']) }}</h3><small class="text-secondary">Today</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-warning mb-0 fw-bold">{{ $stats['flagged'] }}</h3><small class="text-secondary">Flagged</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-danger mb-0 fw-bold">{{ $stats['deleted'] }}</h3><small class="text-secondary">Deleted</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-danger mb-0 fw-bold">{{ $stats['banned_users'] }}</h3><small class="text-secondary">Banned</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="mb-0 fw-bold" style="color:var(--text-secondary);">{{ $stats['rooms'] }}</h3><small class="text-secondary">Rooms</small>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-secondary fw-medium">Room</label>
                <select name="room_id" class="form-select">
                    <option value="">All Rooms</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary fw-medium">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search messages..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary fw-medium">Flagged</label>
                <select name="flagged" class="form-select">
                    <option value="">All</option>
                    <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Flagged Only</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary fw-medium">Status</label>
                <select name="deleted" class="form-select">
                    <option value="">All</option>
                    <option value="0" {{ request('deleted') === '0' ? 'selected' : '' }}>Active</option>
                    <option value="1" {{ request('deleted') === '1' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['room_id','search','flagged','deleted']))
                <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-square-text me-2"></i>Messages</h6>
        <small class="text-secondary">{{ $messages->total() }} messages</small>
    </div>
    <div class="card-body p-0">
        @forelse($messages as $msg)
        <div class="chat-message-item px-4 py-3 border-bottom {{ $msg->is_deleted ? 'opacity-50' : '' }} {{ $msg->is_flagged ? 'bg-warning bg-opacity-10' : '' }}" style="transition:background 0.15s ease;">
            <div class="d-flex align-items-start gap-3">
                @php
                    $userName = $msg->user?->name ?? 'Deleted User';
                    $avatarColor = match(true) {
                        $msg->user?->is_premium => '#f59e0b',
                        $msg->user?->chat_badge => match($msg->user->badge_color) {
                            'primary' => '#4f46e5',
                            'success' => '#10b981',
                            'danger' => '#ef4444',
                            'warning' => '#f59e0b',
                            default => '#6b7280',
                        },
                        default => '#4f46e5',
                    };
                @endphp
                <div class="flex-shrink-0">
                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:42px;height:42px;background:{{ $avatarColor }};font-size:0.95rem;">
                        {{ strtoupper(substr($userName, 0, 1)) }}
                    </div>
                </div>
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="fw-semibold" style="color:var(--text-primary);">{{ $userName }}</span>
                        @if($msg->user?->chat_badge)
                        <span class="badge rounded-pill" style="background:{{ $avatarColor }}20;color:{{ $avatarColor }};font-size:0.7rem;">{{ $msg->user->chat_badge }}</span>
                        @endif
                        @if($msg->user?->is_premium)
                        <span class="badge rounded-pill bg-warning text-dark" style="font-size:0.65rem;"><i class="bi bi-star-fill me-1"></i>Premium</span>
                        @endif
                        <span class="badge bg-secondary" style="font-size:0.7rem;">{{ $msg->room->name ?? 'N/A' }}</span>
                        @if($msg->is_pinned)
                        <span class="badge bg-info" style="font-size:0.65rem;"><i class="bi bi-pin-fill me-1"></i>Pinned</span>
                        @endif
                        @if($msg->is_flagged)
                        <span class="badge bg-warning text-dark" style="font-size:0.65rem;"><i class="bi bi-flag-fill me-1"></i>Flagged</span>
                        @endif
                        @if($msg->is_deleted)
                        <span class="badge bg-danger" style="font-size:0.65rem;"><i class="bi bi-trash-fill me-1"></i>Deleted</span>
                        @endif
                        <span class="text-muted small ms-auto" style="font-size:0.75rem;">{{ $msg->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="chat-bubble p-3 rounded-3 mb-2" style="background:{{ $msg->is_deleted ? '#fef2f2' : '#f3f4f6' }};max-width:85%;border:1px solid {{ $msg->is_deleted ? '#fecaca' : '#e5e7eb' }};">
                        @if($msg->is_deleted)
                            <span class="text-decoration-line-through" style="color:#9ca3af;">{{ Str::limit($msg->filtered_message, 150) }}</span>
                            @if($msg->deleter)
                            <div class="mt-1"><small class="text-danger"><i class="bi bi-info-circle me-1"></i>Deleted by {{ $msg->deleter->name }}</small></div>
                            @endif
                        @else
                            <span style="color:var(--text-primary);">{{ Str::limit($msg->filtered_message, 150) }}</span>
                        @endif
                    </div>
                    @if(auth()->user()->hasAnyPermission(['chat_moderate', 'chat_delete_message', 'chat_ban_user']))
                    <div class="d-flex gap-1 flex-wrap">
                        @if(auth()->user()->hasPermission('chat_moderate') && !$msg->is_deleted)
                        <form method="POST" action="{{ $msg->is_pinned ? route('admin.chat.unpin-message', $msg) : route('admin.chat.pin-message', $msg) }}" class="d-inline" onsubmit="return confirm('{{ $msg->is_pinned ? 'Unpin this message?' : 'Pin this message?' }}')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $msg->is_pinned ? 'btn-info' : 'btn-outline-info' }}" style="font-size:0.75rem;padding:0.2rem 0.5rem;" title="{{ $msg->is_pinned ? 'Unpin' : 'Pin' }}">
                                <i class="bi bi-pin"></i>
                            </button>
                        </form>
                        @endif
                        @if(auth()->user()->hasPermission('chat_moderate'))
                        <form method="POST" action="{{ route('admin.chat.toggle-flag', $msg) }}" class="d-inline" onsubmit="return confirm('{{ $msg->is_flagged ? 'Remove flag?' : 'Flag this message?' }}')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $msg->is_flagged ? 'btn-warning' : 'btn-outline-warning' }}" style="font-size:0.75rem;padding:0.2rem 0.5rem;" title="{{ $msg->is_flagged ? 'Unflag' : 'Flag' }}">
                                <i class="bi bi-flag"></i>
                            </button>
                        </form>
                        @endif
                        @if(auth()->user()->hasPermission('chat_moderate') && $msg->is_deleted)
                        <form method="POST" action="{{ route('admin.chat.restore-message', $msg) }}" class="d-inline" onsubmit="return confirm('Restore this message?')">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-outline-success" style="font-size:0.75rem;padding:0.2rem 0.5rem;" title="Restore"><i class="bi bi-arrow-counterclockwise"></i></button>
                        </form>
                        @endif
                        @if(auth()->user()->hasPermission('chat_delete_message'))
                        <form method="POST" action="{{ route('admin.chat.destroy-message', $msg) }}" class="d-inline" onsubmit="return confirm('Delete this message?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;padding:0.2rem 0.5rem;" title="Delete"><i class="bi bi-trash3"></i></button>
                        </form>
                        @endif
                        @if(auth()->user()->hasPermission('chat_ban_user') && !$msg->is_deleted)
                        <form method="POST" action="{{ route('admin.chat.ban-user') }}" class="d-inline" onsubmit="return confirm('Ban this user from chat?')">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $msg->user_id }}">
                            <input type="hidden" name="reason" value="Flagged message: {{ Str::limit($msg->message, 50) }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger" style="font-size:0.75rem;padding:0.2rem 0.5rem;" title="Ban User"><i class="bi bi-person-x"></i></button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-secondary py-5">
            <i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50"></i>
            <p class="mb-0">No messages found.</p>
            <small class="text-muted">Messages will appear here as users chat.</small>
        </div>
        @endforelse
    </div>
    @if($messages->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <small class="text-secondary">Showing {{ $messages->firstItem() }}-{{ $messages->lastItem() }} of {{ $messages->total() }}</small>
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection
