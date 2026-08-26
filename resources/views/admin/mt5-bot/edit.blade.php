@extends('layouts.app')
@section('title', 'Edit: ' . $bot->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-robot me-2 text-primary"></i>Edit: {{ $bot->name }}</h4>
    <div class="d-flex gap-2">
        @if(auth()->user()->hasPermission('mt5_bot_manage'))
        <form method="POST" action="{{ route('admin.mt5-bot.toggle-status', $bot) }}" class="d-inline" onsubmit="return confirm('{{ $bot->status === 'active' ? 'Stop this bot?' : 'Start this bot?' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $bot->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                <i class="bi bi-{{ $bot->status === 'active' ? 'stop-circle' : 'play-circle' }} me-1"></i>{{ $bot->status === 'active' ? 'Stop' : 'Start' }}
            </button>
        </form>
        @endif
        <a href="{{ route('admin.mt5-bot.show', $bot) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>
<form method="POST" action="{{ route('admin.mt5-bot.update', $bot) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">MT5 Connection</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Bot Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $bot->name) }}" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000">{{ old('description', $bot->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_account_number" class="form-control @error('mt5_account_number') is-invalid @enderror" value="{{ old('mt5_account_number', $bot->mt5_account_number) }}" required maxlength="50">
                            @error('mt5_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Server <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_server" class="form-control @error('mt5_server') is-invalid @enderror" value="{{ old('mt5_server', $bot->mt5_server) }}" required maxlength="255">
                            @error('mt5_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Bot File (.ex5, .set) <span class="text-secondary">(leave blank to keep)</span></label>
                            <input type="file" name="bot_file" class="form-control @error('bot_file') is-invalid @enderror" accept=".ex5,.mq5,.set,.xml,.json,.txt">
                            @error('bot_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">API Key <span class="text-secondary">(leave blank to keep)</span></label>
                            <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" maxlength="255">
                            @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">API Secret <span class="text-secondary">(leave blank to keep)</span></label>
                            <input type="password" name="api_secret" class="form-control @error('api_secret') is-invalid @enderror" value="" maxlength="255">
                            @error('api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Trading Parameters</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Mode <span class="text-danger">*</span></label>
                            <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                                <option value="demo" {{ old('mode', $bot->mode) === 'demo' ? 'selected' : '' }}>Demo</option>
                                <option value="live" {{ old('mode', $bot->mode) === 'live' ? 'selected' : '' }}>Live</option>
                                <option value="backtest" {{ old('mode', $bot->mode) === 'backtest' ? 'selected' : '' }}>Backtest</option>
                            </select>
                            @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Take Profit (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="take_profit_pips" class="form-control @error('take_profit_pips') is-invalid @enderror" value="{{ old('take_profit_pips', $bot->take_profit_pips) }}" required min="1" max="10000" step="0.01">
                            @error('take_profit_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Stop Loss (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="stop_loss_pips" class="form-control @error('stop_loss_pips') is-invalid @enderror" value="{{ old('stop_loss_pips', $bot->stop_loss_pips) }}" required min="1" max="10000" step="0.01">
                            @error('stop_loss_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Max Daily Trades <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_trades" class="form-control @error('max_daily_trades') is-invalid @enderror" value="{{ old('max_daily_trades', $bot->max_daily_trades) }}" required min="1" max="1000">
                            @error('max_daily_trades')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Max Daily Loss ($) <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_loss" class="form-control @error('max_daily_loss') is-invalid @enderror" value="{{ old('max_daily_loss', $bot->max_daily_loss) }}" required min="1" max="1000000" step="0.01">
                            @error('max_daily_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_trade" value="1" id="autoTradeCheck" {{ old('auto_trade', $bot->auto_trade) ? 'checked' : '' }}>
                            <label class="form-check-label text-secondary" for="autoTradeCheck">Enable Auto-Trade</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Bot Status</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Status:</span><span class="badge bg-{{ $bot->status_color }}">{{ ucfirst($bot->status) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Created:</span><span class="text-dark">{{ $bot->created_at?->format('M d, Y') ?? '-' }}</span></div>
                    <div class="d-flex justify-content-between"><span class="text-secondary">Last Updated:</span><span class="text-dark">{{ $bot->updated_at?->diffForHumans() ?? '-' }}</span></div>
                </div>
            </div>
            @if(auth()->user()->hasPermission('mt5_bot_manage'))
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Update Bot</button>
            </div>
            @endif
        </div>
    </div>
</form>
@if(auth()->user()->hasPermission('mt5_bot_manage'))
<div class="mt-3">
    <form method="POST" action="{{ route('admin.mt5-bot.destroy', $bot) }}" onsubmit="return confirm('Delete this bot? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete Bot</button>
    </form>
</div>
@endif
@endsection
