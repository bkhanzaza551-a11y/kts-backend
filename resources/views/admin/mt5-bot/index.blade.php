@extends('layouts.app')
@section('title', 'MT5 Bot Management')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0 fw-bold"><i class="bi bi-robot me-2 text-primary"></i>MT5 Bot Management</h4>
    @if(auth()->user()->hasPermission('mt5_bot_manage'))
    <a href="{{ route('admin.mt5-bot.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Bot</a>
    @endif
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-dark mb-0 fw-bold">{{ $stats['total'] }}</h3><small class="text-secondary">Total Bots</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-success mb-0 fw-bold">{{ $stats['active'] }}</h3><small class="text-secondary">Active</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-danger mb-0 fw-bold">{{ $stats['errors'] }}</h3><small class="text-secondary">Errors</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-info mb-0 fw-bold">{{ number_format($stats['total_trades']) }}</h3><small class="text-secondary">Total Trades</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="text-primary mb-0 fw-bold">${{ number_format($stats['total_balance'], 2) }}</h3><small class="text-secondary">Balance</small>
        </div></div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100"><div class="card-body p-3 text-center">
            <h3 class="{{ $stats['total_profit'] - $stats['total_loss'] >= 0 ? 'text-success' : 'text-danger' }} mb-0 fw-bold">${{ number_format($stats['total_profit'] - $stats['total_loss'], 2) }}</h3><small class="text-secondary">Net P/L</small>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-secondary">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Name, account, server..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="error" {{ request('status') === 'error' ? 'selected' : '' }}>Error</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-secondary">Mode</label>
                <select name="mode" class="form-select">
                    <option value="">All</option>
                    <option value="live" {{ request('mode') === 'live' ? 'selected' : '' }}>Live</option>
                    <option value="demo" {{ request('mode') === 'demo' ? 'selected' : '' }}>Demo</option>
                    <option value="backtest" {{ request('mode') === 'backtest' ? 'selected' : '' }}>Backtest</option>
                </select>
            </div>
            <div class="col-md-2 d-grid"><button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i></button></div>
        </form>
        @if(request()->hasAny(['search','status','mode']))
        <div class="mt-2"><a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg me-1"></i>Clear</a></div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr>
                    <th class="ps-3">Bot Name</th>
                    <th>Account</th>
                    <th>Server</th>
                    <th>Status</th>
                    <th>Mode</th>
                    <th>Balance</th>
                    <th>Trades</th>
                    <th>Win Rate</th>
                    <th class="pe-3">Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($bots as $bot)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('admin.mt5-bot.show', $bot) }}" class="text-dark text-decoration-none fw-semibold">{{ $bot->name }}</a>
                            @if($bot->auto_trade)<span class="badge bg-success ms-1" style="font-size:0.6rem;">AUTO</span>@endif
                            <div class="text-secondary small">{{ Str::limit($bot->description, 40) }}</div>
                        </td>
                        <td class="text-dark">{{ $bot->mt5_account_number }}</td>
                        <td class="text-secondary">{{ Str::limit($bot->mt5_server, 20) }}</td>
                        <td><span class="badge bg-{{ $bot->status_color }}">{{ ucfirst($bot->status) }}</span></td>
                        <td><span class="badge bg-{{ $bot->mode_color }}">{{ ucfirst($bot->mode) }}</span></td>
                        <td class="text-dark">${{ number_format($bot->balance, 2) }}</td>
                        <td class="text-secondary">{{ $bot->total_trades }}</td>
                        <td class="text-secondary">{{ $bot->win_rate }}%</td>
                        <td class="pe-3">
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.mt5-bot.show', $bot) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                                @if(auth()->user()->hasPermission('mt5_bot_manage'))
                                <a href="{{ route('admin.mt5-bot.edit', $bot) }}" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-secondary py-4"><i class="bi bi-robot fs-1 d-block mb-2 opacity-50"></i><p class="mb-0">No bots configured yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@if($bots->hasPages())
<div class="d-flex justify-content-between align-items-center mt-3">
    <small class="text-secondary">Showing {{ $bots->firstItem() }}-{{ $bots->lastItem() }} of {{ $bots->total() }}</small>
    {{ $bots->links() }}
</div>
@endif
@endsection
