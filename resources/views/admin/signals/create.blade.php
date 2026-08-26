@extends('layouts.app')

@section('title', 'Create Signal')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-broadcast me-2 text-primary"></i>Create Signal</h4>
    <a href="{{ route('admin.signals.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form method="POST" action="{{ route('admin.signals.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Signal Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="titleInput" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required maxlength="255" placeholder="e.g. BTCUSDT Buy Signal">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="5000" placeholder="Signal analysis and reasoning...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Symbol <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" name="symbol" id="symbolInput" class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol') }}" required maxlength="20" placeholder="Search coin..." autocomplete="off">
                                @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="symbolDropdown" class="position-absolute w-100 shadow-lg border rounded-bottom d-none" style="z-index:1050;background:white;max-height:300px;overflow-y:auto;"></div>
                            </div>
                            <small class="text-secondary">Auto-suggest from Binance live data</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Direction <span class="text-danger">*</span></label>
                            <select name="direction" id="directionSelect" class="form-select @error('direction') is-invalid @enderror" required>
                                <option value="buy" {{ old('direction') === 'buy' ? 'selected' : '' }}>Buy (Long)</option>
                                <option value="sell" {{ old('direction') === 'sell' ? 'selected' : '' }}>Sell (Short)</option>
                            </select>
                            @error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active (Publish Now)</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Entry Price</label>
                            <input type="number" name="entry_price" id="entryPrice" class="form-control @error('entry_price') is-invalid @enderror" value="{{ old('entry_price') }}" step="0.00001" min="0">
                            @error('entry_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Take Profit</label>
                            <input type="number" name="take_profit" id="takeProfit" class="form-control @error('take_profit') is-invalid @enderror" value="{{ old('take_profit') }}" step="0.00001" min="0">
                            @error('take_profit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Stop Loss</label>
                            <input type="number" name="stop_loss" id="stopLoss" class="form-control @error('stop_loss') is-invalid @enderror" value="{{ old('stop_loss') }}" step="0.00001" min="0">
                            @error('stop_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Expires At (Optional)</label>
                            <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label text-secondary" for="isFeatured"><i class="bi bi-star-fill text-warning me-1"></i>Featured Signal</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Live Market Data Panel --}}
            <div class="card mb-4" id="marketDataPanel" style="display:none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-graph-up me-1"></i>Live Market Data</h6>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-info py-0" onclick="openChartPopup()" title="View TradingView Chart"><i class="bi bi-bar-chart-line"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0" onclick="refreshMarketData()"><i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                </div>
                <div class="card-body" id="marketDataContent">
                    <div class="text-center text-secondary py-3">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div class="small">Loading market data...</div>
                    </div>
                </div>
            </div>

            {{-- Auto Suggest Button --}}
            <div class="card mb-4" id="autoSuggestPanel" style="display:none;">
                <div class="card-header"><h6 class="mb-0"><i class="bi bi-magic me-1"></i>Auto Suggest TP/SL</h6></div>
                <div class="card-body">
                    <p class="text-secondary small mb-2">Automatically calculate Take Profit and Stop Loss based on current price and volatility.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="autoSuggestTpSl()">
                        <i class="bi bi-lightning me-1"></i>Generate TP/SL
                    </button>
                    <div id="suggestResult" class="mt-2 small"></div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Categories</h6></div>
                <div class="card-body">
                    @if($categories->count())
                    @foreach($categories as $cat)
                    <div class="form-check mb-2">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="form-check-input" id="cat{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cat{{ $cat->id }}">
                            <span class="badge me-1" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40;">{{ $cat->name }}</span>
                            @if($cat->description)<small class="text-secondary d-block">{{ Str::limit($cat->description, 50) }}</small>@endif
                        </label>
                    </div>
                    @endforeach
                    @else
                    <p class="text-secondary mb-0">No categories yet. <a href="{{ route('admin.signal-categories.create') }}">Create one</a></p>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Publishing</h6></div>
                <div class="card-body">
                    <div class="mb-0">
                        <small class="text-secondary">
                            <strong class="text-dark">Draft:</strong> Not visible to users<br>
                            <strong class="text-dark">Pending:</strong> Scheduled, not yet live<br>
                            <strong class="text-dark">Active:</strong> Published immediately
                        </small>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Create Signal</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let searchTimeout = null;
let selectedSymbol = null;
let lastTicker = null;

const symbolInput = document.getElementById('symbolInput');
const dropdown = document.getElementById('symbolDropdown');

symbolInput?.addEventListener('input', function() {
    const query = this.value.trim();
    clearTimeout(searchTimeout);

    if (query.length < 1) {
        dropdown.classList.add('d-none');
        return;
    }

    searchTimeout = setTimeout(() => {
        fetch(`{{ route('admin.market.search') }}?q=${encodeURIComponent(query)}`)
            .then(r => r.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    dropdown.innerHTML = data.data.map(s => `
                        <div class="px-3 py-2 border-bottom symbol-option" style="cursor:pointer;" data-symbol="${s.symbol}" data-name="${s.name}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-semibold text-dark">${s.base_asset}</span>
                                    <small class="text-secondary ms-1">${s.name}</small>
                                </div>
                                <span class="badge bg-secondary">${s.quote_asset}</span>
                            </div>
                        </div>
                    `).join('');

                    dropdown.querySelectorAll('.symbol-option').forEach(opt => {
                        opt.addEventListener('click', function() {
                            const symbol = this.dataset.symbol;
                            const name = this.dataset.name;
                            symbolInput.value = symbol;
                            document.getElementById('titleInput').value = symbol + ' Signal';
                            dropdown.classList.add('d-none');
                            selectedSymbol = symbol;
                            loadMarketData(symbol);
                        });
                    });

                    dropdown.classList.remove('d-none');
                } else {
                    dropdown.innerHTML = '<div class="px-3 py-2 text-secondary small">No symbols found</div>';
                    dropdown.classList.remove('d-none');
                }
            })
            .catch(() => {
                dropdown.innerHTML = '<div class="px-3 py-2 text-danger small">Search failed. Try again.</div>';
                dropdown.classList.remove('d-none');
            });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('#symbolInput') && !e.target.closest('#symbolDropdown')) {
        dropdown.classList.add('d-none');
    }
});

symbolInput?.addEventListener('blur', function() {
    setTimeout(() => dropdown.classList.add('d-none'), 200);
});

function loadMarketData(symbol) {
    const panel = document.getElementById('marketDataPanel');
    const suggestPanel = document.getElementById('autoSuggestPanel');
    const content = document.getElementById('marketDataContent');

    panel.style.display = 'block';
    suggestPanel.style.display = 'block';
    content.innerHTML = `
        <div class="text-center text-secondary py-3">
            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
            <div class="small">Loading market data...</div>
        </div>
    `;

    fetch(`{{ route('admin.market.overview') }}?symbol=${encodeURIComponent(symbol)}`)
        .then(r => r.json())
        .then(data => {
            if (data.data) {
                const t = data.data.ticker;
                lastTicker = t;

                const trendColors = {
                    'strong_up': '#10b981', 'up': '#10b981',
                    'neutral': '#6b7280',
                    'strong_down': '#ef4444', 'down': '#ef4444'
                };
                const trendLabels = {
                    'strong_up': 'Strong Up', 'up': 'Up',
                    'neutral': 'Neutral',
                    'strong_down': 'Strong Down', 'down': 'Down'
                };
                const trendIcons = {
                    'strong_up': 'bi-arrow-up-right', 'up': 'bi-arrow-up',
                    'neutral': 'bi-dash',
                    'strong_down': 'bi-arrow-down-right', 'down': 'bi-arrow-down'
                };

                const changeClass = t.change_pct_24h >= 0 ? 'text-success' : 'text-danger';
                const changeIcon = t.change_pct_24h >= 0 ? 'bi-arrow-up' : 'bi-arrow-down';

                content.innerHTML = `
                    <div class="text-center mb-3">
                        <h4 class="mb-0 fw-bold" style="color:var(--text-primary);">$${numberFormat(t.price)}</h4>
                        <span class="${changeClass} fw-semibold">
                            <i class="bi ${changeIcon}"></i> ${t.change_pct_24h >= 0 ? '+' : ''}${t.change_pct_24h.toFixed(2)}%
                        </span>
                    </div>
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <small class="text-secondary d-block" style="font-size:0.7rem;">24h High</small>
                            <small class="text-dark fw-medium">$${numberFormat(t.high_24h)}</small>
                        </div>
                        <div class="col-4">
                            <small class="text-secondary d-block" style="font-size:0.7rem;">24h Low</small>
                            <small class="text-dark fw-medium">$${numberFormat(t.low_24h)}</small>
                        </div>
                        <div class="col-4">
                            <small class="text-secondary d-block" style="font-size:0.7rem;">24h Vol</small>
                            <small class="text-dark fw-medium">${formatVolume(t.volume_24h)}</small>
                        </div>
                    </div>
                    ${data.data.support ? `
                    <div class="row g-2 text-center mb-2">
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.7rem;">Support</small>
                            <span class="badge bg-success bg-opacity-10 text-success">$${numberFormat(data.data.support)}</span>
                        </div>
                        <div class="col-6">
                            <small class="text-secondary d-block" style="font-size:0.7rem;">Resistance</small>
                            <span class="badge bg-danger bg-opacity-10 text-danger">$${numberFormat(data.data.resistance)}</span>
                        </div>
                    </div>
                    ` : ''}
                    <div class="text-center mt-2">
                        <span class="badge" style="background:${trendColors[data.data.trend]}20;color:${trendColors[data.data.trend]};border:1px solid ${trendColors[data.data.trend]}40;">
                            <i class="bi ${trendIcons[data.data.trend]}"></i> ${trendLabels[data.data.trend]}
                        </span>
                    </div>
                    <div class="text-center mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="autoFillEntryPrice('${t.price}')">
                            <i class="bi bi-arrow-left-right me-1"></i>Use as Entry Price
                        </button>
                    </div>
                `;
            } else {
                content.innerHTML = '<div class="text-center text-danger py-3 small">Unable to load market data</div>';
            }
        })
        .catch(() => {
            content.innerHTML = '<div class="text-center text-danger py-3 small">Network error. Try again.</div>';
        });
}

function refreshMarketData() {
    if (selectedSymbol) loadMarketData(selectedSymbol);
}

function autoFillEntryPrice(price) {
    document.getElementById('entryPrice').value = parseFloat(price).toFixed(5);
}

function autoSuggestTpSl() {
    if (!lastTicker) return;

    const price = lastTicker.price;
    const high24h = lastTicker.high_24h;
    const low24h = lastTicker.low_24h;
    const range = high24h - low24h;
    const atr = range * 0.6;
    const direction = document.getElementById('directionSelect').value;

    let tp, sl;

    if (direction === 'buy') {
        tp = price + (atr * 1.5);
        sl = price - (atr * 1.0);
    } else {
        tp = price - (atr * 1.5);
        sl = price + (atr * 1.0);
    }

    const decimals = price > 100 ? 2 : (price > 1 ? 4 : 6);

    document.getElementById('takeProfit').value = tp.toFixed(decimals);
    document.getElementById('stopLoss').value = sl.toFixed(decimals);

    const riskReward = ((tp - price) / (price - sl)).toFixed(2);
    const suggestResult = document.getElementById('suggestResult');
    suggestResult.innerHTML = `
        <div class="p-2 rounded" style="background:#f8fafc;">
            <div class="text-success"><strong>TP:</strong> $${numberFormat(tp)}</div>
            <div class="text-danger"><strong>SL:</strong> $${numberFormat(sl)}</div>
            <div class="text-primary mt-1"><strong>Risk:Reward = 1:${riskReward}</strong></div>
        </div>
    `;
}

function numberFormat(num) {
    return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function formatVolume(vol) {
    if (vol >= 1e9) return (vol / 1e9).toFixed(1) + 'B';
    if (vol >= 1e6) return (vol / 1e6).toFixed(1) + 'M';
    if (vol >= 1e3) return (vol / 1e3).toFixed(1) + 'K';
    return vol.toFixed(0);
}

if (symbolInput.value) {
    selectedSymbol = symbolInput.value;
    loadMarketData(symbolInput.value);
}

let chartPopup = null;

function openChartPopup() {
    if (!selectedSymbol) return;

    if (!chartPopup) {
        const modalHtml = `
        <div class="modal fade" id="chartModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title" id="chartModalTitle">
                            <i class="bi bi-graph-up me-1"></i>${selectedSymbol} - TradingView Chart
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-2">
                        <div id="tradingview_chart" style="width:100%;height:450px;"></div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="useCurrentPriceAsEntry()">
                            <i class="bi bi-arrow-left-right me-1"></i>Use Current Price as Entry
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        chartPopup = new bootstrap.Modal(document.getElementById('chartModal'));
    }

    chartPopup.show();
    loadTradingViewChart(selectedSymbol);
}

function loadTradingViewChart(symbol) {
    const container = document.getElementById('tradingview_chart');
    const bsSymbol = 'BINANCE:' + symbol.toUpperCase();

    container.innerHTML = `
        <div class="tradingview-widget-container">
            <div id="tradingview_widget"></div>
        </div>
    `;

    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
    script.async = true;
    script.innerHTML = JSON.stringify({
        "autosize": true,
        "symbol": bsSymbol,
        "interval": "15",
        "timezone": "Etc/UTC",
        "theme": "light",
        "style": "1",
        "locale": "en",
        "backgroundColor": "#ffffff",
        "gridColor": "#f1f5f9",
        "hide_top_toolbar": false,
        "hide_legend": false,
        "save_image": false,
        "hide_volume": false,
        "support_host": "https://www.tradingview.com"
    });

    container.innerHTML = '';
    container.appendChild(script);
}

function useCurrentPriceAsEntry() {
    if (lastTicker) {
        autoFillEntryPrice(lastTicker.price);
        chartPopup.hide();
    }
}
</script>
@endpush
