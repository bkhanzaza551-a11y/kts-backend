@extends('layouts.app')
@section('title', $bot->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-robot me-2 text-primary"></i>{{ $bot->name }}</h4>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('mt5_bot_manage'))
        <form method="POST" action="{{ route('admin.mt5-bot.toggle-status', $bot) }}" class="d-inline" onsubmit="return confirm('{{ $bot->status === 'active' ? 'Stop this bot? Trading will be paused.' : 'Start this bot?' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $bot->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                <i class="bi bi-{{ $bot->status === 'active' ? 'stop-circle' : 'play-circle' }} me-1"></i>{{ $bot->status === 'active' ? 'Stop' : 'Start' }}
            </button>
        </form>
        <form method="POST" action="{{ route('admin.mt5-bot.toggle-auto-trade', $bot) }}" class="d-inline" onsubmit="return confirm('{{ $bot->auto_trade ? 'Disable auto-trade?' : 'Enable auto-trade? Bot will execute trades automatically.' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $bot->auto_trade ? 'btn-outline-warning' : 'btn-outline-info' }}">
                <i class="bi bi-{{ $bot->auto_trade ? 'pause-circle' : 'play-circle' }} me-1"></i>{{ $bot->auto_trade ? 'Disable Auto' : 'Enable Auto' }}
            </button>
        </form>
        <a href="{{ route('admin.mt5-bot.edit', $bot) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil me-1"></i>Edit</a>
        <form method="POST" action="{{ route('admin.mt5-bot.recalculate-stats', $bot) }}" class="d-inline" onsubmit="return confirm('Recalculate bot statistics from trade data?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-info"><i class="bi bi-calculator me-1"></i>Recalc</button>
        </form>
        @endif
        <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0">${{ number_format($bot->balance, 2) }}</h3><small class="text-secondary">Balance</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0">${{ number_format($bot->equity, 2) }}</h3><small class="text-secondary">Equity</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="{{ $bot->net_profit >= 0 ? 'text-success' : 'text-danger' }} mb-0">${{ number_format($bot->net_profit, 2) }}</h3><small class="text-secondary">Net Profit/Loss</small>
        </div></div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0">{{ $bot->win_rate }}%</h3><small class="text-secondary">Win Rate</small>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Connection Details</h6>
                <span class="badge bg-{{ $bot->status_color }}">{{ ucfirst($bot->status) }}</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Account:</span><span class="fw-semibold text-dark">{{ $bot->mt5_account_number }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Server:</span><span class="text-dark">{{ $bot->mt5_server }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Mode:</span><span class="badge bg-{{ $bot->mode_color }}">{{ ucfirst($bot->mode) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Auto Trade:</span><span class="badge bg-{{ $bot->auto_trade ? 'success' : 'secondary' }}">{{ $bot->auto_trade ? 'Enabled' : 'Disabled' }}</span></div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary">Bot File:</span>
                    <span class="text-dark">
                        @if($bot->bot_file_path)
                            <a href="{{ asset('storage/' . $bot->bot_file_path) }}" target="_blank" class="text-decoration-none"><i class="bi bi-file-earmark-code me-1"></i>{{ basename($bot->bot_file_path) }}</a>
                        @else
                            <span class="text-secondary">None</span>
                        @endif
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Description:</span><span class="text-dark text-end" style="max-width:60%">{{ $bot->description ?: '-' }}</span></div>
                @if($bot->creator)
                <div class="d-flex justify-content-between"><span class="text-secondary">Created by:</span><span class="text-dark">{{ $bot->creator->name }}</span></div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h6 class="mb-0">Trading Parameters</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6"><small class="text-secondary d-block">Take Profit</small><span class="text-success fw-semibold">{{ number_format($bot->take_profit_pips, 2) }} pips</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Stop Loss</small><span class="text-danger fw-semibold">{{ number_format($bot->stop_loss_pips, 2) }} pips</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Max Daily Trades</small><span class="fw-semibold text-dark">{{ $bot->max_daily_trades }}</span></div>
                    <div class="col-6"><small class="text-secondary d-block">Max Daily Loss</small><span class="text-danger fw-semibold">${{ number_format($bot->max_daily_loss, 2) }}</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Trades <span class="text-secondary small">({{ $tradesCount }} total, {{ $openTradesCount }} open, {{ $closedTradesCount }} closed)</span></h6>
                <a href="{{ route('admin.mt5-bot.trades', $bot) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th class="ps-3">Pair</th><th>Type</th><th>Lot</th><th>P/L</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentTrades as $trade)
                            <tr>
                                <td class="ps-3 text-dark">{{ $trade->symbol }}</td>
                                <td><span class="badge bg-{{ $trade->type_color }}">{{ ucfirst($trade->type) }}</span></td>
                                <td class="text-dark">{{ number_format($trade->volume, 2) }}</td>
                                <td class="{{ $trade->net_profit >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($trade->net_profit, 2) }}</td>
                                <td><span class="badge bg-{{ $trade->status_color }}">{{ ucfirst($trade->status) }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-secondary py-3">No trades yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Activity Logs</h6>
                <a href="{{ route('admin.mt5-bot.logs', $bot) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th class="ps-3">Level</th><th>Action</th><th>Message</th><th>Time</th></tr></thead>
                        <tbody>
                            @forelse($bot->logs as $log)
                            <tr>
                                <td class="ps-3"><span class="badge bg-{{ $log->level_color }}">{{ strtoupper($log->level) }}</span></td>
                                <td class="text-dark">{{ $log->action }}</td>
                                <td class="text-secondary" style="max-width:200px;">{{ Str::limit($log->message, 60) }}</td>
                                <td class="text-secondary">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-secondary py-3">No logs yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
