@extends('layouts.app')
@section('title', 'Support Chat - ' . $ticket->ticket_number)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-headset me-2 text-success"></i>{{ $ticket->subject }}
        </h4>
        <small class="text-secondary">{{ $ticket->ticket_number }} | {{ $ticket->user->name ?? 'N/A' }} ({{ $ticket->user->email ?? '' }})</small>
    </div>
    <div class="d-flex gap-2">
        @if($ticket->status == 'open')
            <form method="POST" action="{{ route('admin.support-chat.close', $ticket) }}" onsubmit="return confirm('Close this ticket?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Close</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.support-chat.reopen', $ticket) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-arrow-clockwise me-1"></i>Reopen</button>
            </form>
        @endif
        <a href="{{ route('admin.support-chat.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div style="max-height:500px;overflow-y:auto;" id="chatMessages">
                    @forelse($replies as $reply)
                    <div class="d-flex mb-3 {{ $reply->user_id == $ticket->user_id ? 'justify-content-start' : 'justify-content-end' }}">
                        <div class="p-3 rounded-3 {{ $reply->user_id == $ticket->user_id ? 'bg-light' : 'bg-primary text-white' }}" style="max-width:75%;">
                            <div class="fw-semibold mb-1" style="font-size:0.8rem;">
                                {{ $reply->user->name ?? 'System' }}
                                @if($reply->is_system)
                                    <span class="badge bg-info ms-1" style="font-size:0.65rem;">System</span>
                                @endif
                                <span class="ms-2 opacity-75" style="font-size:0.7rem;">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="white-space:pre-wrap;">{{ $reply->message }}</div>
                            @if($reply->attachment)
                                <div class="mt-2">
                                    @if(in_array(pathinfo($reply->attachment, PATHINFO_EXTENSION), ['jpg','jpeg','png','gif','webp']))
                                        <img src="{{ asset('storage/' . $reply->attachment) }}" style="max-width:200px;border-radius:8px;cursor:pointer;" onclick="window.open(this.src)">
                                    @else
                                        <a href="{{ asset('storage/' . $reply->attachment) }}" target="_blank" class="btn btn-sm btn-outline-light mt-1">
                                            <i class="bi bi-paperclip me-1"></i>Attachment
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-secondary">
                        <i class="bi bi-chat-left-text display-4"></i>
                        <p class="mt-2">No messages yet. Send the first reply below.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        @if($ticket->status == 'open')
        <div class="card mt-3">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.support-chat.reply', $ticket) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your reply..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Attachment (optional)</label>
                        <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.mp4">
                        <small class="text-secondary">Max 10MB. Supported: JPG, PNG, GIF, WebP, PDF, MP4</small>
                    </div>
                    <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Send Reply</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header fw-bold">User Info</div>
            <div class="card-body">
                <p><strong>Name:</strong> {{ $ticket->user->name ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $ticket->user->email ?? 'N/A' }}</p>
                <p><strong>Phone:</strong> {{ $ticket->user->phone ?? 'N/A' }}</p>
                <p><strong>Status:</strong>
                    @if($ticket->status == 'open')
                        <span class="badge bg-success">Open</span>
                    @else
                        <span class="badge bg-secondary">Closed</span>
                    @endif
                </p>
                <p><strong>Priority:</strong> {{ ucfirst($ticket->priority ?? 'medium') }}</p>
                <p><strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

<script>
setTimeout(function() {
    var el = document.getElementById('chatMessages');
    if (el) el.scrollTop = el.scrollHeight;
}, 100);
</script>
@endsection
