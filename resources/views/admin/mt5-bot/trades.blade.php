@extends('layouts.app')
@section('title', 'Trades: ' . $bot->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2 text-primary"></i>Trades: {{ $bot->name }}</h4>
    <a href="{{ route('admin.mt5-bot.show', $bot) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Bot</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th class="ps-3">Opened</th><th>Symbol</th><th>Type</th><th>Lot</th><th>Entry</th><th>Exit</th><th>S/L</th><th>T/P</th><th>P/L</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($trades as $trade)
                    <tr>
                        <td class="ps-3 text-secondary">{{ $trade->opened_at ? $trade->opened_at->format('M d, H:i') : '-' }}</td>
                        <td class="text-dark fw-semibold">{{ $trade->symbol }}</td>
                        <td><span class="badge bg-{{ $trade->type_color }}">{{ ucfirst($trade->type) }}</span></td>
                        <td class="text-dark">{{ number_format($trade->volume, 2) }}</td>
                        <td class="text-dark">{{ $trade->open_price ? number_format($trade->open_price, 5) : '-' }}</td>
                        <td class="text-dark">{{ $trade->close_price ? number_format($trade->close_price, 5) : '-' }}</td>
                        <td class="text-danger">{{ $trade->stop_loss ? number_format($trade->stop_loss, 5) : '-' }}</td>
                        <td class="text-success">{{ $trade->take_profit ? number_format($trade->take_profit, 5) : '-' }}</td>
                        <td class="{{ $trade->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($trade->net_profit, 2) }}
                            @if($trade->swap != 0)<small class="d-block text-secondary">Swap: ${{ number_format($trade->swap, 2) }}</small>@endif
                        </td>
                        <td><span class="badge bg-{{ $trade->status_color }}">{{ ucfirst($trade->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-secondary py-4"><i class="bi bi-graph-up fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No trades recorded yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($trades->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $trades->firstItem() }}-{{ $trades->lastItem() }} of {{ $trades->total() }}</small>
    {{ $trades->withQueryString()->links() }}
</div>
@endif
@endsection
