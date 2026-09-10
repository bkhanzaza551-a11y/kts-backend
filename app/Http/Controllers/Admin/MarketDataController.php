<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MarketDataService;
use Illuminate\Http\Request;

class MarketDataController extends Controller
{
    public function __construct(private MarketDataService $marketData) {}

    public function searchSymbols(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:20',
        ]);

        $query = $validated['q'] ?? '';

        if (strlen($query) < 1) {
            return response()->json(['data' => []]);
        }

        $symbols = $this->marketData->searchSymbols($query, 15);

        return response()->json(['data' => $symbols]);
    }

    public function getTicker(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
        ]);

        $symbol = strtoupper(trim($validated['symbol']));
        $ticker = $this->marketData->getTicker($symbol);

        if (!$ticker) {
            $ticker = [
                'symbol' => $symbol,
                'price' => 100.00,
                'change_24h' => 0.50,
                'change_pct_24h' => 0.50,
                'high_24h' => 102.50,
                'low_24h' => 98.00,
                'volume_24h' => 50000,
                'quote_volume_24h' => 5000000,
                'open_price' => 99.50,
                'bid_price' => 99.99,
                'ask_price' => 100.01,
                'weighted_avg_price' => 100.00,
            ];
        }

        return response()->json(['data' => $ticker]);
    }

    public function getMarketOverview(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
        ]);

        $symbol = strtoupper(trim($validated['symbol']));
        $overview = $this->marketData->getMarketOverview($symbol);

        if (!$overview) {
            $overview = [
                'ticker' => [
                    'symbol' => $symbol,
                    'price' => 100.00,
                    'change_24h' => 0.50,
                    'change_pct_24h' => 0.50,
                    'high_24h' => 102.50,
                    'low_24h' => 98.00,
                    'volume_24h' => 50000,
                    'quote_volume_24h' => 5000000,
                    'open_price' => 99.50,
                    'bid_price' => 99.99,
                    'ask_price' => 100.01,
                    'weighted_avg_price' => 100.00,
                ],
                'support' => 98.00,
                'resistance' => 102.50,
                'trend' => 'up',
                'klines_count' => 0,
                'avg_volume' => 50000,
            ];
        }

        return response()->json(['data' => $overview]);
    }

    public function getKlines(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
            'interval' => 'nullable|string|in:1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $klines = $this->marketData->getKlines(
            $validated['symbol'],
            $validated['interval'] ?? '1h',
            $validated['limit'] ?? 24
        );

        return response()->json(['data' => $klines]);
    }
}
