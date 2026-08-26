@extends('layouts.app')

@section('title', 'Signal - ' . $signal->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">
            @if($signal->direction === 'buy')
            <span class="badge bg-success me-2"><i class="bi bi-arrow-up"></i> BUY</span>
            @else
            <span class="badge bg-danger me-2"><i class="bi bi-arrow-down"></i> SELL</span>
            @endif
            {{ $signal->title }}
        </h4>
        <small class="text-secondary">{{ $signal->symbol }} @if($signal->is_featured)<span class="badge bg-warning text-dark ms-1"><i class="bi bi-star-fill"></i> Featured</span>@endif</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($signal->status === 'draft' && auth()->user()->hasPermission('signals_create'))
        <form method="POST" action="{{ route('admin.signals.publish', $signal) }}" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Publish</button>
        </form>
        @endif

        @if($signal->status === 'active' && auth()->user()->hasPermission('signals_edit'))
        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeModal">
            <i class="bi bi-stop-circle me-1"></i>Close Signal
        </button>
        @endif

        @if(auth()->user()->hasPermission('signals_edit'))
        <a href="{{ route('admin.signals.edit', $signal) }}" class="btn btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        @endif

        @if(auth()->user()->hasPermission('signals_delete'))
        <form method="POST" action="{{ route('admin.signals.destroy', $signal) }}" class="d-inline" onsubmit="return confirm('Delete this signal permanently?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
        @endif

        <a href="{{ route('admin.signals.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <small class="text-secondary d-block mb-1">Entry Price</small>
                        <h5 class=" mb-0">{{ $signal->entry_price ?? 'Not set' }}</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-success d-block mb-1">Take Profit</small>
                        <h5 class=" mb-0">{{ $signal->take_profit ?? 'Not set' }}</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-danger d-block mb-1">Stop Loss</small>
                        <h5 class=" mb-0">{{ $signal->stop_loss ?? 'Not set' }}</h5>
                    </div>
                    <div class="col-md-3">
                        <small class="text-secondary d-block mb-1">Close Price</small>
                        <h5 class=" mb-0">{{ $signal->close_price ?? '-' }}</h5>
                    </div>
                </div>

                @if($signal->description)
                <div class="border-top pt-3">
                    <h6 class="text-secondary mb-2">Description</h6>
                    <p class="text-dark">{{ $signal->description }}</p>
                </div>
                @endif
            </div>
        </div>

        @if($signal->categories->count())
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="text-secondary mb-2">Categories</h6>
                @foreach($signal->categories as $cat)
                <span class="badge me-1 mb-1" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40; font-size: 0.8rem;">{{ $cat->name }}</span>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Status</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-secondary d-block">Status</small>
                        @php $sc = match($signal->status) { 'draft' => 'bg-info', 'pending' => 'bg-warning text-dark', 'active' => 'bg-success', 'closed' => 'bg-secondary', 'cancelled' => 'bg-danger', default => 'bg-secondary' }; @endphp
                        <span class="badge {{ $sc }}">{{ ucfirst($signal->status) }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Result</small>
                        @if($signal->status === 'closed')
                            @php $rc = match($signal->result) { 'win' => 'bg-success', 'loss' => 'bg-danger', 'breakeven' => 'bg-secondary', default => 'bg-secondary' }; @endphp
                            <span class="badge {{ $rc }}">{{ ucfirst($signal->result) }}</span>
                        @else
                            <span class="text-secondary">-</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Pips</small>
                        @if($signal->pips_result !== null)
                            @if($signal->pips_result > 0)
                            <span class="text-success fw-bold fs-5">+{{ number_format($signal->pips_result, 1) }}</span>
                            @elseif($signal->pips_result < 0)
                            <span class="text-danger fw-bold fs-5">{{ number_format($signal->pips_result, 1) }}</span>
                            @else
                            <span class="text-secondary fs-5">0.0</span>
                            @endif
                        @else
                            <span class="text-secondary">-</span>
                        @endif
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Views</small>
                        <span class="text-dark">{{ number_format($signal->views_count) }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Created</small>
                        <span class="text-dark">{{ $signal->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Published</small>
                        <span class="text-dark">{{ $signal->published_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Closed</small>
                        <span class="text-dark">{{ $signal->closed_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Expires</small>
                        <span class="text-dark">{{ $signal->expires_at?->format('M d, Y H:i') ?? 'N/A' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block">Notified</small>
                        <span class="text-dark">{{ number_format($signal->followers_notified) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Created By</h6></div>
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                        <span class="text-white small fw-bold">{{ strtoupper(substr($signal->creator->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <span class="text-dark">{{ $signal->creator->name }}</span>
                        <div class="text-secondary small">{{ $signal->creator->email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($signal->status === 'active' && auth()->user()->hasPermission('signals_edit'))
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.signals.close', $signal) }}">
                @csrf
                <div class="modal-header">
                    <h5 class=" modal-title">Close Signal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Result <span class="text-danger">*</span></label>
                        <select name="result" class="form-select" required>
                            <option value="win">Win</option>
                            <option value="loss">Loss</option>
                            <option value="breakeven">Breakeven</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Pips Result <span class="text-danger">*</span></label>
                        <input type="number" name="pips_result" class="form-control" step="0.1" required placeholder="e.g. 10.5 or -8.2">
                        <small class="text-secondary">Positive for profit, negative for loss.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Close Price</label>
                        <input type="number" name="close_price" class="form-control" step="0.00001" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Close Signal</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
