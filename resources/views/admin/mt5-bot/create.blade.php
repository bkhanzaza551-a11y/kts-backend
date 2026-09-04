@extends('layouts.app')
@section('title', 'Add MT5 Bot')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-robot me-2 text-primary"></i>Add MT5 Bot</h4>
    <a href="{{ route('admin.mt5-bot.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>
<form method="POST" action="{{ route('admin.mt5-bot.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">MT5 Connection</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Bot Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="1000">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_account_number" class="form-control @error('mt5_account_number') is-invalid @enderror" value="{{ old('mt5_account_number') }}" required maxlength="50">
                            @error('mt5_account_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Server <span class="text-danger">*</span></label>
                            <input type="text" name="mt5_server" class="form-control @error('mt5_server') is-invalid @enderror" value="{{ old('mt5_server') }}" required maxlength="255" placeholder="e.g. MetaQuotes-Demo">
                            @error('mt5_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Bot File (.ex5, .set)</label>
                            <input type="file" name="bot_file" class="form-control @error('bot_file') is-invalid @enderror" accept=".ex5,.mq5,.set,.xml,.json,.txt">
                            @error('bot_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">API Key</label>
                            <input type="text" name="api_key" class="form-control @error('api_key') is-invalid @enderror" value="{{ old('api_key') }}" maxlength="255">
                            @error('api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">API Secret</label>
                            <input type="password" name="api_secret" class="form-control @error('api_secret') is-invalid @enderror" value="{{ old('api_secret') }}" maxlength="255">
                            @error('api_secret')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h6 class="mb-0">Trading Parameters</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Mode <span class="text-danger">*</span></label>
                            <select name="mode" class="form-select @error('mode') is-invalid @enderror" required>
                                <option value="demo" {{ old('mode', 'demo') === 'demo' ? 'selected' : '' }}>Demo</option>
                                <option value="live" {{ old('mode') === 'live' ? 'selected' : '' }}>Live</option>
                                <option value="backtest" {{ old('mode') === 'backtest' ? 'selected' : '' }}>Backtest</option>
                            </select>
                            @error('mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Take Profit (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="take_profit_pips" class="form-control @error('take_profit_pips') is-invalid @enderror" value="{{ old('take_profit_pips', '10') }}" required min="1" max="10000" step="0.01">
                            @error('take_profit_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Stop Loss (pips) <span class="text-danger">*</span></label>
                            <input type="number" name="stop_loss_pips" class="form-control @error('stop_loss_pips') is-invalid @enderror" value="{{ old('stop_loss_pips', '20') }}" required min="1" max="10000" step="0.01">
                            @error('stop_loss_pips')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Max Daily Trades <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_trades" class="form-control @error('max_daily_trades') is-invalid @enderror" value="{{ old('max_daily_trades', '10') }}" required min="1" max="1000">
                            @error('max_daily_trades')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Max Daily Loss ($) <span class="text-danger">*</span></label>
                            <input type="number" name="max_daily_loss" class="form-control @error('max_daily_loss') is-invalid @enderror" value="{{ old('max_daily_loss', '100') }}" required min="1" max="1000000" step="0.01">
                            @error('max_daily_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Purchase & Demo Settings</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label text-secondary">WhatsApp Number <span class="text-secondary">(for purchase enquiries)</span></label>
                            <input type="text" name="whatsapp_number" class="form-control @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number', '+923371244640') }}" placeholder="+923371244640" maxlength="20">
                            @error('whatsapp_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Base Balance (USDT) <span class="text-secondary">(for lot calc)</span></label>
                            <input type="number" name="base_balance" class="form-control @error('base_balance') is-invalid @enderror" value="{{ old('base_balance', 100) }}" min="1" max="1000000" step="0.01">
                            @error('base_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Base Lot Size <span class="text-secondary">(for lot calc)</span></label>
                            <input type="number" name="base_lot_size" class="form-control @error('base_lot_size') is-invalid @enderror" value="{{ old('base_lot_size', 0.1) }}" min="0.01" max="100" step="0.01">
                            @error('base_lot_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <hr class="border-secondary mt-3 mb-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Demo Server <span class="text-secondary">(optional)</span></label>
                            <input type="text" name="demo_server" class="form-control @error('demo_server') is-invalid @enderror" value="{{ old('demo_server') }}" maxlength="100" placeholder="e.g. Exness-MT5">
                            @error('demo_server')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Demo Account Number <span class="text-secondary">(optional)</span></label>
                            <input type="text" name="demo_account" class="form-control @error('demo_account') is-invalid @enderror" value="{{ old('demo_account') }}" maxlength="50" placeholder="e.g. 12345678">
                            @error('demo_account')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Demo Email <span class="text-secondary">(optional)</span></label>
                            <input type="email" name="demo_email" class="form-control @error('demo_email') is-invalid @enderror" value="{{ old('demo_email') }}" maxlength="100" placeholder="demo@exness.com">
                            @error('demo_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Demo Phone <span class="text-secondary">(optional)</span></label>
                            <input type="text" name="demo_phone" class="form-control @error('demo_phone') is-invalid @enderror" value="{{ old('demo_phone') }}" maxlength="20" placeholder="+923001234567">
                            @error('demo_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Demo Deposit ($)</label>
                            <input type="number" name="demo_deposit" class="form-control @error('demo_deposit') is-invalid @enderror" value="{{ old('demo_deposit', 10000) }}" min="0" max="100000000" step="0.01">
                            @error('demo_deposit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Info</h6></div>
                <div class="card-body">
                    <small class="text-secondary">
                        <p class="mb-2"><i class="bi bi-info-circle me-1"></i> Bot will be created in <strong>Inactive</strong> state.</p>
                        <p class="mb-2"><i class="bi bi-file-earmark-code me-1"></i> Upload the configured bot file.</p>
                        <p class="mb-0"><i class="bi bi-graph-up me-1"></i> Start the bot after verifying all settings.</p>
                    </small>
                </div>
            </div>
            <div class="d-grid"><button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Create Bot</button></div>
        </div>
    </div>
</form>
@endsection
