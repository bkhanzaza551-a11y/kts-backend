<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MarketDataService
{
    private array $baseUrls = [
        'https://api1.binance.com/api/v3',
        'https://api2.binance.com/api/v3',
        'https://api3.binance.com/api/v3',
    ];

    private function fetchWithFallback(string $endpoint, array $params = [], int $timeout = 2): ?array
    {
        if (Cache::has('binance_api_down')) {
            return null;
        }

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

        \Log::error("All Binance endpoints failed for {$endpoint}");
        Cache::put('binance_api_down', true, 600); // 10 minutes circuit breaker
        return null;
    }

    public function getAllSymbols(): array
    {
        return Cache::remember('binance_symbols', 3600, function () {
            $data = $this->fetchWithFallback('/exchangeInfo');

            if (!$data || !isset($data['symbols'])) {
                return $this->getFallbackSymbols();
            }

            $symbols = [];
            foreach ($data['symbols'] as $symbol) {
                if ($symbol['status'] !== 'TRADING') continue;
                if (!in_array($symbol['quoteAsset'], ['USDT', 'BTC', 'ETH', 'BNB'])) continue;

                $symbols[] = [
                    'symbol' => $symbol['symbol'],
                    'base_asset' => $symbol['baseAsset'],
                    'quote_asset' => $symbol['quoteAsset'],
                    'name' => $this->getCoinName($symbol['baseAsset']),
                    'full_name' => $this->getCoinName($symbol['baseAsset']) . ' / ' . $symbol['quoteAsset'],
                ];
            }

            return $symbols;
        });
    }

    public function searchSymbols(string $query, int $limit = 20): array
    {
        $allSymbols = $this->getAllSymbols();
        $query = strtoupper(trim($query));

        $results = array_filter($allSymbols, function ($symbol) use ($query) {
            return str_starts_with($symbol['symbol'], $query)
                || str_starts_with($symbol['base_asset'], $query)
                || str_contains(strtoupper($symbol['name']), $query);
        });

        return array_values(array_slice($results, 0, $limit));
    }

    public function getTicker(string $symbol): ?array
    {
        $cacheKey = "binance_ticker_" . strtoupper($symbol);

        return Cache::remember($cacheKey, 30, function () use ($symbol) {
            $data = $this->fetchWithFallback('/ticker/24hr', ['symbol' => strtoupper($symbol)]);

            if (!$data || !isset($data['lastPrice'])) {
                return null;
            }

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
        });
    }

    public function getPrice(string $symbol): ?float
    {
        $data = $this->fetchWithFallback('/ticker/price', ['symbol' => strtoupper($symbol)], 5);

        return isset($data['price']) ? (float) $data['price'] : null;
    }

    public function getKlines(string $symbol, string $interval = '1h', int $limit = 24): array
    {
        $cacheKey = "binance_klines_" . strtoupper($symbol) . "_{$interval}_{$limit}";

        return Cache::remember($cacheKey, 60, function () use ($symbol, $interval, $limit) {
            $data = $this->fetchWithFallback('/klines', [
                'symbol' => strtoupper($symbol),
                'interval' => $interval,
                'limit' => $limit,
            ]);

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
            'avg_volume' => !empty($klines) ? array_sum(array_column($klines, 'volume')) / count($klines) : 0,
        ];
    }

    private function getCoinName(string $baseAsset): string
    {
        $names = [
            'BTC' => 'Bitcoin', 'ETH' => 'Ethereum', 'BNB' => 'BNB', 'SOL' => 'Solana',
            'XRP' => 'XRP', 'ADA' => 'Cardano', 'DOGE' => 'Dogecoin', 'DOT' => 'Polkadot',
            'AVAX' => 'Avalanche', 'SHIB' => 'Shiba Inu', 'LTC' => 'Litecoin', 'TRX' => 'TRON',
            'LINK' => 'Chainlink', 'UNI' => 'Uniswap', 'ATOM' => 'Cosmos', 'XLM' => 'Stellar',
            'BCH' => 'Bitcoin Cash', 'ALGO' => 'Algorand', 'FIL' => 'Filecoin', 'APT' => 'Aptos',
            'ARB' => 'Arbitrum', 'OP' => 'Optimism', 'MATIC' => 'Polygon', 'NEAR' => 'NEAR Protocol',
            'ICP' => 'Internet Computer', 'FTM' => 'Fantom', 'AAVE' => 'Aave', 'MKR' => 'Maker',
            'GRT' => 'The Graph', 'CRV' => 'Curve', 'ETC' => 'Ethereum Classic', 'EOS' => 'EOS',
            'XTZ' => 'Tezos', 'THETA' => 'THETA', 'VET' => 'VeChain', 'HBAR' => 'Hedera',
            'FLOW' => 'Flow', 'AXS' => 'Axie Infinity', 'CHZ' => 'Chiliz', 'ENJ' => 'Enjin Coin',
            'LDO' => 'Lido DAO', 'SUI' => 'Sui', 'SEI' => 'Sei', 'TIA' => 'Celestia',
            'JUP' => 'Jupiter', 'WIF' => 'dogwifhat', 'PEPE' => 'Pepe', 'FLOKI' => 'FLOKI',
            'WLD' => 'Worldcoin', 'RENDER' => 'Render', 'STX' => 'Stacks', 'INJ' => 'Injective',
            'TOMO' => 'TomoChain', 'ZIL' => 'Zilliqa', 'NEO' => 'NEO', 'WAVES' => 'Waves',
            'DASH' => 'Dash', 'ZEC' => 'Zcash', 'XMR' => 'Monero', 'BAT' => 'Basic Attention Token',
            'RUNE' => 'THORChain', 'ENS' => 'Ethereum Name Service', 'CELO' => 'Celo',
            'MINA' => 'Mina Protocol', 'ROSE' => 'Oasis Network', 'KAVA' => 'Kava',
            'GLMR' => 'Moonbeam', 'CFX' => 'Conflux', 'KSM' => 'Kusama',
        ];

        return $names[strtoupper($baseAsset)] ?? ucfirst(strtolower($baseAsset));
    }

    private function getFallbackSymbols(): array
    {
        return [
            ['symbol' => 'BTCUSDT', 'base_asset' => 'BTC', 'quote_asset' => 'USDT', 'name' => 'Bitcoin', 'full_name' => 'Bitcoin / USDT'],
            ['symbol' => 'ETHUSDT', 'base_asset' => 'ETH', 'quote_asset' => 'USDT', 'name' => 'Ethereum', 'full_name' => 'Ethereum / USDT'],
            ['symbol' => 'BNBUSDT', 'base_asset' => 'BNB', 'quote_asset' => 'USDT', 'name' => 'BNB', 'full_name' => 'BNB / USDT'],
            ['symbol' => 'SOLUSDT', 'base_asset' => 'SOL', 'quote_asset' => 'USDT', 'name' => 'Solana', 'full_name' => 'Solana / USDT'],
            ['symbol' => 'XRPUSDT', 'base_asset' => 'XRP', 'quote_asset' => 'USDT', 'name' => 'XRP', 'full_name' => 'XRP / USDT'],
            ['symbol' => 'ADAUSDT', 'base_asset' => 'ADA', 'quote_asset' => 'USDT', 'name' => 'Cardano', 'full_name' => 'Cardano / USDT'],
            ['symbol' => 'DOGEUSDT', 'base_asset' => 'DOGE', 'quote_asset' => 'USDT', 'name' => 'Dogecoin', 'full_name' => 'Dogecoin / USDT'],
            ['symbol' => 'DOTUSDT', 'base_asset' => 'DOT', 'quote_asset' => 'USDT', 'name' => 'Polkadot', 'full_name' => 'Polkadot / USDT'],
            ['symbol' => 'AVAXUSDT', 'base_asset' => 'AVAX', 'quote_asset' => 'USDT', 'name' => 'Avalanche', 'full_name' => 'Avalanche / USDT'],
            ['symbol' => 'LINKUSDT', 'base_asset' => 'LINK', 'quote_asset' => 'USDT', 'name' => 'Chainlink', 'full_name' => 'Chainlink / USDT'],
        ];
    }
}
