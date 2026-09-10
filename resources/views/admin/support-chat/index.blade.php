@extends('layouts.app')
@section('title', 'Support Chats')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-headset me-2 text-success"></i>Support Chats</h4>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
                <small class="text-secondary">Total Tickets</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h3 class="text-warning mb-0 fw-bold">{{ number_format($stats['open']) }}</h3>
                <small class="text-secondary">Open</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h3 class="text-success mb-0 fw-bold">{{ number_format($stats['closed']) }}</h3>
                <small class="text-secondary">Closed</small>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100">
            <div class="card-body p-3 text-center">
                <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['ai_chatbot']) }}</h3>
                <small class="text-secondary">AI Bot Handled</small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <form method="GET" class="row g-2 align-items-end" id="supportFilterForm">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Ticket #, subject, user..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-3 col-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6">
                <label class="form-label small text-secondary fw-semibold mb-1">Created Between</label>
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
                                        filled(request('date_from')) ||
                                        filled(request('date_to'));
                @endphp
                @if($hasActiveFilters)
                <a href="{{ route('admin.support-chat.index') }}" class="btn btn-sm btn-outline-secondary px-3" title="Clear all filters">
                    <i class="bi bi-x-circle me-1"></i>Reset
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>User</th>
                        <th>Subject</th>
                        <th>Replies</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td>
                            <code>{{ $ticket->ticket_number }}</code>
                            @if($ticket->source == 'ai_chatbot')
                                <br><span class="badge bg-info mt-1"><i class="bi bi-robot me-1"></i>AI Chatbot</span>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $ticket->user->name ?? 'N/A' }}</strong><br>
                            <small class="text-secondary">{{ $ticket->user->email ?? '' }}</small>
                        </td>
                        <td>{{ Str::limit($ticket->subject, 50) }}</td>
                        <td><span class="badge bg-info">{{ $ticket->replies->count() }}</span></td>
                        <td>
                            @if($ticket->status == 'open')
                                <span class="badge bg-success">Open</span>
                            @elseif($ticket->status == 'in_progress')
                                <span class="badge bg-warning">In Progress</span>
                            @else
                                <span class="badge bg-secondary">Closed</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->priority == 'high')
                                <span class="badge bg-danger">High</span>
                            @elseif($ticket->priority == 'medium')
                                <span class="badge bg-warning">Medium</span>
                            @else
                                <span class="badge bg-secondary">Low</span>
                            @endif
                        </td>
                        <td>{{ $ticket->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.support-chat.show', $ticket) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-chat-dots me-1"></i>Open
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">No support chats found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $tickets->withQueryString()->links() }}
    </div>
</div>
@endsection
