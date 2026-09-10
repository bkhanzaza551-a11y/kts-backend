@extends('layouts.app')
@section('title', 'MT5 Bot Management & Configuration')
@section('content')

{{-- Top Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 fw-bold text-dark"><i class="bi bi-robot me-2 text-primary"></i>MT5 Bot Management</h4>
        <p class="text-secondary small mb-0">Configure the master trading bot, lot sizing rules, risk parameters, and mobile app live details.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        @if(auth()->user()->hasPermission('mt5_bot_manage'))
        <form method="POST" action="{{ route('admin.mt5-bot.toggle-status', $bot) }}" class="d-inline" onsubmit="return confirm('{{ $bot->status === 'active' ? 'Stop this bot? Trading will be paused.' : 'Start this bot?' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $bot->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }} fw-semibold">
                <i class="bi bi-{{ $bot->status === 'active' ? 'stop-circle' : 'play-circle' }} me-1"></i>{{ $bot->status === 'active' ? 'Stop Bot' : 'Start Bot' }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.mt5-bot.toggle-auto-trade', $bot) }}" class="d-inline" onsubmit="return confirm('{{ $bot->auto_trade ? 'Disable auto-trade?' : 'Enable auto-trade?' }}')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-sm {{ $bot->auto_trade ? 'btn-success' : 'btn-outline-secondary' }} fw-semibold">
                <i class="bi bi-cpu me-1"></i>Auto-Trade: {{ $bot->auto_trade ? 'ON' : 'OFF' }}
            </button>
        </form>

        <form method="POST" action="{{ route('admin.mt5-bot.recalculate-stats', $bot) }}" class="d-inline" onsubmit="return confirm('Recalculate win rate and profit stats from trade logs?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-info fw-semibold">
                <i class="bi bi-arrow-repeat me-1"></i>Recalculate Stats
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Top Summary Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <span class="badge bg-{{ $bot->status_color }} px-3 py-1 fs-6 mb-1 text-uppercase">{{ $bot->status }}</span>
                <div class="text-secondary small fw-medium">Bot Status</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <h3 class="text-dark mb-0 fw-bold">{{ $bot->mode ? ucfirst($bot->mode) : 'Live' }}</h3>
                <small class="text-secondary">Execution Mode</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <h3 class="text-info mb-0 fw-bold">{{ number_format($bot->win_rate, 1) }}%</h3>
                <small class="text-secondary">Win Rate ({{ $bot->winning_trades }}/{{ $bot->total_trades }})</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <h3 class="text-primary mb-0 fw-bold">{{ $bot->total_trades }}</h3>
                <small class="text-secondary">Total Trades</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <h3 class="text-dark mb-0 fw-bold">${{ number_format($bot->balance, 2) }}</h3>
                <small class="text-secondary">Balance / Equity</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card stat-card border-0 h-100 shadow-sm">
            <div class="card-body p-3 text-center">
                <h3 class="{{ $bot->net_profit >= 0 ? 'text-success' : 'text-danger' }} mb-0 fw-bold">
                    {{ $bot->net_profit >= 0 ? '+' : '' }}${{ number_format($bot->net_profit, 2) }}
                </h3>
                <small class="text-secondary">Net Profit</small>
            </div>
        </div>
    </div>
</div>

{{-- Main Bot Editor Form --}}
<form method="POST" action="{{ route('admin.mt5-bot.update', $bot) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    
    <div class="row g-4">
        {{-- Left Column: Bot Identity & Lot Sizing --}}
        <div class="col-lg-8">
            {{-- Card 1: Bot Identity --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-robot me-2 text-primary"></i>Bot Identity & MT5 Connection</h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Mobile App Synchronized</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">Bot Title / Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $bot->name) }}" required maxlength="255" placeholder="e.g. KTS10 Pips Bot">
                            <div class="form-text">This title is shown prominently in the mobile app.</div>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">Trading Symbol / Pair / Server <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_server" class="form-control @error('mt5_server') is-invalid @enderror" value="{{ old('mt5_server', $bot->mt5_server) }}" required maxlength="255" placeholder="e.g. Exness-MT5Real">
                            <div class="form-text">MT5 broker server name.</div>
                            @error('mt5_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label text-dark fw-semibold">Bot Description & Strategy Details</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000" placeholder="e.g. Automated Gold (XAUUSD) & Forex Scalping Bot based on 10 pips algorithm">{{ old('description', $bot->description) }}</textarea>
                            <div class="form-text">Short description shown beneath the bot title in the mobile app.</div>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">MT5 Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_account_number" class="form-control @error('mt5_account_number') is-invalid @enderror" value="{{ old('mt5_account_number', $bot->mt5_account_number) }}" required maxlength="50" placeholder="e.g. 87654321">
                            @error('mt5_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">Upload Bot File (.exe, .ex5, .txt, .zip, etc.)</label>
                            <input type="file" name="bot_file" class="form-control @error('bot_file') is-invalid @enderror" accept=".exe,.ex5,.ex4,.txt,.zip,.rar,.set,.mq5,.mq4,.dll,.json">
                            <div class="form-text small">Accepted: <code>.exe, .ex5, .ex4, .txt, .zip, .rar, .set, .mq5, .mq4</code> (Max: 100MB)</div>
                            @error('bot_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Uploaded Bot File Details Card --}}
                        <div class="col-12">
                            @if($bot->hasBotFile())
                            <div class="p-3 bg-light rounded border d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                        <i class="bi bi-file-earmark-code fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">
                                            {{ $bot->bot_file_name ?: basename($bot->bot_file_path) }}
                                            <span class="badge bg-success ms-2"><i class="bi bi-shield-check me-1"></i>Available for Approved Users</span>
                                        </div>
                                        <div class="text-secondary small">
                                            <span><i class="bi bi-hdd me-1"></i>Size: <strong>{{ $bot->bot_file_size ?: 'Uploaded' }}</strong></span>
                                            @if($bot->bot_file_uploaded_at)
                                            <span class="ms-3"><i class="bi bi-calendar3 me-1"></i>Uploaded: {{ $bot->bot_file_uploaded_at->format('M d, Y H:i') }}</span>
                                            @endif
                                            <span class="ms-3 badge bg-light text-dark border text-uppercase">{{ $bot->bot_file_type ?: 'File' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.mt5-bot.download-file', $bot) }}" class="btn btn-sm btn-primary">
                                        <i class="bi bi-download me-1"></i>Download File
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Are you sure you want to delete the bot software file? Users will not be able to download it until re-uploaded.')) { document.getElementById('delete-bot-file-form').submit(); }">
                                        <i class="bi bi-trash me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                            @else
                            <div class="p-3 bg-light-subtle rounded border border-dashed text-secondary small d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle fs-5 text-primary"></i>
                                <div>
                                    <strong>No bot file uploaded yet.</strong> Upload an <code>.exe</code>, <code>.ex5</code>, <code>.txt</code>, or <code>.zip</code> file above so approved Demo & Real account users can download it inside the mobile app.
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Lot Size & Risk Management (The core requirement) --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calculator me-2 text-warning"></i>Lot Sizing & Risk Management (Mobile Calculator)</h6>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Core Calculation Rules</span>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div class="small">
                            <strong>How Lot Sizing Works:</strong> When users enter their balance in the Mobile App, their lot size scales proportionally: <code>Lot Size = (User Budget / Base Balance) × Base Lot Size</code>.
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">Base Balance (USDT / $) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" id="base_balance_input" name="base_balance" class="form-control @error('base_balance') is-invalid @enderror" value="{{ old('base_balance', $bot->base_balance ?? 100) }}" required min="1" max="1000000" step="0.01">
                            </div>
                            <div class="form-text">e.g. <strong>$100</strong></div>
                            @error('base_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold">Base Lot Size <span class="text-danger">*</span></label>
                            <input type="number" id="base_lot_size_input" name="base_lot_size" class="form-control @error('base_lot_size') is-invalid @enderror" value="{{ old('base_lot_size', $bot->base_lot_size ?? 0.01) }}" required min="0.001" max="100" step="0.001">
                            <div class="form-text">e.g. <strong>0.01</strong> lot for every $100 balance.</div>
                            @error('base_lot_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Live Calculation Preview Matrix --}}
                    <div class="card bg-light border-0 mt-3 p-3">
                        <div class="fw-semibold text-secondary small mb-2"><i class="bi bi-eye me-1"></i>Live Scaling Preview (What mobile users see):</div>
                        <div class="row g-2 text-center" id="lot_preview_container">
                            <div class="col">
                                <div class="bg-white rounded p-2 shadow-sm border">
                                    <div class="text-muted small">$100</div>
                                    <div class="fw-bold text-primary" id="prev_100">0.01 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded p-2 shadow-sm border">
                                    <div class="text-muted small">$500</div>
                                    <div class="fw-bold text-primary" id="prev_500">0.05 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded p-2 shadow-sm border">
                                    <div class="text-muted small">$1,000</div>
                                    <div class="fw-bold text-primary" id="prev_1000">0.10 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded p-2 shadow-sm border">
                                    <div class="text-muted small">$5,000</div>
                                    <div class="fw-bold text-primary" id="prev_5000">0.50 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded p-2 shadow-sm border">
                                    <div class="text-muted small">$10,000</div>
                                    <div class="fw-bold text-primary" id="prev_10000">1.00 Lot</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold">Take Profit (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="take_profit_pips" class="form-control @error('take_profit_pips') is-invalid @enderror" value="{{ old('take_profit_pips', $bot->take_profit_pips ?? 10) }}" required min="0.1" max="10000" step="0.1">
                            <div class="form-text">Default: 10 pips</div>
                            @error('take_profit_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold">Stop Loss (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="stop_loss_pips" class="form-control @error('stop_loss_pips') is-invalid @enderror" value="{{ old('stop_loss_pips', $bot->stop_loss_pips ?? 5) }}" required min="0.1" max="10000" step="0.1">
                            <div class="form-text">Default: 5 pips</div>
                            @error('stop_loss_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold">Max Daily Trades <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_trades" class="form-control @error('max_daily_trades') is-invalid @enderror" value="{{ old('max_daily_trades', $bot->max_daily_trades ?? 10) }}" required min="1" max="1000">
                            <div class="form-text">Max trades / day</div>
                            @error('max_daily_trades')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold">Max Daily Loss ($) <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_loss" class="form-control @error('max_daily_loss') is-invalid @enderror" value="{{ old('max_daily_loss', $bot->max_daily_loss ?? 500) }}" required min="1" max="1000000" step="0.01">
                            <div class="form-text">Daily loss limit</div>
                            @error('max_daily_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Demo & Sandbox Credentials --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-check me-2 text-info"></i>Demo Account Sandbox (Optional for Demo Access)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Demo Server</label>
                            <input type="text" name="demo_server" class="form-control" value="{{ old('demo_server', $bot->demo_server) }}" placeholder="e.g. Exness-MT5Trial">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Demo Account Number</label>
                            <input type="text" name="demo_account" class="form-control" value="{{ old('demo_account', $bot->demo_account) }}" placeholder="e.g. 12345678">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Demo Email</label>
                            <input type="email" name="demo_email" class="form-control" value="{{ old('demo_email', $bot->demo_email) }}" placeholder="demo@ktsmarkets.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Demo Phone</label>
                            <input type="text" name="demo_phone" class="form-control" value="{{ old('demo_phone', $bot->demo_phone) }}" placeholder="+923001234567">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small">Demo Initial Deposit ($)</label>
                            <input type="number" name="demo_deposit" class="form-control" value="{{ old('demo_deposit', $bot->demo_deposit ?? 10000) }}" step="0.01">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Operation Status, WhatsApp & Performance Stats --}}
        <div class="col-lg-4">
            {{-- Card: Operation Mode & Status --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-toggle-on me-2 text-success"></i>Operation & Status</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Bot Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $bot->status) === 'active' ? 'selected' : '' }}>🟢 Active (Running)</option>
                            <option value="inactive" {{ old('status', $bot->status) === 'inactive' ? 'selected' : '' }}>⚪ Inactive (Paused)</option>
                            <option value="error" {{ old('status', $bot->status) === 'error' ? 'selected' : '' }}>🔴 Error State</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Execution Mode <span class="text-danger">*</span></label>
                        <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                            <option value="live" {{ old('mode', $bot->mode) === 'live' ? 'selected' : '' }}>🔴 Live Trading</option>
                            <option value="demo" {{ old('mode', $bot->mode) === 'demo' ? 'selected' : '' }}>🔵 Demo Testing</option>
                            <option value="backtest" {{ old('mode', $bot->mode) === 'backtest' ? 'selected' : '' }}>🟡 Backtest Mode</option>
                        </select>
                        @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch p-2 bg-light rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="auto_trade" value="1" id="autoTradeCheck" {{ old('auto_trade', $bot->auto_trade) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-dark" for="autoTradeCheck">Enable Automated Trading</label>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold"><i class="bi bi-whatsapp text-success me-1"></i>WhatsApp Contact Number</label>
                        <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $bot->whatsapp_number ?? '+923371244640') }}" placeholder="+923371244640">
                        <div class="form-text">Mobile app's "Buy Bot via WhatsApp" button opens this direct chat.</div>
                        @error('whatsapp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Card: Performance Stats Overrides --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Live Performance Metrics</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label text-secondary small">Balance ($)</label>
                            <input type="number" name="balance" class="form-control form-control-sm" value="{{ old('balance', $bot->balance) }}" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary small">Equity ($)</label>
                            <input type="number" name="equity" class="form-control form-control-sm" value="{{ old('equity', $bot->equity) }}" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary small">Total Profit ($)</label>
                            <input type="number" name="total_profit" class="form-control form-control-sm" value="{{ old('total_profit', $bot->total_profit) }}" step="0.01">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-secondary small">Total Loss ($)</label>
                            <input type="number" name="total_loss" class="form-control form-control-sm" value="{{ old('total_loss', $bot->total_loss) }}" step="0.01">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-secondary small">Trades</label>
                            <input type="number" name="total_trades" class="form-control form-control-sm" value="{{ old('total_trades', $bot->total_trades) }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-secondary small">Wins</label>
                            <input type="number" name="winning_trades" class="form-control form-control-sm" value="{{ old('winning_trades', $bot->winning_trades) }}">
                        </div>
                        <div class="col-4">
                            <label class="form-label text-secondary small">Losses</label>
                            <input type="number" name="losing_trades" class="form-control form-control-sm" value="{{ old('losing_trades', $bot->losing_trades) }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            @if(auth()->user()->hasPermission('mt5_bot_manage'))
            <div class="d-grid mb-4">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold">
                    <i class="bi bi-cloud-arrow-up me-2"></i>Save & Sync Bot Settings
                </button>
            </div>
            @endif
        </div>
    </div>
</form>

{{-- Bottom Tables: Recent Trades & Logs --}}
<div class="row g-4 mt-1">
    {{-- Recent Trades --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Trade Executions</h6>
                <span class="badge bg-secondary-subtle text-secondary">{{ $tradesCount }} Total Trades</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Symbol</th>
                                <th>Type</th>
                                <th>Lot</th>
                                <th>Open Price</th>
                                <th>Close Price</th>
                                <th>Profit</th>
                                <th>Status</th>
                                <th class="pe-3">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTrades as $trade)
                            <tr>
                                <td class="ps-3 fw-bold">{{ $trade->symbol }}</td>
                                <td>
                                    <span class="badge bg-{{ $trade->type === 'BUY' ? 'success' : 'danger' }}">{{ $trade->type }}</span>
                                </td>
                                <td>{{ number_format($trade->lot_size, 2) }}</td>
                                <td>{{ number_format($trade->open_price, 2) }}</td>
                                <td>{{ $trade->close_price ? number_format($trade->close_price, 2) : '—' }}</td>
                                <td class="fw-semibold {{ $trade->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $trade->profit !== null ? ($trade->profit >= 0 ? '+' : '') . '$' . number_format($trade->profit, 2) : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trade->status === 'open' ? 'info' : 'secondary' }}">{{ ucfirst($trade->status) }}</span>
                                </td>
                                <td class="pe-3 text-secondary small">{{ $trade->opened_at ? $trade->opened_at->format('M d, H:i') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-graph-up fs-2 d-block mb-1 opacity-50"></i>
                                    No trade history yet. Trades executed by this bot will show here in real-time.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- System Logs --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-journal-text me-2 text-info"></i>System Audit Logs</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small">
                    @forelse($bot->logs ?? [] as $log)
                    <li class="list-group-item px-3 py-2 border-bottom">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'success' ? 'success' : 'secondary') }} me-1" style="font-size: 0.65rem;">{{ strtoupper($log->level ?? 'INFO') }}</span>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                        </div>
                        <div class="text-dark mt-1">{{ $log->message }}</div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">No recent bot logs.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Interactive Script for Dynamic Lot Sizing Preview --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseBalInput = document.getElementById('base_balance_input');
    const baseLotInput = document.getElementById('base_lot_size_input');

    function updatePreviews() {
        const baseBal = parseFloat(baseBalInput.value) || 100;
        const baseLot = parseFloat(baseLotInput.value) || 0.01;

        const tiers = [100, 500, 1000, 5000, 10000];
        tiers.forEach(budget => {
            const el = document.getElementById('prev_' + budget);
            if (el) {
                const calculated = ((budget / baseBal) * baseLot).toFixed(2);
                el.innerText = calculated + ' Lot';
            }
        });
    }

    if (baseBalInput && baseLotInput) {
        baseBalInput.addEventListener('input', updatePreviews);
        baseLotInput.addEventListener('input', updatePreviews);
        updatePreviews();
    }
});
</script>

@if(auth()->user()->hasPermission('mt5_bot_manage'))
<form id="delete-bot-file-form" action="{{ route('admin.mt5-bot.delete-file', $bot) }}" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endif

@endsection
