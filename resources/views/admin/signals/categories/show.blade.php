@extends('layouts.app')

@section('title', 'Category - ' . $signalCategory->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold">
            <span class="badge me-2" style="background-color: {{ $signalCategory->color }}20; color: {{ $signalCategory->color }}; border: 1px solid {{ $signalCategory->color }}40;">
                @if($signalCategory->icon)<i class="bi bi-{{ $signalCategory->icon }} me-1"></i>@endif
                {{ $signalCategory->name }}
            </span>
        </h4>
        <small class="text-secondary">{{ $signalCategory->description ?? 'No description' }}</small>
    </div>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('signal_categories_edit'))
        <a href="{{ route('admin.signal-categories.edit', $signalCategory) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif
        <a href="{{ route('admin.signal-categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ $signalCategory->signals_count }}</h3>
            <small class="text-secondary">Total Signals</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0"><div class="card-body p-3 text-center">
            @php $active = $signalCategory->signals()->where('status', 'active')->count(); @endphp
            <h3 class="text-success mb-0 fw-bold">{{ $active }}</h3>
            <small class="text-secondary">Active</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0"><div class="card-body p-3 text-center">
            <h3 class="text-secondary mb-0 fw-bold">{{ $signalCategory->sort_order }}</h3>
            <small class="text-secondary">Sort Order</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0"><div class="card-body p-3 text-center">
            @if($signalCategory->is_active)
            <h3 class="text-success mb-0 fw-bold">Active</h3>
            @else
            <h3 class="text-secondary mb-0 fw-bold">Inactive</h3>
            @endif
            <small class="text-secondary">Status</small>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h6 class="mb-0">Signals in This Category</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Signal</th>
                        <th>Symbol</th>
                        <th>Direction</th>
                        <th>Status</th>
                        <th>Result</th>
                        <th class="pe-3">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($signalCategory->signals()->with('creator')->latest()->paginate(15) as $signal)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.signals.show', $signal) }}" class="text-dark text-decoration-none fw-semibold">{{ $signal->title }}</a>
                        </td>
                        <td><span class="badge bg-secondary">{{ $signal->symbol }}</span></td>
                        <td>
                            @if($signal->direction === 'buy')
                            <span class="badge bg-success"><i class="bi bi-arrow-up"></i> BUY</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-arrow-down"></i> SELL</span>
                            @endif
                        </td>
                        <td>
                            @php $sc = match($signal->status) { 'draft' => 'bg-info', 'active' => 'bg-success', 'closed' => 'bg-secondary', 'cancelled' => 'bg-danger', default => 'bg-warning text-dark' }; @endphp
                            <span class="badge {{ $sc }}">{{ ucfirst($signal->status) }}</span>
                        </td>
                        <td>
                            @if($signal->status === 'closed')
                            @php $rc = match($signal->result) { 'win' => 'bg-success', 'loss' => 'bg-danger', default => 'bg-secondary' }; @endphp
                            <span class="badge {{ $rc }}">{{ ucfirst($signal->result) }}</span>
                            @else
                            <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="pe-3 text-secondary small">{{ $signal->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No signals in this category.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
