@extends('layouts.app')
@section('title', 'MT5 Bot Management & Engine')
@section('content')

{{-- Top Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-robot text-primary me-2"></i>MT5 Bot Management</h4>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                <i class="bi bi-broadcast me-1"></i>Mobile App Synced
            </span>
        </div>
        <p class="text-secondary small mb-0">Configure master trading bot algorithms, dynamic lot scaling, risk rules, and live trading parameters.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap align-items-center">
        @if(auth()->user()->hasPermission('mt5_bot_view'))
        <a href="{{ route('admin.mt5-bot.send-file') }}" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-2 shadow-sm">
            <i class="bi bi-send me-1"></i>Bot File Hub & Email
        </a>
        @endif
        @if(auth()->user()->hasPermission('mt5_bot_manage'))
        <form method="POST" action="{{ route('admin.mt5-bot.recalculate-stats', $bot) }}" class="d-inline" onsubmit="return confirm('Recalculate win rate and profit stats from trade logs?')">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-secondary fw-semibold px-3 py-2">
                <i class="bi bi-arrow-repeat me-1"></i>Recalculate Stats
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Top Summary Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Status</span>
                    <span class="badge bg-{{ $bot->status === 'active' ? 'success' : ($bot->status === 'inactive' ? 'secondary' : 'danger') }}-subtle text-{{ $bot->status === 'active' ? 'success' : ($bot->status === 'inactive' ? 'secondary' : 'danger') }} rounded-pill px-2">
                        <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>{{ ucfirst($bot->status) }}
                    </span>
                </div>
                <h5 class="fw-bold mb-0 text-dark">{{ $bot->status === 'active' ? 'RUNNING' : strtoupper($bot->status) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Mode</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2">
                        <i class="bi bi-lightning-charge me-1"></i>{{ ucfirst($bot->mode ?? 'live') }}
                    </span>
                </div>
                <h5 class="fw-bold mb-0 text-dark">{{ ucfirst($bot->mode ?? 'Live') }}</h5>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Win Rate</span>
                    <span class="badge bg-info-subtle text-info rounded-pill px-2">{{ $bot->winning_trades }}/{{ $bot->total_trades }} W</span>
                </div>
                <h5 class="fw-bold mb-0 text-info">{{ number_format($bot->win_rate, 1) }}%</h5>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Trades</span>
                    <i class="bi bi-arrow-left-right text-muted"></i>
                </div>
                <h5 class="fw-bold mb-0 text-primary">{{ number_format($bot->total_trades) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Balance</span>
                    <i class="bi bi-wallet2 text-muted"></i>
                </div>
                <h5 class="fw-bold mb-0 text-dark">${{ number_format($bot->balance, 2) }}</h5>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-secondary small fw-medium">Net Profit</span>
                    <i class="bi bi-graph-up text-{{ $bot->net_profit >= 0 ? 'success' : 'danger' }}"></i>
                </div>
                <h5 class="fw-bold mb-0 {{ $bot->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $bot->net_profit >= 0 ? '+' : '' }}${{ number_format($bot->net_profit, 2) }}
                </h5>
            </div>
        </div>
    </div>
</div>

{{-- Main Bot Editor Form --}}
<form method="POST" action="{{ route('admin.mt5-bot.update', $bot) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row g-4">
        {{-- Left Column: Bot Identity & Lot Sizing --}}
        <div class="col-lg-8">
            
            {{-- Card 1: Bot Identity & MT5 Connection --}}
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Bot Identity & MT5 Connection</h6>
                    </div>
                    <span class="badge bg-light text-secondary border">Core Config</span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Bot Title / Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-tag"></i></span>
                                <input type="text" name="name" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" value="{{ old('name', $bot->name) }}" required maxlength="255" placeholder="e.g. KTS Swing Trader">
                            </div>
                            <div class="form-text small">Visible on mobile app dashboard.</div>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Trading Server / Broker <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-hdd-network"></i></span>
                                <input type="text" name="mt5_server" class="form-control border-start-0 ps-0 @error('mt5_server') is-invalid @enderror" value="{{ old('mt5_server', $bot->mt5_server) }}" required maxlength="255" placeholder="e.g. Exness-MT5Real">
                            </div>
                            <div class="form-text small">MT5 broker cluster / server name.</div>
                            @error('mt5_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">MT5 Account Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="bi bi-hash"></i></span>
                                <input type="text" name="mt5_account_number" class="form-control border-start-0 ps-0 @error('mt5_account_number') is-invalid @enderror" value="{{ old('mt5_account_number', $bot->mt5_account_number) }}" required maxlength="50" placeholder="e.g. 87654321">
                            </div>
                            @error('mt5_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Bot Strategy Description</label>
                            <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description', $bot->description) }}" maxlength="1000" placeholder="e.g. Automated Gold & Forex 10 Pips Algorithm">
                            <div class="form-text small">Short summary displayed beneath the bot title.</div>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Bot File Management --}}
                        <div class="col-12 pt-2">
                            <label class="form-label text-dark fw-semibold small mb-2 d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-file-earmark-binary text-primary me-1"></i>Active Bot Software File</span>
                                <a href="{{ route('admin.mt5-bot.send-file') }}" class="text-primary text-decoration-none small">
                                    <i class="bi bi-folder2-open me-1"></i>Manage in Bot File Hub &rarr;
                                </a>
                            </label>

                            @if($bot->hasBotFile())
                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="bi bi-check-circle-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0 small">
                                            {{ $bot->bot_file_name ?: basename($bot->bot_file_path) }}
                                            <span class="badge bg-success-subtle text-success border border-success-subtle ms-2 px-2 py-0.5">Ready for Users</span>
                                        </div>
                                        <div class="text-secondary small mt-0.5" style="font-size: 0.78rem;">
                                            <span>Size: <strong>{{ $bot->bot_file_size ?: 'Uploaded' }}</strong></span>
                                            @if($bot->bot_file_uploaded_at)
                                            <span class="mx-2">&bull;</span>
                                            <span>Uploaded: {{ $bot->bot_file_uploaded_at->format('M d, Y') }}</span>
                                            @endif
                                            <span class="badge bg-white text-dark border ms-2 text-uppercase">{{ $bot->bot_file_type ?: 'File' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    <a href="{{ route('admin.mt5-bot.download-file', $bot) }}" class="btn btn-sm btn-outline-primary fw-semibold px-3">
                                        <i class="bi bi-download me-1"></i>Download
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('replace-file-container').classList.toggle('d-none');">
                                        <i class="bi bi-arrow-repeat me-1"></i>Replace
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('Are you sure you want to delete the bot software file? Users will not be able to download it until re-uploaded.')) { document.getElementById('delete-bot-file-form').submit(); }">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="replace-file-container" class="d-none mt-2">
                                <div class="input-group">
                                    <input type="file" name="bot_file" class="form-control form-control-sm @error('bot_file') is-invalid @enderror" accept=".exe,.ex5,.ex4,.txt,.zip,.rar,.set,.mq5,.mq4,.dll,.json">
                                </div>
                                <div class="form-text small">Accepted: <code>.ex5, .ex4, .zip, .rar, .exe, .set</code> (Max: 100MB)</div>
                            </div>
                            @else
                            <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-muted"></i>
                                    <span class="text-secondary small">No software file attached. Upload to enable one-click user downloads:</span>
                                </div>
                                <div class="flex-grow-1" style="max-width: 320px;">
                                    <input type="file" name="bot_file" class="form-control form-control-sm @error('bot_file') is-invalid @enderror" accept=".exe,.ex5,.ex4,.txt,.zip,.rar,.set,.mq5,.mq4,.dll,.json">
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Lot Sizing & Dynamic Scaler --}}
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-warning-subtle text-warning-emphasis rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-calculator"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Lot Sizing & Dynamic Scaler</h6>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle small px-2">
                        Formula: (Balance / Base) × Lot
                    </span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Base Balance (Budget) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0 fw-bold">$</span>
                                <input type="number" id="base_balance_input" name="base_balance" class="form-control border-start-0 ps-0 @error('base_balance') is-invalid @enderror" value="{{ old('base_balance', $bot->base_balance ?? 100) }}" required min="1" max="1000000" step="0.01">
                            </div>
                            <div class="form-text small">Base account balance reference (e.g. <strong>$100</strong>).</div>
                            @error('base_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Base Lot Size <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" id="base_lot_size_input" name="base_lot_size" class="form-control border-end-0 @error('base_lot_size') is-invalid @enderror" value="{{ old('base_lot_size', $bot->base_lot_size ?? 0.01) }}" required min="0.001" max="100" step="0.001">
                                <span class="input-group-text bg-light text-secondary border-start-0 small fw-semibold">Lot</span>
                            </div>
                            <div class="form-text small">Default lot traded for every base balance (e.g. <strong>0.01</strong>).</div>
                            @error('base_lot_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Live Calculation Preview Matrix --}}
                    <div class="bg-light rounded-3 p-3 mt-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-secondary small"><i class="bi bi-phone me-1"></i>App User Scaler Preview (Real-time):</span>
                            <span class="text-muted small">Auto-calculated</span>
                        </div>
                        <div class="row g-2 text-center" id="lot_preview_container">
                            <div class="col">
                                <div class="bg-white rounded-2 p-2 shadow-xs border">
                                    <div class="text-muted small" style="font-size: 0.75rem;">$100</div>
                                    <div class="fw-bold text-primary small" id="prev_100">0.01 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded-2 p-2 shadow-xs border">
                                    <div class="text-muted small" style="font-size: 0.75rem;">$500</div>
                                    <div class="fw-bold text-primary small" id="prev_500">0.05 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded-2 p-2 shadow-xs border">
                                    <div class="text-muted small" style="font-size: 0.75rem;">$1,000</div>
                                    <div class="fw-bold text-primary small" id="prev_1000">0.10 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded-2 p-2 shadow-xs border">
                                    <div class="text-muted small" style="font-size: 0.75rem;">$5,000</div>
                                    <div class="fw-bold text-primary small" id="prev_5000">0.50 Lot</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-white rounded-2 p-2 shadow-xs border">
                                    <div class="text-muted small" style="font-size: 0.75rem;">$10,000</div>
                                    <div class="fw-bold text-primary small" id="prev_10000">1.00 Lot</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Take Profit <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="take_profit_pips" class="form-control @error('take_profit_pips') is-invalid @enderror" value="{{ old('take_profit_pips', $bot->take_profit_pips ?? 10) }}" required min="0.1" max="10000" step="0.1">
                                <span class="input-group-text bg-light text-secondary">pips</span>
                            </div>
                            @error('take_profit_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Stop Loss <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="stop_loss_pips" class="form-control @error('stop_loss_pips') is-invalid @enderror" value="{{ old('stop_loss_pips', $bot->stop_loss_pips ?? 5) }}" required min="0.1" max="10000" step="0.1">
                                <span class="input-group-text bg-light text-secondary">pips</span>
                            </div>
                            @error('stop_loss_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Max Daily Trades <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="max_daily_trades" class="form-control @error('max_daily_trades') is-invalid @enderror" value="{{ old('max_daily_trades', $bot->max_daily_trades ?? 10) }}" required min="1" max="1000">
                                <span class="input-group-text bg-light text-secondary">trades</span>
                            </div>
                            @error('max_daily_trades')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label text-dark fw-semibold small mb-1">Max Daily Loss <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-secondary">$</span>
                                <input type="number" name="max_daily_loss" class="form-control @error('max_daily_loss') is-invalid @enderror" value="{{ old('max_daily_loss', $bot->max_daily_loss ?? 500) }}" required min="1" max="1000000" step="0.01">
                            </div>
                            @error('max_daily_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Advanced Options Accordion (Cleaned Up & Uncluttered) --}}
            <div class="accordion mb-4 shadow-sm rounded-3 border-0" id="advancedBotSettingsAccordion">
                <div class="accordion-item border-0 rounded-3 overflow-hidden">
                    <h2 class="accordion-header" id="headingAdvanced">
                        <button class="accordion-button collapsed bg-white py-3 fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced" aria-expanded="false" aria-controls="collapseAdvanced">
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-secondary-subtle text-secondary rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                                    <i class="bi bi-sliders fs-6"></i>
                                </div>
                                <span>Advanced: Demo Sandbox & Performance Stats Overrides</span>
                            </div>
                            <span class="badge bg-light text-muted border ms-auto me-3 small fw-normal">Optional Controls</span>
                        </button>
                    </h2>
                    <div id="collapseAdvanced" class="accordion-collapse collapse" aria-labelledby="headingAdvanced" data-bs-parent="#advancedBotSettingsAccordion">
                        <div class="accordion-body bg-light-subtle p-4 border-top">
                            {{-- Sub-section A: Demo Account Sandbox --}}
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-info me-1"></i>Demo Account Sandbox Details</h6>
                                <p class="text-secondary small mb-3">Credentials provided to users requesting test demo bot connectivity.</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small mb-1">Demo Server</label>
                                        <input type="text" name="demo_server" class="form-control form-control-sm" value="{{ old('demo_server', $bot->demo_server) }}" placeholder="e.g. Exness-MT5Trial">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small mb-1">Demo Account Number</label>
                                        <input type="text" name="demo_account" class="form-control form-control-sm" value="{{ old('demo_account', $bot->demo_account) }}" placeholder="e.g. 12345678">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-secondary small mb-1">Demo Email</label>
                                        <input type="email" name="demo_email" class="form-control form-control-sm" value="{{ old('demo_email', $bot->demo_email) }}" placeholder="demo@ktsmarkets.com">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-secondary small mb-1">Demo Phone</label>
                                        <input type="text" name="demo_phone" class="form-control form-control-sm" value="{{ old('demo_phone', $bot->demo_phone) }}" placeholder="+923001234567">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-secondary small mb-1">Demo Deposit ($)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">$</span>
                                            <input type="number" name="demo_deposit" class="form-control" value="{{ old('demo_deposit', $bot->demo_deposit ?? 10000) }}" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="text-secondary opacity-25">

                            {{-- Sub-section B: Manual Performance Stats Override --}}
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-bar-chart-line text-primary me-1"></i>Manual Performance Stats Override</h6>
                                <p class="text-secondary small mb-3">Optionally adjust displayed live bot stats shown to mobile app users.</p>
                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <label class="form-label text-secondary small mb-1">Balance ($)</label>
                                        <input type="number" name="balance" class="form-control form-control-sm" value="{{ old('balance', $bot->balance) }}" step="0.01">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label text-secondary small mb-1">Equity ($)</label>
                                        <input type="number" name="equity" class="form-control form-control-sm" value="{{ old('equity', $bot->equity) }}" step="0.01">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label text-secondary small mb-1">Total Profit ($)</label>
                                        <input type="number" name="total_profit" class="form-control form-control-sm" value="{{ old('total_profit', $bot->total_profit) }}" step="0.01">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label text-secondary small mb-1">Total Loss ($)</label>
                                        <input type="number" name="total_loss" class="form-control form-control-sm" value="{{ old('total_loss', $bot->total_loss) }}" step="0.01">
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <label class="form-label text-secondary small mb-1">Total Trades</label>
                                        <input type="number" name="total_trades" class="form-control form-control-sm" value="{{ old('total_trades', $bot->total_trades) }}">
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <label class="form-label text-secondary small mb-1">Winning Trades</label>
                                        <input type="number" name="winning_trades" class="form-control form-control-sm" value="{{ old('winning_trades', $bot->winning_trades) }}">
                                    </div>
                                    <div class="col-md-4 col-4">
                                        <label class="form-label text-secondary small mb-1">Losing Trades</label>
                                        <input type="number" name="losing_trades" class="form-control form-control-sm" value="{{ old('losing_trades', $bot->losing_trades) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Operation Status, WhatsApp & Save --}}
        <div class="col-lg-4">
            
            {{-- Card: Operation Mode & Controls --}}
            <div class="card shadow-sm mb-4 border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-success-subtle text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-power"></i>
                        </div>
                        <h6 class="mb-0 fw-bold text-dark">Engine Status & Operations</h6>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold small mb-1">Engine Operational Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $bot->status) === 'active' ? 'selected' : '' }}>🟢 Active (Running)</option>
                            <option value="inactive" {{ old('status', $bot->status) === 'inactive' ? 'selected' : '' }}>⚪ Inactive (Paused)</option>
                            <option value="error" {{ old('status', $bot->status) === 'error' ? 'selected' : '' }}>🔴 Error / Standby</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold small mb-1">Execution Mode <span class="text-danger">*</span></label>
                        <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                            <option value="live" {{ old('mode', $bot->mode) === 'live' ? 'selected' : '' }}>🔴 Live Real Trading</option>
                            <option value="demo" {{ old('mode', $bot->mode) === 'demo' ? 'selected' : '' }}>🔵 Demo Testing</option>
                            <option value="backtest" {{ old('mode', $bot->mode) === 'backtest' ? 'selected' : '' }}>🟡 Backtest Analysis</option>
                        </select>
                        @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                            <div>
                                <label class="form-check-label fw-bold text-dark small d-block" for="autoTradeCheck">Automated Trading</label>
                                <span class="text-secondary small" style="font-size: 0.75rem;">Allows auto order placement</span>
                            </div>
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input fs-5" type="checkbox" name="auto_trade" value="1" id="autoTradeCheck" {{ old('auto_trade', $bot->auto_trade) ? 'checked' : '' }} style="cursor: pointer;">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 text-secondary opacity-25">

                    <div class="mb-2">
                        <label class="form-label text-dark fw-semibold small mb-1">
                            <i class="bi bi-whatsapp text-success me-1"></i>WhatsApp Support Number
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success border-end-0"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" name="whatsapp_number" id="whatsapp_input" class="form-control border-start-0 ps-0 @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', $bot->whatsapp_number ?? '+923371244640') }}" placeholder="+923371244640">
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="text-secondary small" style="font-size: 0.75rem;">Opens when user taps "Buy Bot"</span>
                            @if($bot->whatsapp_number)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $bot->whatsapp_number) }}" target="_blank" class="text-success small fw-semibold text-decoration-none" style="font-size: 0.75rem;">
                                <i class="bi bi-box-arrow-up-right me-0.5"></i>Test Chat
                            </a>
                            @endif
                        </div>
                        @error('whatsapp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Submit Action Card --}}
            @if(auth()->user()->hasPermission('mt5_bot_manage'))
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-cloud-arrow-up"></i>
                        <span>Save & Sync Bot Settings</span>
                    </button>
                    <div class="text-center text-muted small mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-check2-circle text-success me-1"></i>Updates sync instantly to all mobile users
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</form>

{{-- Bottom Tables: Recent Trades & Logs --}}
<div class="row g-4 mt-1">
    {{-- Recent Trades --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">Recent Trade Executions</h6>
                </div>
                <span class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $tradesCount }} Total Trades</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 text-secondary small fw-semibold">Symbol</th>
                                <th class="text-secondary small fw-semibold">Type</th>
                                <th class="text-secondary small fw-semibold">Lot</th>
                                <th class="text-secondary small fw-semibold">Open Price</th>
                                <th class="text-secondary small fw-semibold">Close Price</th>
                                <th class="text-secondary small fw-semibold">Profit</th>
                                <th class="text-secondary small fw-semibold">Status</th>
                                <th class="pe-3 text-secondary small fw-semibold">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTrades as $trade)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $trade->symbol }}</td>
                                <td>
                                    <span class="badge bg-{{ $trade->type === 'BUY' ? 'success' : 'danger' }}-subtle text-{{ $trade->type === 'BUY' ? 'success' : 'danger' }} border border-{{ $trade->type === 'BUY' ? 'success' : 'danger' }}-subtle px-2">
                                        {{ $trade->type }}
                                    </span>
                                </td>
                                <td class="small">{{ number_format($trade->lot_size, 2) }}</td>
                                <td class="small">{{ number_format($trade->open_price, 2) }}</td>
                                <td class="small">{{ $trade->close_price ? number_format($trade->close_price, 2) : '—' }}</td>
                                <td class="fw-semibold small {{ $trade->profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $trade->profit !== null ? ($trade->profit >= 0 ? '+' : '') . '$' . number_format($trade->profit, 2) : '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $trade->status === 'open' ? 'info' : 'secondary' }}-subtle text-{{ $trade->status === 'open' ? 'info' : 'secondary' }} rounded-pill px-2">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </td>
                                <td class="pe-3 text-secondary small">{{ $trade->opened_at ? $trade->opened_at->format('M d, H:i') : '—' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-graph-up fs-2 d-block mb-1 opacity-50"></i>
                                    <div class="fw-semibold">No trade history yet</div>
                                    <small class="text-muted">Trades executed by this bot will appear here automatically.</small>
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
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-journal-text"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-dark">System Audit Logs</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush small" style="max-height: 380px; overflow-y: auto;">
                    @forelse($bot->logs ?? [] as $log)
                    <li class="list-group-item px-3 py-2.5 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-{{ $log->level === 'error' ? 'danger' : ($log->level === 'success' ? 'success' : 'secondary') }}-subtle text-{{ $log->level === 'error' ? 'danger' : ($log->level === 'success' ? 'success' : 'secondary') }} px-1.5 py-0.5" style="font-size: 0.65rem;">
                                {{ strtoupper($log->level ?? 'INFO') }}
                            </span>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                        </div>
                        <div class="text-dark" style="font-size: 0.8rem;">{{ $log->message }}</div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">
                        <i class="bi bi-journal-check fs-2 d-block mb-1 opacity-50"></i>
                        No recent bot logs recorded.
                    </li>
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
