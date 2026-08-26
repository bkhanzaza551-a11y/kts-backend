@extends('layouts.app')
@section('title', 'Chat Rooms')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-door-open me-2 text-info"></i>Chat Rooms</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.chat.badges') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-award me-1"></i>Badges</a>
        <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

@if(auth()->user()->hasPermission('chat_moderate'))
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Create Room</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.chat.store-room') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label text-secondary">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary">Description</label>
                <input type="text" name="description" class="form-control" maxlength="500">
            </div>
            <div class="col-md-2">
                <div class="form-check mt-4">
                    <input type="hidden" name="is_public" value="0">
                    <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublic" checked>
                    <label class="form-check-label text-secondary" for="isPublic">Public</label>
                </div>
            </div>
            <div class="col-md-3 d-grid"><button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Create</button></div>
        </form>
    </div>
</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Name</th><th>Slug</th><th>Messages</th><th>Public</th><th>Status</th><th>Paused</th><th class="pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($rooms as $room)
                    <tr class="{{ !$room->is_active ? 'opacity-50' : '' }} {{ $room->is_paused ? 'table-warning' : '' }}">
                        <td class="ps-3">
                            <span class="fw-semibold text-dark">{{ $room->name }}</span>
                            @if($room->description)<div class="text-secondary small">{{ Str::limit($room->description, 40) }}</div>@endif
                            @if($room->is_paused && $room->pause_reason)<div class="text-warning small"><i class="bi bi-pause-circle me-1"></i>{{ $room->pause_reason }}</div>@endif
                        </td>
                        <td class="text-secondary"><code>{{ $room->slug }}</code></td>
                        <td class="text-secondary">{{ $room->messages_count }}</td>
                        <td><span class="badge bg-{{ $room->is_public ? 'info' : 'secondary' }}">{{ $room->is_public ? 'Public' : 'Private' }}</span></td>
                        <td><span class="badge bg-{{ $room->is_active ? 'success' : 'danger' }}">{{ $room->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td><span class="badge bg-{{ $room->is_paused ? 'warning' : 'secondary' }}">{{ $room->is_paused ? 'Paused' : 'Active' }}</span></td>
                        <td class="pe-3">
                            @if(auth()->user()->hasPermission('chat_moderate'))
                            <div class="d-flex gap-1">
                                <form method="POST" action="{{ route('admin.chat.toggle-pause-room', $room) }}" class="d-inline" onsubmit="return confirm('{{ $room->is_paused ? 'Resume chat in this room?' : 'Pause chat in this room? Users wont be able to send messages.' }}')">
                                    @csrf @method('PATCH')
                                    @if(!$room->is_paused)
                                    <input type="hidden" name="pause_reason" value="Paused by admin">
                                    @endif
                                    <button type="submit" class="btn btn-sm {{ $room->is_paused ? 'btn-success' : 'btn-outline-warning' }}" title="{{ $room->is_paused ? 'Resume' : 'Pause' }}"><i class="bi bi-{{ $room->is_paused ? 'play' : 'pause' }}"></i></button>
                                </form>
                                <form method="POST" action="{{ route('admin.chat.toggle-room', $room) }}" class="d-inline" onsubmit="return confirm('Toggle room status?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $room->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $room->is_active ? 'Deactivate' : 'Activate' }}"><i class="bi bi-{{ $room->is_active ? 'pause' : 'play' }}"></i></button>
                                </form>
                                <form method="POST" action="{{ route('admin.chat.destroy-room', $room) }}" class="d-inline" onsubmit="return confirm('Delete this room and all its messages?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">No rooms yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($rooms->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $rooms->firstItem() }}-{{ $rooms->lastItem() }} of {{ $rooms->total() }}</small>
    {{ $rooms->links() }}
</div>
@endif
@endsection
