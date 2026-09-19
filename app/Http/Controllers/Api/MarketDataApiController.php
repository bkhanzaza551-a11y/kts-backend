<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketDataService;
use Illuminate\Http\Request;

class MarketDataApiController extends Controller
{
    public function __construct(private MarketDataService $marketData) {}

    public function getTicker(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'nullable|string|max:20',
        ]);

        $symbol = $validated['symbol'] ?? 'XAUUSD';
        $ticker = $this->marketData->getTicker($symbol);

        if (!$ticker) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch ticker data'], 404);
        }

        return response()->json(['success' => true, 'data' => $ticker]);
    }

    public function getMarketOverview(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'nullable|string|max:20',
        ]);

        $symbol = $validated['symbol'] ?? 'XAUUSD';
        $overview = $this->marketData->getMarketOverview($symbol);

        if (!$overview) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch market data'], 404);
        }

        return response()->json(['success' => true, 'data' => $overview]);
    }
}
