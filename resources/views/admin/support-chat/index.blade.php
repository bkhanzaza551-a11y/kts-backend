@extends('layouts.app')
@section('title', 'Support Chats')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-headset me-2 text-success"></i>Support Chats</h4>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Ticket #, name, email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
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
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><code>{{ $ticket->ticket_number }}</code></td>
                        <td>
                            <strong>{{ $ticket->user->name ?? 'N/A' }}</strong><br>
                            <small class="text-secondary">{{ $ticket->user->email ?? '' }}</small>
                        </td>
                        <td>{{ Str::limit($ticket->subject, 50) }}</td>
                        <td><span class="badge bg-info">{{ $ticket->replies->count() }}</span></td>
                        <td>
                            @if($ticket->status == 'open')
                                <span class="badge bg-success">Open</span>
                            @else
                                <span class="badge bg-secondary">Closed</span>
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
