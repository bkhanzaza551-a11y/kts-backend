<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketDataService
{
    private array $baseUrls = [
        'https://api.binance.com/api/v3',
        'https://api1.binance.com/api/v3',
        'https://data-api.binance.vision/api/v3',
        'https://api.binance.us/api/v3',
    ];

    private function fetchWithFallback(string $endpoint, array $params = [], int $timeout = 2): ?array
    {
        foreach ($this->baseUrls as $baseUrl) {
            try {
                $response = Http::timeout($timeout)
                    ->withoutVerifying()
                    ->withHeaders(['Accept' => 'application/json'])
                    ->get($baseUrl . $endpoint, $params);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return null;
    }

    public function getAllSymbols(): array
    {
        return $this->getCatalogSymbols();
    }

    public function searchSymbols(string $query, int $limit = 20): array
    {
        $allSymbols = $this->getCatalogSymbols();
        $query = strtoupper(trim($query));

        if (empty($query)) {
            return array_slice($allSymbols, 0, $limit);
        }

        $results = array_filter($allSymbols, function ($symbol) use ($query) {
            return str_starts_with($symbol['symbol'], $query)
                || str_starts_with($symbol['base_asset'], $query)
                || str_contains(strtoupper($symbol['name']), $query)
                || str_contains($symbol['symbol'], $query);
        });

        return array_values(array_slice($results, 0, $limit));
    }

    public function getTicker(string $symbol): ?array
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = "binance_ticker_" . $symbol;

        return Cache::remember($cacheKey, 20, function () use ($symbol) {
            // Check Binance API for crypto symbols
            $data = $this->fetchWithFallback('/ticker/24hr', ['symbol' => $symbol], 2);

            if ($data && isset($data['lastPrice'])) {
                return [
                    'symbol' => $data['symbol'],
                    'price' => (float) $data['lastPrice'],
                    'change_24h' => (float) ($data['priceChange'] ?? 0),
                    'change_pct_24h' => (float) ($data['priceChangePercent'] ?? 0),
                    'high_24h' => (float) ($data['highPrice'] ?? 0),
                    'low_24h' => (float) ($data['lowPrice'] ?? 0),
                    'volume_24h' => (float) ($data['volume'] ?? 0),
                    'quote_volume_24h' => (float) ($data['quoteVolume'] ?? 0),
                    'open_price' => (float) ($data['openPrice'] ?? 0),
                    'bid_price' => (float) ($data['bidPrice'] ?? 0),
                    'ask_price' => (float) ($data['askPrice'] ?? 0),
                    'weighted_avg_price' => (float) ($data['weightedAvgPrice'] ?? 0),
                ];
            }

            // Fallback for Forex / Metals / Indices or when API is unreachable
            return $this->getFallbackTicker($symbol);
        });
    }

    public function getPrice(string $symbol): ?float
    {
        $ticker = $this->getTicker($symbol);
        return $ticker ? $ticker['price'] : null;
    }

    public function getKlines(string $symbol, string $interval = '1h', int $limit = 24): array
    {
        $symbol = strtoupper(trim($symbol));
        $cacheKey = "binance_klines_" . $symbol . "_{$interval}_{$limit}";

        return Cache::remember($cacheKey, 60, function () use ($symbol, $interval, $limit) {
            $data = $this->fetchWithFallback('/klines', [
                'symbol' => $symbol,
                'interval' => $interval,
                'limit' => $limit,
            ], 2);

            if (!is_array($data)) {
                return [];
            }

            return array_map(function ($kline) {
                return [
                    'open_time' => $kline[0],
                    'open' => (float) $kline[1],
                    'high' => (float) $kline[2],
                    'low' => (float) $kline[3],
                    'close' => (float) $kline[4],
                    'volume' => (float) $kline[5],
                    'close_time' => $kline[6],
                    'quote_volume' => (float) $kline[7],
                    'trades' => (int) $kline[8],
                ];
            }, $data);
        });
    }

    public function getMarketOverview(string $symbol): ?array
    {
        $ticker = $this->getTicker($symbol);
        if (!$ticker) return null;

        $klines = $this->getKlines($symbol, '1h', 24);

        $support = null;
        $resistance = null;
        if (!empty($klines)) {
            $lows = array_column($klines, 'low');
            $highs = array_column($klines, 'high');
            $support = min($lows);
            $resistance = max($highs);
        } else {
            // Simulated support/resistance around current price
            $support = round($ticker['price'] * 0.985, 4);
            $resistance = round($ticker['price'] * 1.015, 4);
        }

        $trend = 'neutral';
        if ($ticker['change_pct_24h'] > 2) $trend = 'strong_up';
        elseif ($ticker['change_pct_24h'] > 0) $trend = 'up';
        elseif ($ticker['change_pct_24h'] < -2) $trend = 'strong_down';
        elseif ($ticker['change_pct_24h'] < 0) $trend = 'down';

        return [
            'ticker' => $ticker,
            'support' => $support,
            'resistance' => $resistance,
            'trend' => $trend,
            'klines_count' => count($klines),
            'avg_volume' => !empty($klines) ? array_sum(array_column($klines, 'volume')) / count($klines) : ($ticker['volume_24h'] ?? 0),
        ];
    }

    private function getFallbackTicker(string $symbol): array
    {
        $defaults = [
            'BTCUSDT' => ['price' => 64500.00, 'high_24h' => 65200.00, 'low_24h' => 63800.00, 'change_pct_24h' => 1.85, 'volume_24h' => 28540],
            'ETHUSDT' => ['price' => 3450.00, 'high_24h' => 3520.00, 'low_24h' => 3380.00, 'change_pct_24h' => 2.10, 'volume_24h' => 184500],
            'SOLUSDT' => ['price' => 155.00, 'high_24h' => 158.50, 'low_24h' => 151.20, 'change_pct_24h' => 3.40, 'volume_24h' => 1250000],
            'BNBUSDT' => ['price' => 580.00, 'high_24h' => 588.00, 'low_24h' => 572.00, 'change_pct_24h' => 0.85, 'volume_24h' => 95000],
            'XRPUSDT' => ['price' => 0.5850, 'high_24h' => 0.5980, 'low_24h' => 0.5720, 'change_pct_24h' => 1.20, 'volume_24h' => 45000000],
            'DOGEUSDT' => ['price' => 0.1250, 'high_24h' => 0.1310, 'low_24h' => 0.1210, 'change_pct_24h' => 4.50, 'volume_24h' => 65000000],
            'ADAUSDT' => ['price' => 0.3850, 'high_24h' => 0.3950, 'low_24h' => 0.3750, 'change_pct_24h' => 0.65, 'volume_24h' => 25000000],
            'AVAXUSDT' => ['price' => 28.50, 'high_24h' => 29.80, 'low_24h' => 27.90, 'change_pct_24h' => 2.20, 'volume_24h' => 350000],
            'LINKUSDT' => ['price' => 14.50, 'high_24h' => 15.10, 'low_24h' => 14.00, 'change_pct_24h' => 1.50, 'volume_24h' => 850000],
            // Forex & Metals
            'XAUUSD' => ['price' => 2510.50, 'high_24h' => 2525.00, 'low_24h' => 2498.00, 'change_pct_24h' => 0.65, 'volume_24h' => 150000],
            'XAGUSD' => ['price' => 28.80, 'high_24h' => 29.20, 'low_24h' => 28.40, 'change_pct_24h' => 0.90, 'volume_24h' => 450000],
            'EURUSD' => ['price' => 1.0850, 'high_24h' => 1.0890, 'low_24h' => 1.0820, 'change_pct_24h' => 0.15, 'volume_24h' => 950000],
            'GBPUSD' => ['price' => 1.2950, 'high_24h' => 1.3010, 'low_24h' => 1.2910, 'change_pct_24h' => 0.25, 'volume_24h' => 750000],
            'USDJPY' => ['price' => 145.20, 'high_24h' => 146.10, 'low_24h' => 144.80, 'change_pct_24h' => -0.30, 'volume_24h' => 820000],
            'AUDUSD' => ['price' => 0.6720, 'high_24h' => 0.6760, 'low_24h' => 0.6690, 'change_pct_24h' => 0.40, 'volume_24h' => 540000],
            'USDCAD' => ['price' => 1.3550, 'high_24h' => 1.3590, 'low_24h' => 1.3510, 'change_pct_24h' => -0.10, 'volume_24h' => 480000],
            'USDCHF' => ['price' => 0.8520, 'high_24h' => 0.8560, 'low_24h' => 0.8490, 'change_pct_24h' => -0.05, 'volume_24h' => 320000],
            'US30' => ['price' => 40850.00, 'high_24h' => 41100.00, 'low_24h' => 40600.00, 'change_pct_24h' => 0.75, 'volume_24h' => 1200000],
            'NAS100' => ['price' => 19250.00, 'high_24h' => 19450.00, 'low_24h' => 19100.00, 'change_pct_24h' => 1.15, 'volume_24h' => 1800000],
        ];

        $def = $defaults[$symbol] ?? [
            'price' => 100.00,
            'high_24h' => 102.50,
            'low_24h' => 98.00,
            'change_pct_24h' => 0.50,
            'volume_24h' => 50000,
        ];

        return [
            'symbol' => $symbol,
            'price' => (float) $def['price'],
            'change_24h' => round($def['price'] * ($def['change_pct_24h'] / 100), 4),
            'change_pct_24h' => (float) $def['change_pct_24h'],
            'high_24h' => (float) $def['high_24h'],
            'low_24h' => (float) $def['low_24h'],
            'volume_24h' => (float) $def['volume_24h'],
            'quote_volume_24h' => round($def['volume_24h'] * $def['price'], 2),
            'open_price' => round($def['price'] / (1 + ($def['change_pct_24h'] / 100)), 4),
            'bid_price' => round($def['price'] * 0.9999, 4),
            'ask_price' => round($def['price'] * 1.0001, 4),
            'weighted_avg_price' => (float) $def['price'],
        ];
    }

    private function getCatalogSymbols(): array
    {
        return [
            // Top Crypto USDT
            ['symbol' => 'BTCUSDT', 'base_asset' => 'BTC', 'quote_asset' => 'USDT', 'name' => 'Bitcoin', 'full_name' => 'Bitcoin / USDT'],
            ['symbol' => 'ETHUSDT', 'base_asset' => 'ETH', 'quote_asset' => 'USDT', 'name' => 'Ethereum', 'full_name' => 'Ethereum / USDT'],
            ['symbol' => 'SOLUSDT', 'base_asset' => 'SOL', 'quote_asset' => 'USDT', 'name' => 'Solana', 'full_name' => 'Solana / USDT'],
            ['symbol' => 'BNBUSDT', 'base_asset' => 'BNB', 'quote_asset' => 'USDT', 'name' => 'BNB', 'full_name' => 'BNB / USDT'],
            ['symbol' => 'XRPUSDT', 'base_asset' => 'XRP', 'quote_asset' => 'USDT', 'name' => 'XRP', 'full_name' => 'XRP / USDT'],
            ['symbol' => 'DOGEUSDT', 'base_asset' => 'DOGE', 'quote_asset' => 'USDT', 'name' => 'Dogecoin', 'full_name' => 'Dogecoin / USDT'],
            ['symbol' => 'ADAUSDT', 'base_asset' => 'ADA', 'quote_asset' => 'USDT', 'name' => 'Cardano', 'full_name' => 'Cardano / USDT'],
            ['symbol' => 'AVAXUSDT', 'base_asset' => 'AVAX', 'quote_asset' => 'USDT', 'name' => 'Avalanche', 'full_name' => 'Avalanche / USDT'],
            ['symbol' => 'LINKUSDT', 'base_asset' => 'LINK', 'quote_asset' => 'USDT', 'name' => 'Chainlink', 'full_name' => 'Chainlink / USDT'],
            ['symbol' => 'DOTUSDT', 'base_asset' => 'DOT', 'quote_asset' => 'USDT', 'name' => 'Polkadot', 'full_name' => 'Polkadot / USDT'],
            ['symbol' => 'NEARUSDT', 'base_asset' => 'NEAR', 'quote_asset' => 'USDT', 'name' => 'NEAR Protocol', 'full_name' => 'NEAR / USDT'],
            ['symbol' => 'MATICUSDT', 'base_asset' => 'MATIC', 'quote_asset' => 'USDT', 'name' => 'Polygon', 'full_name' => 'Polygon / USDT'],
            ['symbol' => 'SHIBUSDT', 'base_asset' => 'SHIB', 'quote_asset' => 'USDT', 'name' => 'Shiba Inu', 'full_name' => 'Shiba Inu / USDT'],
            ['symbol' => 'LTCUSDT', 'base_asset' => 'LTC', 'quote_asset' => 'USDT', 'name' => 'Litecoin', 'full_name' => 'Litecoin / USDT'],
            ['symbol' => 'TRXUSDT', 'base_asset' => 'TRX', 'quote_asset' => 'USDT', 'name' => 'TRON', 'full_name' => 'TRON / USDT'],
            ['symbol' => 'SUIUSDT', 'base_asset' => 'SUI', 'quote_asset' => 'USDT', 'name' => 'Sui', 'full_name' => 'Sui / USDT'],
            ['symbol' => 'PEPEUSDT', 'base_asset' => 'PEPE', 'quote_asset' => 'USDT', 'name' => 'Pepe', 'full_name' => 'Pepe / USDT'],
            ['symbol' => 'WIFUSDT', 'base_asset' => 'WIF', 'quote_asset' => 'USDT', 'name' => 'dogwifhat', 'full_name' => 'dogwifhat / USDT'],
            ['symbol' => 'INJUSDT', 'base_asset' => 'INJ', 'quote_asset' => 'USDT', 'name' => 'Injective', 'full_name' => 'Injective / USDT'],
            ['symbol' => 'APTUSDT', 'base_asset' => 'APT', 'quote_asset' => 'USDT', 'name' => 'Aptos', 'full_name' => 'Aptos / USDT'],
            ['symbol' => 'ARBUSDT', 'base_asset' => 'ARB', 'quote_asset' => 'USDT', 'name' => 'Arbitrum', 'full_name' => 'Arbitrum / USDT'],
            ['symbol' => 'OPUSDT', 'base_asset' => 'OP', 'quote_asset' => 'USDT', 'name' => 'Optimism', 'full_name' => 'Optimism / USDT'],
            ['symbol' => 'RENDERUSDT', 'base_asset' => 'RENDER', 'quote_asset' => 'USDT', 'name' => 'Render', 'full_name' => 'Render / USDT'],
            ['symbol' => 'FTMUSDT', 'base_asset' => 'FTM', 'quote_asset' => 'USDT', 'name' => 'Fantom', 'full_name' => 'Fantom / USDT'],
            ['symbol' => 'ATOMUSDT', 'base_asset' => 'ATOM', 'quote_asset' => 'USDT', 'name' => 'Cosmos', 'full_name' => 'Cosmos / USDT'],
            ['symbol' => 'UNIUSDT', 'base_asset' => 'UNI', 'quote_asset' => 'USDT', 'name' => 'Uniswap', 'full_name' => 'Uniswap / USDT'],
            ['symbol' => 'BCHUSDT', 'base_asset' => 'BCH', 'quote_asset' => 'USDT', 'name' => 'Bitcoin Cash', 'full_name' => 'Bitcoin Cash / USDT'],
            ['symbol' => 'AAVEUSDT', 'base_asset' => 'AAVE', 'quote_asset' => 'USDT', 'name' => 'Aave', 'full_name' => 'Aave / USDT'],

            // Forex Pairs
            ['symbol' => 'EURUSD', 'base_asset' => 'EUR', 'quote_asset' => 'USD', 'name' => 'Euro / US Dollar', 'full_name' => 'Euro / USD'],
            ['symbol' => 'GBPUSD', 'base_asset' => 'GBP', 'quote_asset' => 'USD', 'name' => 'British Pound / US Dollar', 'full_name' => 'GBP / USD'],
            ['symbol' => 'USDJPY', 'base_asset' => 'USD', 'quote_asset' => 'JPY', 'name' => 'US Dollar / Japanese Yen', 'full_name' => 'USD / JPY'],
            ['symbol' => 'AUDUSD', 'base_asset' => 'AUD', 'quote_asset' => 'USD', 'name' => 'Australian Dollar / USD', 'full_name' => 'AUD / USD'],
            ['symbol' => 'USDCAD', 'base_asset' => 'USD', 'quote_asset' => 'CAD', 'name' => 'US Dollar / Canadian Dollar', 'full_name' => 'USD / CAD'],
            ['symbol' => 'USDCHF', 'base_asset' => 'USD', 'quote_asset' => 'CHF', 'name' => 'US Dollar / Swiss Franc', 'full_name' => 'USD / CHF'],
            ['symbol' => 'NZDUSD', 'base_asset' => 'NZD', 'quote_asset' => 'USD', 'name' => 'New Zealand Dollar / USD', 'full_name' => 'NZD / USD'],
            ['symbol' => 'EURGBP', 'base_asset' => 'EUR', 'quote_asset' => 'GBP', 'name' => 'Euro / British Pound', 'full_name' => 'EUR / GBP'],
            ['symbol' => 'EURJPY', 'base_asset' => 'EUR', 'quote_asset' => 'JPY', 'name' => 'Euro / Japanese Yen', 'full_name' => 'EUR / JPY'],
            ['symbol' => 'GBPJPY', 'base_asset' => 'GBP', 'quote_asset' => 'JPY', 'name' => 'British Pound / Japanese Yen', 'full_name' => 'GBP / JPY'],

            // Metals & Commodities
            ['symbol' => 'XAUUSD', 'base_asset' => 'XAU', 'quote_asset' => 'USD', 'name' => 'Gold / US Dollar', 'full_name' => 'Gold / USD'],
            ['symbol' => 'XAGUSD', 'base_asset' => 'XAG', 'quote_asset' => 'USD', 'name' => 'Silver / US Dollar', 'full_name' => 'Silver / USD'],
            ['symbol' => 'USOIL', 'base_asset' => 'USOIL', 'quote_asset' => 'USD', 'name' => 'Crude Oil (WTI)', 'full_name' => 'WTI Crude Oil'],
            ['symbol' => 'UKOIL', 'base_asset' => 'UKOIL', 'quote_asset' => 'USD', 'name' => 'Brent Crude Oil', 'full_name' => 'Brent Crude Oil'],

            // Indices
            ['symbol' => 'US30', 'base_asset' => 'US30', 'quote_asset' => 'USD', 'name' => 'Dow Jones Industrial 30', 'full_name' => 'US Wall Street 30'],
            ['symbol' => 'NAS100', 'base_asset' => 'NAS100', 'quote_asset' => 'USD', 'name' => 'Nasdaq 100 Index', 'full_name' => 'US Tech 100'],
            ['symbol' => 'SPX500', 'base_asset' => 'SPX500', 'quote_asset' => 'USD', 'name' => 'S&P 500 Index', 'full_name' => 'S&P 500'],
            ['symbol' => 'GER30', 'base_asset' => 'GER30', 'quote_asset' => 'EUR', 'name' => 'Germany DAX 40', 'full_name' => 'DAX 40'],
        ];
    }
}
