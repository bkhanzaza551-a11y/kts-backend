@extends('layouts.app')

@section('title', 'Edit Signal - ' . $signal->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold"><i class="bi bi-broadcast me-2 text-primary"></i>Edit Signal</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.signals.show', $signal) }}" class="btn btn-outline-info">View</a>
        <a href="{{ route('admin.signals.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<form method="POST" action="{{ route('admin.signals.update', $signal) }}">
    @csrf
    @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">Signal Details</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $signal->title) }}" required maxlength="255">
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="5000">{{ old('description', $signal->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Symbol <span class="text-danger">*</span></label>
                            <input type="text" name="symbol" class="form-control @error('symbol') is-invalid @enderror" value="{{ old('symbol', $signal->symbol) }}" required maxlength="20">
                            @error('symbol')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Direction <span class="text-danger">*</span></label>
                            <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                                <option value="buy" {{ old('direction', $signal->direction) === 'buy' ? 'selected' : '' }}>Buy (Long)</option>
                                <option value="sell" {{ old('direction', $signal->direction) === 'sell' ? 'selected' : '' }}>Sell (Short)</option>
                            </select>
                            @error('direction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(['draft','pending','active','closed','cancelled'] as $s)
                                <option value="{{ $s }}" {{ old('status', $signal->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Entry Price</label>
                            <input type="number" name="entry_price" class="form-control @error('entry_price') is-invalid @enderror" value="{{ old('entry_price', $signal->entry_price) }}" step="0.00001" min="0">
                            @error('entry_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Take Profit</label>
                            <input type="number" name="take_profit" class="form-control @error('take_profit') is-invalid @enderror" value="{{ old('take_profit', $signal->take_profit) }}" step="0.00001" min="0">
                            @error('take_profit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Stop Loss</label>
                            <input type="number" name="stop_loss" class="form-control @error('stop_loss') is-invalid @enderror" value="{{ old('stop_loss', $signal->stop_loss) }}" step="0.00001" min="0">
                            @error('stop_loss')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    @if($signal->status === 'closed')
                    <div class="row g-3 mt-1 p-3 bg-light rounded border">
                        <div class="col-12"><h6 class="text-warning mb-0">Close Details</h6></div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Result <span class="text-danger">*</span></label>
                            <select name="result" class="form-select" required>
                                <option value="win" {{ old('result', $signal->result) === 'win' ? 'selected' : '' }}>Win</option>
                                <option value="loss" {{ old('result', $signal->result) === 'loss' ? 'selected' : '' }}>Loss</option>
                                <option value="breakeven" {{ old('result', $signal->result) === 'breakeven' ? 'selected' : '' }}>Breakeven</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Pips Result</label>
                            <input type="number" name="pips_result" class="form-control" value="{{ old('pips_result', $signal->pips_result) }}" step="0.1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Close Price</label>
                            <input type="number" name="close_price" class="form-control" value="{{ old('close_price', $signal->close_price) }}" step="0.00001" min="0">
                        </div>
                    </div>
                    @endif

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Expires At</label>
                            <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at', $signal->expires_at?->format('Y-m-d\TH:i')) }}">
                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_featured" value="0">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured" value="1" {{ old('is_featured', $signal->is_featured) ? 'checked' : '' }}>
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
                        <button type="button" class="btn btn-sm btn-outline-info py-0" onclick="openChartPopup()" title="View Chart"><i class="bi bi-bar-chart-line"></i></button>
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

            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0">Categories</h6></div>
                <div class="card-body">
                    @foreach($categories as $cat)
                    <div class="form-check mb-2">
                        <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="form-check-input" id="cat{{ $cat->id }}" {{ in_array($cat->id, old('categories', $selectedCategories)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="cat{{ $cat->id }}">
                            <span class="badge me-1" style="background-color: {{ $cat->color }}20; color: {{ $cat->color }}; border: 1px solid {{ $cat->color }}40;">{{ $cat->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <small class="text-secondary d-block">Created</small>
                            <small class="text-dark">{{ $signal->created_at->format('M d') }}</small>
                        </div>
                        <div class="col-4">
                            <small class="text-secondary d-block">Published</small>
                            <small class="text-dark">{{ $signal->published_at?->format('M d') ?? 'N/A' }}</small>
                        </div>
                        <div class="col-4">
                            <small class="text-secondary d-block">Views</small>
                            <small class="text-dark">{{ number_format($signal->views_count) }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-check-lg me-1"></i>Update Signal</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let selectedSymbol = '{{ old('symbol', $signal->symbol) }}';
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let searchTimeout;

if (selectedSymbol) {
    document.getElementById('marketDataPanel').style.display = 'block';
    loadMarketData(selectedSymbol);
}

document.querySelectorAll('input[name="symbol"]').forEach(function(el) {
    el.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 2) return;
        searchTimeout = setTimeout(() => {
            document.getElementById('marketDataPanel').style.display = 'block';
            selectedSymbol = query;
            loadMarketData(query);
        }, 500);
    });
});

function loadMarketData(symbol) {
    const panel = document.getElementById('marketDataPanel');
    const content = document.getElementById('marketDataContent');
    panel.style.display = 'block';
    content.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><div class="small text-secondary mt-1">Loading...</div></div>';

    fetch('{{ url("admin/market/overview") }}?symbol=' + encodeURIComponent(symbol), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.data || !data.data.ticker) {
            content.innerHTML = '<div class="text-center text-secondary py-3 small">No data found for this symbol.</div>';
            return;
        }
        var t = data.data.ticker;
        var trendIcons = { strong_up: '🟢🟢', up: '🟢', neutral: '🟡', down: '🔴', strong_down: '🔴🔴' };
        var trendColors = { strong_up: '#10b981', up: '#10b981', neutral: '#f59e0b', down: '#ef4444', strong_down: '#ef4444' };
        var c = trendColors[data.data.trend] || '#6b7280';

        content.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
                '<h6 class="mb-0 fw-bold" style="color:' + c + '">' + t.symbol + '</h6>' +
                '<span class="badge" style="background:' + c + '20;color:' + c + ';border:1px solid ' + c + '40">' + (trendIcons[data.data.trend] || '') + ' ' + data.data.trend.replace('_', ' ').toUpperCase() + '</span>' +
            '</div>' +
            '<h4 class="fw-bold mb-2" style="color:' + c + '">$' + numberFormat(t.price) + '</h4>' +
            '<div class="row g-2 mb-2">' +
                '<div class="col-6"><small class="text-secondary d-block">24h Change</small><strong style="color:' + (t.change_pct_24h >= 0 ? '#10b981' : '#ef4444') + '">' + (t.change_pct_24h >= 0 ? '+' : '') + t.change_pct_24h.toFixed(2) + '%</strong></div>' +
                '<div class="col-6"><small class="text-secondary d-block">24h High</small><strong class="text-success">' + numberFormat(t.high_24h) + '</strong></div>' +
                '<div class="col-6"><small class="text-secondary d-block">24h Low</small><strong class="text-danger">' + numberFormat(t.low_24h) + '</strong></div>' +
                '<div class="col-6"><small class="text-secondary d-block">Volume</small><strong>' + formatVolume(t.volume_24h) + '</strong></div>' +
                '<div class="col-6"><small class="text-secondary d-block">Support</small><strong class="text-info">' + (data.data.support ? '$' + numberFormat(data.data.support) : 'N/A') + '</strong></div>' +
                '<div class="col-6"><small class="text-secondary d-block">Resistance</small><strong class="text-warning">' + (data.data.resistance ? '$' + numberFormat(data.data.resistance) : 'N/A') + '</strong></div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-success w-100 mt-1" onclick="autoFillEntryPrice(' + t.price + ')"><i class="bi bi-arrow-left-right me-1"></i>Use as Entry Price</button>';
    })
    .catch(function() {
        content.innerHTML = '<div class="text-center text-danger py-3 small">Network error. Try again.</div>';
    });
}

function refreshMarketData() { if (selectedSymbol) loadMarketData(selectedSymbol); }
function autoFillEntryPrice(price) { document.querySelector('input[name="entry_price"]').value = parseFloat(price).toFixed(5); }
function numberFormat(num) { return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function formatVolume(vol) { if (vol >= 1e9) return (vol/1e9).toFixed(1)+'B'; if (vol >= 1e6) return (vol/1e6).toFixed(1)+'M'; if (vol >= 1e3) return (vol/1e3).toFixed(1)+'K'; return vol.toFixed(0); }

var chartPopupEdit = null;
var chartInstanceEdit = null;

function openChartPopup() {
    if (!selectedSymbol) return;
    if (!chartPopupEdit) {
        var modalHtml = '<div class="modal fade" id="chartModalEdit" tabindex="-1" aria-hidden="true">' +
            '<div class="modal-dialog modal-lg modal-dialog-centered">' +
            '<div class="modal-content">' +
            '<div class="modal-header py-2">' +
            '<h6 class="modal-title" id="chartModalTitleEdit">Loading...</h6>' +
            '<button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
            '</div>' +
            '<div class="modal-body p-3">' +
            '<div class="d-flex justify-content-between align-items-center mb-2">' +
            '<div class="btn-group btn-group-sm" id="chartIntervalsEdit">' +
            '<button class="btn btn-outline-primary active" data-interval="15m" data-limit="48">15M</button>' +
            '<button class="btn btn-outline-primary" data-interval="1h" data-limit="48">1H</button>' +
            '<button class="btn btn-outline-primary" data-interval="4h" data-limit="48">4H</button>' +
            '<button class="btn btn-outline-primary" data-interval="1d" data-limit="30">1D</button>' +
            '<button class="btn btn-outline-primary" data-interval="1w" data-limit="20">1W</button>' +
            '</div>' +
            '<div class="text-end" id="chartPriceInfoEdit"></div>' +
            '</div>' +
            '<div style="height:350px;position:relative;"><canvas id="priceChartEdit"></canvas></div>' +
            '<div class="row mt-2" id="chartStatsEdit"></div>' +
            '</div></div></div></div>';
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        chartPopupEdit = new bootstrap.Modal(document.getElementById('chartModalEdit'));
        document.getElementById('chartIntervalsEdit').addEventListener('click', function(e) {
            var btn = e.target.closest('[data-interval]');
            if (!btn) return;
            this.querySelectorAll('.btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            loadChart(selectedSymbol, btn.dataset.interval, parseInt(btn.dataset.limit));
        });
    }
    chartPopupEdit.show();
    loadChart(selectedSymbol, '15m', 48);
}

function loadChart(symbol, interval, limit) {
    var canvas = document.getElementById('priceChartEdit');
    var title = document.getElementById('chartModalTitleEdit');
    var priceInfo = document.getElementById('chartPriceInfoEdit');
    var statsDiv = document.getElementById('chartStatsEdit');
    title.innerHTML = '<i class="bi bi-graph-up me-1"></i>' + symbol + ' Price Chart';
    canvas.style.opacity = '0.3';

    fetch('{{ url("admin/market/klines") }}?symbol=' + encodeURIComponent(symbol) + '&interval=' + interval + '&limit=' + limit)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        canvas.style.opacity = '1';
        if (!data.data || data.data.length === 0) {
            title.innerHTML = '<i class="bi bi-graph-up me-1"></i>' + symbol + ' — No Data';
            return;
        }
        var klines = data.data;
        var labels = klines.map(function(k) {
            var d = new Date(k.open_time);
            if (interval === '15m' || interval === '1h') return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            if (interval === '4h') return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit' });
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });
        var closes = klines.map(function(k) { return k.close; });
        var highs = klines.map(function(k) { return k.high; });
        var lows = klines.map(function(k) { return k.low; });
        var volumes = klines.map(function(k) { return k.volume; });
        var lastPrice = closes[closes.length - 1];
        var firstPrice = closes[0];
        var changePct = ((lastPrice - firstPrice) / firstPrice * 100).toFixed(2);
        var color = changePct >= 0 ? '#10b981' : '#ef4444';

        priceInfo.innerHTML = '<span class="fw-bold fs-5" style="color:' + color + '">$' + numberFormat(lastPrice) + '</span>' +
            '<span class="ms-2 badge" style="background:' + color + '20;color:' + color + ';border:1px solid ' + color + '40">' + (changePct >= 0 ? '+' : '') + changePct + '%</span>';

        var maxHigh = Math.max.apply(null, highs);
        var minLow = Math.min.apply(null, lows);
        var avgVol = volumes.reduce(function(a, b) { return a + b; }, 0) / volumes.length;

        statsDiv.innerHTML =
            '<div class="col-3"><small class="text-secondary d-block">High</small><strong class="text-success">' + numberFormat(maxHigh) + '</strong></div>' +
            '<div class="col-3"><small class="text-secondary d-block">Low</small><strong class="text-danger">' + numberFormat(minLow) + '</strong></div>' +
            '<div class="col-3"><small class="text-secondary d-block">Range</small><strong>' + numberFormat(maxHigh - minLow) + '</strong></div>' +
            '<div class="col-3"><small class="text-secondary d-block">Avg Vol</small><strong>' + formatVolume(avgVol) + '</strong></div>';

        if (chartInstanceEdit) chartInstanceEdit.destroy();

        var ctx = canvas.getContext('2d');
        var gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, color + '40');
        gradient.addColorStop(1, color + '00');

        chartInstanceEdit = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Price', data: closes, borderColor: color, backgroundColor: gradient,
                    borderWidth: 2, fill: true, tension: 0.3, pointRadius: 0, pointHoverRadius: 5, pointHoverBackgroundColor: color
                }, {
                    label: 'High', data: highs, borderColor: '#10b98140', borderWidth: 1, borderDash: [4, 4], fill: false, tension: 0.3, pointRadius: 0
                }, {
                    label: 'Low', data: lows, borderColor: '#ef444440', borderWidth: 1, borderDash: [4, 4], fill: false, tension: 0.3, pointRadius: 0
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', titleColor: '#94a3b8', bodyColor: '#f1f5f9',
                        borderColor: '#334155', borderWidth: 1, padding: 10,
                        callbacks: { label: function(ctx) { return ctx.dataset.label + ': $' + numberFormat(ctx.parsed.y); } }
                    }
                },
                scales: {
                    x: { grid: { color: '#e2e8f020' }, ticks: { color: '#94a3b8', maxTicksLimit: 10, font: { size: 11 } } },
                    y: { position: 'right', grid: { color: '#e2e8f020' }, ticks: { color: '#94a3b8', font: { size: 11 }, callback: function(v) { return '$' + numberFormat(v); } } }
                }
            }
        });
    })
    .catch(function() {
        canvas.style.opacity = '1';
        title.innerHTML = '<i class="bi bi-graph-up me-1"></i>' + symbol + ' — Error';
    });
}
</script>
@endpush
