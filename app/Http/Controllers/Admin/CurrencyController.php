<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request)
    {
        $currency = $request->input('currency', 'USD');
        CurrencyService::setCurrentCurrency($currency);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'currency' => $currency]);
        }

        return back()->with('success', "Currency switched to {$currency}");
    }

    public function getRates()
    {
        return response()->json([
            'success' => true,
            'current' => CurrencyService::getCurrentCurrency(),
            'rates' => CurrencyService::getConversionTable(),
            'currencies' => CurrencyService::getCurrencyInfo(),
        ]);
    }
}
