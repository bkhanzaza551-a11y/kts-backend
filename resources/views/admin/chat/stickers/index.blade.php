@extends('layouts.app')
@section('title', 'Sticker Management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-emoji-smile me-2 text-warning"></i>Sticker Management</h4>
    <a href="{{ route('admin.chat.stickers.create-pack') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Sticker Pack</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total_packs']) }}</h3><small class="text-secondary">Total Packs</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ number_format($stats['active_packs']) }}</h3><small class="text-secondary">Active Packs</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total_stickers']) }}</h3><small class="text-secondary">Total Stickers</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">{{ number_format($stats['total_usage']) }}</h3><small class="text-secondary">Total Usage</small>
        </div></div>
    </div>
    <div class="col-xl-4 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            @if($stats['most_used'])
            <h6 class="text-dark mb-0 fw-bold" style="font-size:0.85rem;">{{ $stats['most_used']->name }}</h6>
            <small class="text-secondary">Most Used ({{ number_format($stats['most_used']->usage_count) }}x)</small>
            @else
            <h6 class="text-secondary mb-0">-</h6>
            <small class="text-secondary">No usage yet</small>
            @endif
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-secondary fw-medium">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Pack name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary fw-medium">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.chat.stickers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Pack</th>
                    <th>Stickers</th>
                    <th>Active</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date</th>
                    <th class="pe-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($packs as $pack)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center gap-2">
                                @if($pack->thumbnail)
                                <img src="{{ asset('storage/' . $pack->thumbnail) }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">
                                @else
                                <div class="rounded d-flex align-items-center justify-content-center bg-warning bg-opacity-10" style="width:36px;height:36px;">
                                    <i class="bi bi-emoji-smile text-warning"></i>
                                </div>
                                @endif
                                <div>
                                    <a href="{{ route('admin.chat.stickers.show-pack', $pack) }}" class="text-dark text-decoration-none fw-semibold">{{ $pack->name }}</a>
                                    @if($pack->description)<div class="text-secondary small">{{ Str::limit($pack->description, 40) }}</div>@endif
                                </div>
                            </div>
                        </td>
                        <td><span class="text-dark">{{ $pack->stickers_count }}</span></td>
                        <td><span class="text-success">{{ $pack->active_stickers_count }}</span></td>
                        <td>
                            <form method="POST" action="{{ route('admin.chat.stickers.toggle-pack', $pack) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $pack->is_active ? 'btn-outline-success' : 'btn-outline-secondary' }}" style="font-size:0.75rem;">
                                    {{ $pack->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="text-secondary">{{ $pack->creator->name ?? '-' }}</td>
                        <td class="text-secondary small">{{ $pack->created_at->format('M d, Y') }}</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.chat.stickers.show-pack', $pack) }}" class="btn btn-sm btn-outline-info" title="View Stickers"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.chat.stickers.edit-pack', $pack) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.chat.stickers.destroy-pack', $pack) }}" class="d-inline" onsubmit="return confirm('Delete this pack and ALL its stickers?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash3"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-secondary py-4">
                        <i class="bi bi-emoji-smile fs-1 d-block mb-2 opacity-50"></i>
                        <p class="mb-0">No sticker packs yet.</p>
                        <a href="{{ route('admin.chat.stickers.create-pack') }}" class="btn btn-sm btn-outline-primary mt-2">Create First Pack</a>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($packs->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $packs->firstItem() }}-{{ $packs->lastItem() }} of {{ $packs->total() }}</small>
    {{ $packs->links() }}
</div>
@endif
@endsection
