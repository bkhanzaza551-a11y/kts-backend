@extends('layouts.app')
@section('title', 'Chat Moderation')

@section('content')
<div class="container-fluid px-0">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="p-2.5 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                <i class="bi bi-chat-left-dots-fill fs-4"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">Chat Moderation & Live Feed</h4>
                <p class="text-secondary mb-0 small">Monitor community chat rooms, moderate user messages, pin announcements, and manage filters.</p>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.chat.rooms') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" style="border-radius:8px;">
                <i class="bi bi-door-open-fill"></i> Rooms <span class="badge bg-primary text-white rounded-pill ms-1">{{ $stats['rooms'] }}</span>
            </a>
            <a href="{{ route('admin.chat.badges') }}" class="btn btn-sm btn-outline-warning text-dark d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" style="border-radius:8px;">
                <i class="bi bi-award-fill text-warning"></i> Badges
            </a>
            <a href="{{ route('admin.chat.restricted-words') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" style="border-radius:8px;">
                <i class="bi bi-shield-x"></i> Restricted Words
            </a>
            <a href="{{ route('admin.chat.banned-users') }}" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1.5 px-3 py-1.5 fw-semibold" style="border-radius:8px;">
                <i class="bi bi-person-x-fill"></i> Banned Users @if($stats['banned_users'] > 0)<span class="badge bg-danger text-white rounded-pill ms-1">{{ $stats['banned_users'] }}</span>@endif
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 shadow-sm" role="alert" style="border-radius:10px;">
        <i class="bi bi-check-circle-fill fs-5"></i>
        <div class="flex-grow-1 fw-medium">{{ session('success') }}</div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Metrics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Total Messages</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ number_format($stats['total_messages']) }}</h4>
                        </div>
                        <div class="stat-icon bg-light text-secondary rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-chat-dots-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Today</p>
                            <h4 class="mb-0 fw-bold text-primary">{{ number_format($stats['today_messages']) }}</h4>
                        </div>
                        <div class="stat-icon bg-primary-subtle text-primary rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-send-check-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Flagged</p>
                            <h4 class="mb-0 fw-bold {{ $stats['flagged'] > 0 ? 'text-warning' : 'text-dark' }}">{{ $stats['flagged'] }}</h4>
                        </div>
                        <div class="stat-icon bg-warning-subtle text-warning-emphasis rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-flag-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Deleted</p>
                            <h4 class="mb-0 fw-bold {{ $stats['deleted'] > 0 ? 'text-danger' : 'text-dark' }}">{{ $stats['deleted'] }}</h4>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-trash3-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Banned Users</p>
                            <h4 class="mb-0 fw-bold {{ $stats['banned_users'] > 0 ? 'text-danger' : 'text-dark' }}">{{ $stats['banned_users'] }}</h4>
                        </div>
                        <div class="stat-icon bg-danger-subtle text-danger rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-person-x-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card stat-card border-0 h-100 shadow-sm" style="border-radius:12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-secondary mb-1 small fw-medium">Chat Rooms</p>
                            <h4 class="mb-0 fw-bold text-dark">{{ $stats['rooms'] }}</h4>
                        </div>
                        <div class="stat-icon bg-info-subtle text-info-emphasis rounded-2 p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                            <i class="bi bi-door-open-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="card mb-4 border-0 shadow-sm" style="border-radius:12px;">
        <div class="card-body p-3">
            <form method="GET" class="row g-2.5 align-items-end" id="chatFilterForm">
                <div class="col-xl-3 col-lg-3 col-md-6">
                    <label class="form-label small text-secondary fw-semibold mb-1">Room</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-door-closed"></i></span>
                        <select name="room_id" class="form-select form-select-sm border-start-0 ps-1">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-3 col-md-6">
                    <label class="form-label small text-secondary fw-semibold mb-1">Search Keyword / User</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-1" placeholder="Search message text or username..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                    <label class="form-label small text-secondary fw-semibold mb-1">Flagged State</label>
                    <select name="flagged" class="form-select form-select-sm">
                        <option value="">All Messages</option>
                        <option value="1" {{ request('flagged') === '1' ? 'selected' : '' }}>Flagged Only</option>
                    </select>
                </div>

                <div class="col-xl-2 col-lg-2 col-md-3 col-6">
                    <label class="form-label small text-secondary fw-semibold mb-1">Message Status</label>
                    <select name="deleted" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="0" {{ request('deleted') === '0' ? 'selected' : '' }}>Active Only</option>
                        <option value="1" {{ request('deleted') === '1' ? 'selected' : '' }}>Deleted Only</option>
                    </select>
                </div>

                <div class="col-auto d-flex gap-2 ms-auto pt-2 pt-md-0">
                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1" style="border-radius:8px;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    @php
                        $hasActiveFilters = filled(request('room_id')) ||
                                            filled(request('search')) ||
                                            filled(request('flagged')) ||
                                            (request('deleted') !== null && request('deleted') !== '');
                    @endphp
                    @if($hasActiveFilters)
                    <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-secondary px-3 d-inline-flex align-items-center gap-1" style="border-radius:8px;" title="Clear all filters">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Messages Feed Card --}}
    <div class="card border-0 shadow-sm" style="border-radius:12px; overflow:hidden;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="p-1.5 rounded-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;">
                    <i class="bi bi-chat-square-text fs-6"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">Live Community Chat Stream</h6>
                    <small class="text-muted">Real-time public trader room conversations and automated moderation</small>
                </div>
            </div>
            <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill fw-semibold" style="font-size:0.75rem;">
                {{ number_format($messages->total()) }} Total Messages
            </span>
        </div>

        <div class="card-body p-0">
            @forelse($messages as $msg)
            @php
                $user = $msg->user;
                $userName = $user?->name ?? 'Deleted User';
                $avatarColor = match(true) {
                    $user?->is_premium => '#d97706',
                    $user?->chat_badge => match($user->badge_color) {
                        'primary' => '#4338ca',
                        'success' => '#059669',
                        'danger' => '#dc2626',
                        'warning' => '#d97706',
                        default => '#4b5563',
                    },
                    default => '#2563eb',
                };
            @endphp
            <div class="chat-message-row px-4 py-3 border-bottom {{ $msg->is_deleted ? 'msg-deleted' : '' }} {{ $msg->is_flagged ? 'msg-flagged' : '' }}" style="transition:background-color 0.15s ease;">
                <div class="d-flex align-items-start gap-3">
                    {{-- User Avatar --}}
                    <div class="flex-shrink-0 pt-0.5">
                        <div class="user-avatar-circle shadow-sm" style="background:{{ $avatarColor }};">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                    </div>

                    {{-- Message Content Body --}}
                    <div class="flex-grow-1 min-width-0">
                        {{-- Meta Header: User Name, Badges, Room, Time --}}
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1.5">
                            @if($user)
                            <a href="{{ route('admin.users.show', $user->id) }}" class="fw-bold text-dark text-decoration-none hover-primary user-name-link">
                                {{ $userName }}
                            </a>
                            @else
                            <span class="fw-bold text-secondary">{{ $userName }}</span>
                            @endif

                            @if($user?->chat_badge)
                            <span class="badge rounded-pill fw-semibold" style="background:{{ $avatarColor }}18; color:{{ $avatarColor }}; font-size:0.7rem; border:1px solid {{ $avatarColor }}30;">
                                <i class="bi bi-patch-check-fill me-0.5"></i>{{ $user->chat_badge }}
                            </span>
                            @endif

                            @if($user?->is_premium)
                            <span class="badge rounded-pill bg-warning text-dark fw-semibold" style="font-size:0.68rem;">
                                <i class="bi bi-star-fill me-0.5"></i>VIP
                            </span>
                            @endif

                            <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">
                                <i class="bi bi-hash text-primary me-0.5"></i>{{ $msg->room->name ?? 'Global Room' }}
                            </span>

                            @if($msg->is_pinned)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-0.5" style="font-size:0.68rem;">
                                <i class="bi bi-pin-angle-fill me-0.5"></i>Pinned
                            </span>
                            @endif

                            @if($msg->is_flagged)
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size:0.68rem;">
                                <i class="bi bi-flag-fill me-0.5"></i>Flagged
                            </span>
                            @endif

                            @if($msg->is_deleted)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size:0.68rem;">
                                <i class="bi bi-trash3-fill me-0.5"></i>Deleted
                            </span>
                            @endif

                            <span class="text-muted small ms-auto" style="font-size:0.75rem;" title="{{ $msg->created_at->format('M d, Y h:i A') }}">
                                <i class="bi bi-clock me-1 text-muted"></i>{{ $msg->created_at->diffForHumans() }}
                            </span>
                        </div>

                        {{-- Modern Chat Message Bubble --}}
                        <div class="chat-bubble-modern p-3 mb-2">
                            @if($msg->is_deleted)
                                <div class="text-decoration-line-through text-muted fst-italic">
                                    {{ $msg->filtered_message }}
                                </div>
                                <div class="mt-2 pt-1 border-top border-danger-subtle d-flex align-items-center gap-1.5 text-danger small">
                                    <i class="bi bi-shield-slash-fill"></i>
                                    <span>This message was removed by <strong>{{ $msg->deleter?->name ?? 'System Moderator' }}</strong></span>
                                </div>
                            @else
                                <div class="chat-text-content">
                                    {{ $msg->filtered_message }}
                                </div>
                            @endif

                            @if($msg->sticker)
                            <div class="mt-2 pt-2 border-top">
                                <img src="{{ asset($msg->sticker->image_path) }}" alt="Sticker" class="rounded" style="max-height:90px; object-fit:contain;">
                            </div>
                            @endif
                        </div>

                        {{-- Actions Bar --}}
                        @if(auth()->user()->hasAnyPermission(['chat_moderate', 'chat_delete_message', 'chat_ban_user']))
                        <div class="d-flex align-items-center gap-1.5 flex-wrap moderation-actions">
                            {{-- Pin / Unpin --}}
                            @if(auth()->user()->hasPermission('chat_moderate') && !$msg->is_deleted)
                            <form method="POST" action="{{ $msg->is_pinned ? route('admin.chat.unpin-message', $msg) : route('admin.chat.pin-message', $msg) }}" class="d-inline" onsubmit="return confirm('{{ $msg->is_pinned ? 'Unpin this message from the room?' : 'Pin this announcement message?' }}')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $msg->is_pinned ? 'btn-info text-white' : 'btn-light border text-info' }} action-pill-btn" title="{{ $msg->is_pinned ? 'Unpin message' : 'Pin message' }}">
                                    <i class="bi bi-pin-angle{{ $msg->is_pinned ? '-fill' : '' }}"></i> <span>{{ $msg->is_pinned ? 'Unpin' : 'Pin' }}</span>
                                </button>
                            </form>
                            @endif

                            {{-- Flag / Unflag --}}
                            @if(auth()->user()->hasPermission('chat_moderate') && !$msg->is_deleted)
                            <form method="POST" action="{{ route('admin.chat.toggle-flag', $msg) }}" class="d-inline" onsubmit="return confirm('{{ $msg->is_flagged ? 'Remove flag from this message?' : 'Flag this message as inappropriate?' }}')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $msg->is_flagged ? 'btn-warning text-dark' : 'btn-light border text-warning' }} action-pill-btn" title="{{ $msg->is_flagged ? 'Unflag' : 'Flag' }}">
                                    <i class="bi bi-flag{{ $msg->is_flagged ? '-fill' : '' }}"></i> <span>{{ $msg->is_flagged ? 'Flagged' : 'Flag' }}</span>
                                </button>
                            </form>
                            @endif

                            {{-- Restore Deleted --}}
                            @if(auth()->user()->hasPermission('chat_moderate') && $msg->is_deleted)
                            <form method="POST" action="{{ route('admin.chat.restore-message', $msg) }}" class="d-inline" onsubmit="return confirm('Restore this deleted message back to the chat?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-light border text-success action-pill-btn" title="Restore message">
                                    <i class="bi bi-arrow-counterclockwise"></i> <span>Restore</span>
                                </button>
                            </form>
                            @endif

                            {{-- Delete Message --}}
                            @if(auth()->user()->hasPermission('chat_delete_message') && !$msg->is_deleted)
                            <form method="POST" action="{{ route('admin.chat.destroy-message', $msg) }}" class="d-inline" onsubmit="return confirm('Permanently remove this message from the chat room?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light border text-danger action-pill-btn" title="Delete message">
                                    <i class="bi bi-trash3"></i> <span>Delete</span>
                                </button>
                            </form>
                            @endif

                            {{-- Ban User From Chat --}}
                            @if(auth()->user()->hasPermission('chat_ban_user') && !$msg->is_deleted && $msg->user_id)
                            <form method="POST" action="{{ route('admin.chat.ban-user') }}" class="d-inline" onsubmit="return confirm('Ban {{ addslashes($userName) }} from participating in chat rooms?')">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $msg->user_id }}">
                                <input type="hidden" name="reason" value="Banned via chat moderation: {{ Str::limit($msg->message, 50) }}">
                                <button type="submit" class="btn btn-sm btn-light border text-danger action-pill-btn" title="Ban user from chat">
                                    <i class="bi bi-person-x"></i> <span>Ban User</span>
                                </button>
                            </form>
                            @endif

                            {{-- View User Profile --}}
                            @if($user)
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-light border text-secondary action-pill-btn" title="Inspect user profile and trade activity">
                                <i class="bi bi-person-badge"></i> <span>Profile</span>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 px-3">
                <div class="p-3 bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:64px;height:64px;">
                    <i class="bi bi-chat-dots fs-2 text-muted"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Messages Found</h5>
                <p class="text-secondary small mb-3">No chat messages match your current filter criteria or room selection.</p>
                <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-primary px-3 fw-semibold">Clear Filters</a>
            </div>
            @endforelse
        </div>

        @if($messages->hasPages())
        <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted fw-medium">Showing {{ $messages->firstItem() }} to {{ $messages->lastItem() }} of {{ number_format($messages->total()) }} messages</small>
            <div>{{ $messages->links() }}</div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .chat-message-row:hover {
        background-color: #f8fafc !important;
    }
    .msg-flagged {
        background-color: rgba(254, 240, 138, 0.15) !important;
        border-left: 3px solid #f59e0b !important;
    }
    .msg-deleted {
        background-color: rgba(254, 202, 202, 0.12) !important;
        opacity: 0.8;
    }
    .user-avatar-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .chat-bubble-modern {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 4px 14px 14px 14px;
        max-width: 820px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
    .msg-deleted .chat-bubble-modern {
        background-color: #fef2f2;
        border-color: #fecaca;
    }
    .chat-text-content {
        color: #1e293b;
        font-size: 0.9rem;
        line-height: 1.55;
        word-break: break-word;
    }
    .action-pill-btn {
        font-size: 0.75rem !important;
        padding: 0.22rem 0.55rem !important;
        border-radius: 6px !important;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    .action-pill-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.06);
    }
    .hover-primary:hover {
        color: #2563eb !important;
    }
</style>
@endpush
