<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mt5BotTrade;
use App\Models\Mt5BotConfig;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Mt5AnalyticsController extends Controller
{
    public function index()
    {
        $overall = $this->getOverallStats();
        $equityCurve = $this->getEquityCurve();
        $bySymbol = $this->getBySymbol();
        $byStrategy = $this->getByStrategy();
        $monthlyPnl = $this->getMonthlyPnl();

        return view('admin.analytics.mt5', compact(
            'overall',
            'equityCurve',
            'bySymbol',
            'byStrategy',
            'monthlyPnl'
        ));
    }

    private function getOverallStats(): array
    {
        $totalTrades = Mt5BotTrade::where('status', 'closed')->count();
        $wins = Mt5BotTrade::where('status', 'closed')->where('profit', '>', 0)->count();
        $losses = Mt5BotTrade::where('status', 'closed')->where('profit', '<', 0)->count();
        $breakeven = Mt5BotTrade::where('status', 'closed')->where('profit', '=', 0)->count();

        $totalProfit = Mt5BotTrade::where('status', 'closed')->sum('profit');
        $totalCommission = Mt5BotTrade::where('status', 'closed')->sum('commission');
        $totalSwap = Mt5BotTrade::where('status', 'closed')->sum('swap');
        $netProfit = $totalProfit + $totalCommission + $totalSwap;

        $avgProfit = Mt5BotTrade::where('status', 'closed')->avg('profit');
        $bestTrade = Mt5BotTrade::where('status', 'closed')->max('profit');
        $worstTrade = Mt5BotTrade::where('status', 'closed')->min('profit');

        $openTrades = Mt5BotTrade::where('status', 'open')->count();
        $openProfit = Mt5BotTrade::where('status', 'open')->sum('profit');

        return [
            'total_trades' => $totalTrades,
            'wins' => $wins,
            'losses' => $losses,
            'breakeven' => $breakeven,
            'win_rate' => $totalTrades > 0 ? round(($wins / $totalTrades) * 100, 1) : 0,
            'total_profit' => round((float) $totalProfit, 2),
            'net_profit' => round((float) $netProfit, 2),
            'total_commission' => round((float) $totalCommission, 2),
            'total_swap' => round((float) $totalSwap, 2),
            'avg_profit' => round((float) $avgProfit, 2),
            'best_trade' => round((float) $bestTrade, 2),
            'worst_trade' => round((float) $worstTrade, 2),
            'open_trades' => $openTrades,
            'open_profit' => round((float) $openProfit, 2),
        ];
    }

    private function getEquityCurve(): array
    {
        $equity = \App\Models\Mt5BotConfig::where('status', 'active')->sum('balance') ?: 10000;

        $trades = Mt5BotTrade::where('status', 'closed')
            ->orderBy('closed_at')
            ->select('profit', 'closed_at')
            ->get();

        $curve = [['date' => 'Start', 'equity' => round($equity, 2)]];

        foreach ($trades as $trade) {
            $equity += $trade->profit;
            $curve[] = [
                'date' => $trade->closed_at->format('M d'),
                'equity' => round($equity, 2),
            ];
        }

        return $curve;
    }

    private function getBySymbol(): array
    {
        return Mt5BotTrade::where('status', 'closed')
            ->selectRaw("
                symbol,
                COUNT(*) as total,
                SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN profit < 0 THEN 1 ELSE 0 END) as losses,
                SUM(profit) as total_profit,
                AVG(profit) as avg_profit
            ")
            ->groupBy('symbol')
            ->orderBy('total_profit', 'desc')
            ->get()
            ->map(fn ($row) => [
                'symbol' => $row->symbol,
                'total' => $row->total,
                'wins' => $row->wins,
                'losses' => $row->losses,
                'win_rate' => $row->total > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
                'total_profit' => round((float) $row->total_profit, 2),
                'avg_profit' => round((float) $row->avg_profit, 2),
            ])
            ->toArray();
    }

    private function getByStrategy(): array
    {
        return Mt5BotTrade::where('status', 'closed')
            ->whereNotNull('strategy')
            ->selectRaw("
                strategy,
                COUNT(*) as total,
                SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as wins,
                SUM(profit) as total_profit
            ")
            ->groupBy('strategy')
            ->orderBy('total_profit', 'desc')
            ->get()
            ->map(fn ($row) => [
                'strategy' => $row->strategy,
                'total' => $row->total,
                'wins' => $row->wins,
                'win_rate' => $row->total > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
                'total_profit' => round((float) $row->total_profit, 2),
            ])
            ->toArray();
    }

    private function getMonthlyPnl(): array
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months->push([
                'month' => $month->format('M Y'),
                'year' => $month->year,
                'month_num' => $month->month,
            ]);
        }

        $data = Mt5BotTrade::where('status', 'closed')
            ->where('closed_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("
                strftime('%Y-%m', closed_at) as ym,
                COUNT(*) as trades,
                SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as wins,
                SUM(profit) as total_profit
            ")
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        return $months->map(function ($m) use ($data) {
            $key = sprintf('%04d-%02d', $m['year'], $m['month_num']);
            $row = $data->get($key);
            return [
                'month' => $m['month'],
                'trades' => $row->trades ?? 0,
                'wins' => $row->wins ?? 0,
                'win_rate' => ($row->trades ?? 0) > 0 ? round(($row->wins / $row->trades) * 100, 1) : 0,
                'profit' => round((float) ($row->total_profit ?? 0), 2),
            ];
        })->toArray();
    }
}
