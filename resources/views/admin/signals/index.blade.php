@extends('layouts.app')

@section('title', 'Signal Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-broadcast me-2 text-primary"></i>Signal Management</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.signal-categories.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-tags me-1"></i>Categories
        </a>
        @if(auth()->user()->hasPermission('signals_create'))
        <a href="{{ route('admin.signals.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>New Signal
        </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ number_format($stats['total']) }}</h3>
            <small class="text-secondary">Total</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['draft']) }}</h3>
            <small class="text-secondary">Draft</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ number_format($stats['active']) }}</h3>
            <small class="text-secondary">Active</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-secondary mb-0 fw-bold">{{ number_format($stats['closed']) }}</h3>
            <small class="text-secondary">Closed</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">+{{ number_format($stats['total_pips'], 1) }}</h3>
            <small class="text-secondary">Total Pips</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            @php $winRate = $stats['total'] > 0 ? round(($stats['wins'] / max($stats['wins'] + $stats['losses'], 1)) * 100) : 0; @endphp
            <h3 class="text-warning mb-0 fw-bold">{{ $winRate }}%</h3>
            <small class="text-secondary">Win Rate</small>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small text-secondary">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Title, symbol..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach(['draft','pending','active','closed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Direction</label>
                <select name="direction" class="form-select">
                    <option value="">All</option>
                    <option value="buy" {{ request('direction') === 'buy' ? 'selected' : '' }}>Buy</option>
                    <option value="sell" {{ request('direction') === 'sell' ? 'selected' : '' }}>Sell</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Result</label>
                <select name="result" class="form-select">
                    <option value="">All</option>
                    @foreach(['win','loss','breakeven','pending'] as $r)
                    <option value="{{ $r }}" {{ request('result') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-secondary">Symbol</label>
                <select name="symbol" class="form-select">
                    <option value="">All</option>
                    @foreach($symbols as $sym)
                    <option value="{{ $sym }}" {{ request('symbol') === $sym ? 'selected' : '' }}>{{ $sym }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button>
            </div>
        </form>
        @if(request()->hasAny(['search','status','direction','result','category_id','symbol']))
        <div class="mt-2"><a href="{{ route('admin.signals.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a></div>
        @endif
    </div>
</div>

@php
    $currentSort = request('sort', 'created_at');
    $currentDir = request('dir', 'desc');
    $sortLink = function ($col) use ($currentSort, $currentDir) {
        $newDir = ($currentSort === $col && $currentDir === 'asc') ? 'desc' : 'asc';
        return route('admin.signals.index', array_merge(request()->query(), ['sort' => $col, 'dir' => $newDir]));
    };
@endphp

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">
                            <a href="{{ $sortLink('title') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                Signal
                                @if($currentSort === 'title')<i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                @else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif
                            </a>
                        </th>
                        <th>Symbol</th>
                        <th>Direction</th>
                        <th>Entry</th>
                        <th>TP / SL</th>
                        <th>
                            <a href="{{ $sortLink('status') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                Status
                                @if($currentSort === 'status')<i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                @else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif
                            </a>
                        </th>
                        <th>Result</th>
                        <th>
                            <a href="{{ $sortLink('pips_result') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                Pips
                                @if($currentSort === 'pips_result')<i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                @else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('created_at') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                Date
                                @if($currentSort === 'created_at')<i class="bi bi-chevron-{{ $currentDir === 'asc' ? 'up' : 'down' }}" style="font-size:0.6rem;"></i>
                                @else<i class="bi bi-arrow-down-up small opacity-50"></i>@endif
                            </a>
                        </th>
                        <th class="pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($signals as $signal)
                    <tr>
                        <td class="ps-3">
                            <div>
                                <a href="{{ route('admin.signals.show', $signal) }}" class="text-dark text-decoration-none fw-semibold">{{ $signal->title }}</a>
                                @if($signal->is_featured)
                                <span class="badge bg-warning text-dark ms-1" style="font-size:0.6rem;"><i class="bi bi-star-fill"></i></span>
                                @endif
                                @if($signal->categories->count())
                                <div class="mt-1">
                                    @foreach($signal->categories as $cat)
                                    <span class="badge" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40;">{{ $cat->name }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge bg-secondary">{{ $signal->symbol }}</span></td>
                        <td>
                            @if($signal->direction === 'buy')
                            <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>BUY</span>
                            @else
                            <span class="badge bg-danger"><i class="bi bi-arrow-down me-1"></i>SELL</span>
                            @endif
                        </td>
                        <td class="text-dark small">{{ $signal->entry_price ?? '-' }}</td>
                        <td class="small">
                            @if($signal->take_profit)
                            <span class="text-success">{{ $signal->take_profit }}</span>
                            @endif
                            @if($signal->stop_loss)
                            <span class="text-danger"> / {{ $signal->stop_loss }}</span>
                            @endif
                            @if(!$signal->take_profit && !$signal->stop_loss)
                            <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = match($signal->status) {
                                    'draft' => 'bg-info',
                                    'pending' => 'bg-warning text-dark',
                                    'active' => 'bg-success',
                                    'closed' => 'bg-secondary',
                                    'cancelled' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ ucfirst($signal->status) }}</span>
                        </td>
                        <td>
                            @if($signal->status === 'closed')
                                @php
                                    $resultClass = match($signal->result) {
                                        'win' => 'bg-success',
                                        'loss' => 'bg-danger',
                                        'breakeven' => 'bg-secondary',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $resultClass }}">{{ ucfirst($signal->result) }}</span>
                            @elseif($signal->isPending())
                            <span class="text-secondary small">-</span>
                            @else
                            <span class="badge bg-warning text-dark">pending</span>
                            @endif
                        </td>
                        <td>
                            @if($signal->pips_result !== null)
                                @if($signal->pips_result > 0)
                                <span class="text-success fw-bold">+{{ number_format($signal->pips_result, 1) }}</span>
                                @elseif($signal->pips_result < 0)
                                <span class="text-danger fw-bold">{{ number_format($signal->pips_result, 1) }}</span>
                                @else
                                <span class="text-secondary">0.0</span>
                                @endif
                            @else
                            <span class="text-secondary">-</span>
                            @endif
                        </td>
                        <td class="text-secondary small">{{ $signal->created_at->format('M d, H:i') }}</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.signals.show', $signal) }}" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                @if(auth()->user()->hasPermission('signals_edit'))
                                <a href="{{ route('admin.signals.edit', $signal) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-secondary py-4">
                            <i class="bi bi-broadcast fs-1 d-block mb-2 opacity-50"></i>
                            <p class="mb-0">No signals found.</p>
                            @if(auth()->user()->hasPermission('signals_create'))
                            <a href="{{ route('admin.signals.create') }}" class="btn btn-sm btn-outline-primary mt-2">Create First Signal</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($signals->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $signals->firstItem() }}-{{ $signals->lastItem() }} of {{ $signals->total() }}</small>
    {{ $signals->withQueryString()->links() }}
</div>
@endif
@endsection
