<?php

namespace App\Console\Commands;

use App\Models\Signal;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
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
        } else {
            $pnl = $signal->direction === 'buy'
                ? (($currentPrice - $entry) / $entry * 100)
                : (($entry - $currentPrice) / $entry * 100);

            $this->line("  [LIVE] Signal #{$signal->id} ({$signal->symbol}) Entry: {$entry} | Current: {$currentPrice} | PnL: " . number_format($pnl, 2) . "%");
        }
    }

    private function getCurrentPrice(string $symbol): ?float
    {
        $binanceSymbol = strtoupper(str_replace('/', '', $symbol));

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
                    return isset($data['price']) ? (float) $data['price'] : null;
                }
            } catch (\Exception $e) {
                continue;
            }
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

        if (str_contains($symbolUpper, 'BTC') || str_contains($symbolUpper, 'ETH') ||
            str_contains($symbolUpper, 'BNB') || str_contains($symbolUpper, 'SOL')) {
            $diff = $direction === 'buy' ? $current - $entry : $entry - $current;
            return round($diff, 2);
        }

        $diff = $direction === 'buy' ? $current - $entry : $entry - $current;
        return round($diff * 10000, 1);
    }
}
