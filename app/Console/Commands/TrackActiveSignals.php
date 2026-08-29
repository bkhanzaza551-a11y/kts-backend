<?php

namespace App\Console\Commands;

use App\Models\Signal;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TrackActiveSignals extends Command
{
    protected $signature = 'signals:track';
    protected $description = 'Track active signals against live market prices and auto-close WIN/LOSS';

    public function handle()
    {
        $activeSignals = Signal::where('status', 'active')
            ->where('result', 'pending')
            ->get();

        if ($activeSignals->isEmpty()) {
            $this->info('No active signals to track.');
            return 0;
        }

        $this->info("Tracking {$activeSignals->count()} active signals...");

        foreach ($activeSignals as $signal) {
            $this->trackSignal($signal);
        }

        return 0;
    }

    private function trackSignal(Signal $signal)
    {
        $currentPrice = $this->getCurrentPrice($signal->symbol);

        if ($currentPrice === null) {
            $this->warn("  Could not fetch price for {$signal->symbol}");
            return;
        }

        $entry = (float) $signal->entry_price;
        $tp = $signal->take_profit ? (float) $signal->take_profit : null;
        $sl = $signal->stop_loss ? (float) $signal->stop_loss : null;

        if ($entry <= 0) {
            $this->warn("  Invalid entry price for signal #{$signal->id}");
            return;
        }

        $pipsResult = $this->calculatePips($signal->symbol, $signal->direction, $entry, $currentPrice);

        $result = null;
        $shouldClose = false;

        if ($signal->direction === 'buy') {
            if ($tp && $currentPrice >= $tp) {
                $result = 'win';
                $shouldClose = true;
            } elseif ($sl && $currentPrice <= $sl) {
                $result = 'loss';
                $shouldClose = true;
            }
        } else {
            if ($tp && $currentPrice <= $tp) {
                $result = 'win';
                $shouldClose = true;
            } elseif ($sl && $currentPrice >= $sl) {
                $result = 'loss';
                $shouldClose = true;
            }
        }

        if (!$shouldClose && $signal->published_at && $signal->published_at->diffInHours(now()) >= 24) {
            $result = 'breakeven';
            $shouldClose = true;
            $pipsResult = 0;
        }

        if ($shouldClose) {
            $signal->close($result, $pipsResult, $currentPrice);

            $emoji = $result === 'win' ? 'WIN' : ($result === 'loss' ? 'LOSS' : 'BREAKEVEN');
            $this->info("  [{$emoji}] Signal #{$signal->id} ({$signal->symbol}) CLOSED | Entry: {$entry} | Close: {$currentPrice} | Pips: {$pipsResult}");

            ActivityLogger::log('signal_auto_close', 'Signal', $signal->id, "Auto-closed as {$result} at {$currentPrice} ({$pipsResult} pips)");

            // In-app notification
            $resultEmoji = $result === 'win' ? '✅' : ($result === 'loss' ? '❌' : '⏰');
            NotificationService::send('signal_closed', [
                'title' => "{$resultEmoji} Signal Closed: {$signal->symbol}",
                'body' => "Your {$signal->direction} signal for {$signal->symbol} closed as " . strtoupper($result) . ". {$pipsResult} pips.",
                'type' => $result === 'win' ? 'success' : ($result === 'loss' ? 'danger' : 'info'),
                'target' => 'all',
            ]);

            // Push notification
            PushNotificationService::sendToAll(
                "Signal {$resultEmoji} " . strtoupper($result),
                "{$signal->symbol} {$signal->direction} signal closed at {$currentPrice}. {$pipsResult} pips",
                ['signal_id' => $signal->id, 'result' => $result, 'type' => 'signal_closed']
            );
        } else {
            $pnl = $signal->direction === 'buy'
                ? (($currentPrice - $entry) / $entry * 100)
                : (($entry - $currentPrice) / $entry * 100);

            $this->line("  [LIVE] Signal #{$signal->id} ({$signal->symbol}) Entry: {$entry} | Current: {$currentPrice} | PnL: " . number_format($pnl, 2) . "%");
        }
    }

    private function getCurrentPrice(string $symbol): ?float
    {
        $cacheKey = 'signal_price_' . strtoupper(str_replace('/', '', $symbol));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $symbolUpper = strtoupper($symbol);
        $isCrypto = $this->isCrypto($symbolUpper);
        $price = null;

        // Source 1: TwelveData (forex + crypto + commodities) — best all-round free API
        $price = $this->fetchTwelveData($symbol, $symbolUpper);
        if ($price !== null) {
            Cache::put($cacheKey, $price, 60);
            return $price;
        }

        // Source 2: Binance (crypto only)
        if ($isCrypto) {
            $price = $this->fetchBinance($symbolUpper);
            if ($price !== null) {
                Cache::put($cacheKey, $price, 60);
                return $price;
            }
        }

        // Source 3: CoinGecko (crypto — BTC, ETH, SOL, etc.)
        if ($isCrypto) {
            $price = $this->fetchCoinGecko($symbolUpper);
            if ($price !== null) {
                Cache::put($cacheKey, $price, 60);
                return $price;
            }
        }

        // Source 4: Yahoo Finance (everything — forex, crypto, commodities)
        $price = $this->fetchYahoo($symbolUpper, $isCrypto);
        if ($price !== null) {
            Cache::put($cacheKey, $price, 60);
            return $price;
        }

        return null;
    }

    private function isCrypto(string $symbol): bool
    {
        $cryptoPairs = ['BTC', 'ETH', 'BNB', 'SOL', 'XRP', 'ADA', 'DOGE', 'DOT', 'AVAX', 'MATIC', 'LINK', 'UNI', 'SHIB', 'LTC', 'ATOM', 'NEAR', 'FTM', 'APE', 'ARB', 'OP'];
        foreach ($cryptoPairs as $coin) {
            if (str_contains($symbol, $coin)) {
                return true;
            }
        }
        return false;
    }

    private function formatForTwelveData(string $symbolUpper): string
    {
        // EURUSD -> EUR/USD, XAUUSD -> XAU/USD, BTCUSDT -> BTC/USDT
        $forexPairs = [
            'EURUSD' => 'EUR/USD', 'GBPUSD' => 'GBP/USD', 'USDJPY' => 'USD/JPY',
            'USDCHF' => 'USD/CHF', 'AUDUSD' => 'AUD/USD', 'USDCAD' => 'USD/CAD',
            'NZDUSD' => 'NZD/USD', 'EURGBP' => 'EUR/GBP', 'EURJPY' => 'EUR/JPY',
            'GBPJPY' => 'GBP/JPY', 'AUDJPY' => 'AUD/JPY', 'EURAUD' => 'EUR/AUD',
            'EURCHF' => 'EUR/CHF', 'GBPAUD' => 'GBP/AUD', 'AUDNZD' => 'AUD/NZD',
            'USDCNH' => 'USD/CNH', 'USDTRY' => 'USD/TRY', 'USDZAR' => 'USD/ZAR',
            'USDMXN' => 'USD/MXN', 'USDINR' => 'USD/INR', 'USDPLN' => 'USD/PLN',
            'USDSEK' => 'USD/SEK', 'USDNOK' => 'USD/NOK', 'USDDKK' => 'USD/DKK',
            'USDHKD' => 'USD/HKD', 'USDSGD' => 'USD/SGD', 'USDTHB' => 'USD/THB',
        ];
        if (isset($forexPairs[$symbolUpper])) {
            return $forexPairs[$symbolUpper];
        }
        // Commodities
        $commodities = ['XAUUSD' => 'XAU/USD', 'XAGUSD' => 'XAG/USD', 'XAUEUR' => 'XAU/EUR'];
        if (isset($commodities[$symbolUpper])) {
            return $commodities[$symbolUpper];
        }
        // Crypto: BTCUSDT -> BTC/USDT
        if (str_ends_with($symbolUpper, 'USDT')) {
            return substr($symbolUpper, 0, -4) . '/USDT';
        }
        if (str_ends_with($symbolUpper, 'USD')) {
            return substr($symbolUpper, 0, -3) . '/USD';
        }
        return $symbolUpper;
    }

    private function formatForYahoo(string $symbolUpper, bool $isCrypto): string
    {
        // Forex: EURUSD -> EURUSD=X
        $forexPairs = [
            'EURUSD', 'GBPUSD', 'USDJPY', 'USDCHF', 'AUDUSD', 'USDCAD',
            'NZDUSD', 'EURGBP', 'EURJPY', 'GBPJPY', 'AUDJPY', 'EURAUD',
            'EURCHF', 'GBPAUD', 'AUDNZD', 'USDCNH', 'USDTRY', 'USDZAR',
            'USDMXN', 'USDINR', 'USDPLN', 'USDSEK', 'USDNOK', 'USDDKK',
            'USDHKD', 'USDSGD', 'USDTHB',
        ];
        if (in_array($symbolUpper, $forexPairs)) {
            return $symbolUpper . '=X';
        }
        // Commodities: XAUUSD -> GC=F (gold futures)
        $commodityMap = [
            'XAUUSD' => 'GC=F',   // Gold
            'XAGUSD' => 'SI=F',   // Silver
        ];
        if (isset($commodityMap[$symbolUpper])) {
            return $commodityMap[$symbolUpper];
        }
        // Crypto: BTCUSDT -> BTC-USD
        if ($isCrypto) {
            if (str_ends_with($symbolUpper, 'USDT')) {
                return substr($symbolUpper, 0, -4) . '-USD';
            }
            if (str_ends_with($symbolUpper, 'USD')) {
                return substr($symbolUpper, 0, -3) . '-USD';
            }
            return $symbolUpper . '-USD';
        }
        return $symbolUpper;
    }

    private function fetchTwelveData(string $symbol, string $symbolUpper): ?float
    {
        $apiKey = env('TWELVEDATA_API_KEY', '');
        if (empty($apiKey)) {
            return null;
        }

        $tdSymbol = $this->formatForTwelveData($symbolUpper);

        try {
            $response = Http::timeout(8)->get("https://api.twelvedata.com/price", [
                'symbol' => $tdSymbol,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['price']) && is_numeric($data['price'])) {
                    return (float) $data['price'];
                }
            }
        } catch (\Exception $e) {
            // continue
        }

        return null;
    }

    private function fetchBinance(string $symbolUpper): ?float
    {
        $binanceSymbol = str_replace('/', '', $symbolUpper);
        $urls = [
            "https://api1.binance.com/api/v3/ticker/price?symbol={$binanceSymbol}",
            "https://api2.binance.com/api/v3/ticker/price?symbol={$binanceSymbol}",
            "https://api3.binance.com/api/v3/ticker/price?symbol={$binanceSymbol}",
        ];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(5)->get($url);
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['price']) && is_numeric($data['price'])) {
                        return (float) $data['price'];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    private function fetchCoinGecko(string $symbolUpper): ?float
    {
        $coinMap = [
            'BTCUSDT' => 'bitcoin', 'BTCUSD' => 'bitcoin',
            'ETHUSDT' => 'ethereum', 'ETHUSD' => 'ethereum',
            'BNBUSDT' => 'binancecoin', 'BNBUSD' => 'binancecoin',
            'SOLUSDT' => 'solana', 'SOLUSD' => 'solana',
            'XRPUSDT' => 'ripple', 'XRPUSD' => 'ripple',
            'ADAUSDT' => 'cardano', 'ADAUSD' => 'cardano',
            'DOGEUSDT' => 'dogecoin', 'DOGEUSD' => 'dogecoin',
            'DOTUSDT' => 'polkadot', 'DOTUSD' => 'polkadot',
            'AVAXUSDT' => 'avalanche-2', 'AVAXUSD' => 'avalanche-2',
            'MATICUSDT' => 'matic-network', 'MATICUSD' => 'matic-network',
            'LINKUSDT' => 'chainlink', 'LINKUSD' => 'chainlink',
            'LTCUSDT' => 'litecoin', 'LTCUSD' => 'litecoin',
            'ATOMUSDT' => 'cosmos', 'ATOMUSD' => 'cosmos',
        ];

        if (!isset($coinMap[$symbolUpper])) {
            return null;
        }

        try {
            $response = Http::timeout(8)->get(
                "https://api.coingecko.com/api/v3/simple/price",
                ['ids' => $coinMap[$symbolUpper], 'vs_currencies' => 'usd']
            );

            if ($response->successful()) {
                $data = $response->json();
                $coinId = $coinMap[$symbolUpper];
                if (isset($data[$coinId]['usd'])) {
                    return (float) $data[$coinId]['usd'];
                }
            }
        } catch (\Exception $e) {
            // continue
        }

        return null;
    }

    private function fetchYahoo(string $symbolUpper, bool $isCrypto): ?float
    {
        $yahooSymbol = $this->formatForYahoo($symbolUpper, $isCrypto);

        try {
            $response = Http::timeout(8)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->get("https://query1.finance.yahoo.com/v8/finance/chart/{$yahooSymbol}", [
                'interval' => '1m',
                'range' => '1d',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $meta = $data['chart']['result'][0]['meta'] ?? null;
                if ($meta && isset($meta['regularMarketPrice'])) {
                    return (float) $meta['regularMarketPrice'];
                }
            }
        } catch (\Exception $e) {
            // continue
        }

        return null;
    }

    private function calculatePips(string $symbol, string $direction, float $entry, float $current): float
    {
        $symbolUpper = strtoupper($symbol);

        if (str_contains($symbolUpper, 'JPY')) {
            $diff = $direction === 'buy' ? $current - $entry : $entry - $current;
            return round($diff * 100, 1);
        }

        if ($this->isCrypto($symbolUpper)) {
            $diff = $direction === 'buy' ? $current - $entry : $entry - $current;
            return round($diff, 2);
        }

        $diff = $direction === 'buy' ? $current - $entry : $entry - $current;
        return round($diff * 10000, 1);
    }
}
