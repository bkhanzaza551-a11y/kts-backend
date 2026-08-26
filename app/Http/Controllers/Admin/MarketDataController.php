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

        $ticker = $this->marketData->getTicker($validated['symbol']);

        if (!$ticker) {
            return response()->json(['error' => 'Unable to fetch ticker data'], 404);
        }

        return response()->json(['data' => $ticker]);
    }

    public function getMarketOverview(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
        ]);

        $overview = $this->marketData->getMarketOverview($validated['symbol']);

        if (!$overview) {
            return response()->json(['error' => 'Unable to fetch market data'], 404);
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
