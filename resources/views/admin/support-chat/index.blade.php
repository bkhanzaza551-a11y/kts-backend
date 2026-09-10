@extends('layouts.app')
@section('title', 'Support Chats')
@section('content')
{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-headset me-2 text-primary"></i>Support Chats & Helpdesk</h4>
        <p class="text-secondary small mb-0">Manage customer support inquiries, AI chatbot escalations, and live user tickets.</p>
    </div>
    @if(auth()->user()->hasPermission('ai_chat_manage'))
    <a href="{{ route('admin.ai-chatbot.index') }}" class="btn btn-sm btn-outline-primary fw-semibold">
        <i class="bi bi-robot me-1"></i>AI Chatbot Settings
    </a>
    @endif
</div>

{{-- Top Summary Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-semibold text-uppercase">Total Tickets</span>
                    <h3 class="text-dark mb-0 fw-bold mt-1">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-ticket-detailed-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-semibold text-uppercase">Open / Pending</span>
                    <h3 class="text-warning mb-0 fw-bold mt-1">{{ number_format($stats['open']) }}</h3>
                </div>
                <div class="bg-warning-subtle text-warning rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-envelope-open-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-semibold text-uppercase">Resolved / Closed</span>
                    <h3 class="text-success mb-0 fw-bold mt-1">{{ number_format($stats['closed']) }}</h3>
                </div>
                <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100" style="border-radius: 14px;">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-semibold text-uppercase">AI Bot Handled</span>
                    <h3 class="text-info mb-0 fw-bold mt-1">{{ number_format($stats['ai_chatbot']) }}</h3>
                </div>
                <div class="bg-info-subtle text-info rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-robot fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters Card --}}
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 14px;">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end" id="supportFilterForm">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ticket #, subject, user name, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>🟢 Open</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>🟡 In Progress</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>⚪ Closed</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Created Between</label>
                <div class="input-group">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date From">
                    <span class="input-group-text bg-light text-muted">to</span>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Date To">
                </div>
            </div>
            <div class="col-auto d-flex gap-2 ms-auto pt-2 pt-md-0">
                <button type="submit" class="btn btn-primary px-3 fw-semibold">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @php
                    $hasActiveFilters = filled(request('search')) ||
                                        filled(request('status')) ||
                                        filled(request('date_from')) ||
                                        filled(request('date_to'));
                @endphp
                @if($hasActiveFilters)
                <a href="{{ route('admin.support-chat.index') }}" class="btn btn-outline-secondary px-3" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tickets Table --}}
<div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3 py-3">Ticket #</th>
                        <th class="py-3">User</th>
                        <th class="py-3">Subject</th>
                        <th class="py-3 text-center">Replies</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Priority</th>
                        <th class="py-3">Created</th>
                        <th class="pe-3 py-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="ps-3">
                            <span class="font-monospace fw-bold text-dark">{{ $ticket->ticket_number }}</span>
                            @if($ticket->source == 'ai_chatbot')
                                <div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-robot me-1"></i>AI Bot</span></div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                    {{ strtoupper(substr($ticket->user->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <strong class="text-dark d-block">{{ $ticket->user->name ?? 'N/A' }}</strong>
                                    <small class="text-secondary">{{ $ticket->user->email ?? '' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.support-chat.show', $ticket) }}" class="text-dark fw-semibold text-decoration-none text-truncate d-inline-block" style="max-width: 250px;">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2 py-1">{{ $ticket->replies->count() }}</span>
                        </td>
                        <td class="text-center">
                            @if($ticket->status == 'open')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">🟢 Open</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">🟡 In Progress</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">⚪ Closed</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($ticket->priority == 'high')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">High</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">Medium</span>
                            @else
                                <span class="badge bg-light text-secondary border px-2 py-1">Low</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-secondary">{{ $ticket->created_at->diffForHumans() }}</small>
                        </td>
                        <td class="pe-3 text-end">
                            <a href="{{ route('admin.support-chat.show', $ticket) }}" class="btn btn-sm btn-outline-primary fw-semibold px-3">
                                <i class="bi bi-chat-dots me-1"></i>Open Chat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <div class="bg-light text-secondary rounded-circle p-3 d-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                                    <i class="bi bi-inbox fs-2 text-muted"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">No support chats found</h6>
                                <p class="text-secondary small mb-0">No active support conversations match the current filter criteria.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tickets->hasPages())
    <div class="card-footer bg-white border-top py-3">
        {{ $tickets->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
