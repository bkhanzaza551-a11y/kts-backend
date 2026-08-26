<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    private static ?array $rates = null;

    private static array $currencies = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar', 'code' => 'USD'],
        'USDT' => ['symbol' => '₮', 'name' => 'Tether', 'code' => 'USDT'],
        'PKR' => ['symbol' => 'Rs', 'name' => 'Pakistani Rupee', 'code' => 'PKR'],
    ];

    private static array $defaultRates = [
        'USD_USDT' => 1.0,
        'USD_PKR' => 278.50,
        'USDT_USD' => 1.0,
        'USDT_PKR' => 278.50,
        'PKR_USD' => 0.00359,
        'PKR_USDT' => 0.00359,
    ];

    public static function getCurrencyInfo(): array
    {
        return self::$currencies;
    }

    public static function getCurrentCurrency(): string
    {
        return SystemSetting::getValue('currency', 'USD') ?? 'USD';
    }

    public static function setCurrentCurrency(string $code): void
    {
        $code = strtoupper($code);
        if (!isset(self::$currencies[$code])) {
            throw new \InvalidArgumentException("Invalid currency: {$code}");
        }
        SystemSetting::setValue('currency', $code, 'select', 'Display currency for admin panel', 'general');
    }

    public static function getRates(): array
    {
        if (self::$rates !== null) {
            return self::$rates;
        }

        $cached = Cache::get('currency_rates');
        if ($cached) {
            self::$rates = $cached;
            return self::$rates;
        }

        try {
            $response = Http::timeout(5)->get('https://api.exchangerate-api.com/v4/latest/USD');
            if ($response->successful()) {
                $data = $response->json();
                $pkrRate = $data['rates']['PKR'] ?? 278.50;

                self::$rates = [
                    'USD_USDT' => 1.0,
                    'USD_PKR' => $pkrRate,
                    'USDT_USD' => 1.0,
                    'USDT_PKR' => $pkrRate,
                    'PKR_USD' => 1 / $pkrRate,
                    'PKR_USDT' => 1 / $pkrRate,
                ];

                Cache::put('currency_rates', self::$rates, 3600);
                return self::$rates;
            }
        } catch (\Exception $e) {
            // API failed, use defaults
        }

        self::$rates = self::$defaultRates;
        return self::$rates;
    }

    public static function convert(float $amount, string $from, string $to): float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        if ($from === $to) {
            return round($amount, 2);
        }

        $rates = self::getRates();
        $key = "{$from}_{$to}";

        if (isset($rates[$key])) {
            return round($amount * $rates[$key], 2);
        }

        // Cross conversion via USD
        $toUsd = self::getRates()["{$from}_USD"] ?? null;
        $fromUsd = self::getRates()["USD_{$to}"] ?? null;

        if ($toUsd !== null && $fromUsd !== null) {
            return round($amount * $toUsd * $fromUsd, 2);
        }

        return round($amount, 2);
    }

    public static function formatAmount(float $amount, ?string $currency = null): string
    {
        $currency = $currency ?? self::getCurrentCurrency();
        $info = self::$currencies[$currency] ?? self::$currencies['USD'];

        $converted = self::convert($amount, 'USD', $currency);

        return "{$info['symbol']} " . number_format($converted, 2);
    }

    public static function getConversionTable(): array
    {
        $rates = self::getRates();
        return [
            'USD' => ['USDT' => $rates['USD_USDT'], 'PKR' => $rates['USD_PKR']],
            'USDT' => ['USD' => $rates['USDT_USD'], 'PKR' => $rates['USDT_PKR']],
            'PKR' => ['USD' => $rates['PKR_USD'], 'USDT' => $rates['PKR_USDT']],
        ];
    }
}
