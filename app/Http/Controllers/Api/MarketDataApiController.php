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
            'symbol' => 'required|string|max:20',
        ]);

        $ticker = $this->marketData->getTicker($validated['symbol']);

        if (!$ticker) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch ticker data'], 404);
        }

        return response()->json(['success' => true, 'data' => $ticker]);
    }

    public function getMarketOverview(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|max:20',
        ]);

        $overview = $this->marketData->getMarketOverview($validated['symbol']);

        if (!$overview) {
            return response()->json(['success' => false, 'message' => 'Unable to fetch market data'], 404);
        }

        return response()->json(['success' => true, 'data' => $overview]);
    }
}
