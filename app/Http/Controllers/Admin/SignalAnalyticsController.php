<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Signal;
use App\Models\SignalCategory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SignalAnalyticsController extends Controller
{
    public function index()
    {
        $overall = $this->getOverallStats();
        $bySymbol = $this->getBySymbol();
        $byCategory = $this->getByCategory();
        $monthlyPerformance = $this->getMonthlyPerformance();
        $recentSignals = Signal::where('status', 'closed')
            ->with('categories')
            ->latest('closed_at')
            ->limit(10)
            ->get();

        return view('admin.analytics.signals', compact(
            'overall',
            'bySymbol',
            'byCategory',
            'monthlyPerformance',
            'recentSignals'
        ));
    }

    private function getOverallStats(): array
    {
        $total = Signal::where('status', 'closed')->count();
        $wins = Signal::where('status', 'closed')->where('result', 'win')->count();
        $losses = Signal::where('status', 'closed')->where('result', 'loss')->count();
        $pending = Signal::whereIn('status', ['active', 'draft', 'pending'])->count();

        $totalPips = Signal::where('status', 'closed')
            ->whereNotNull('pips_result')
            ->sum('pips_result');

        $avgPips = Signal::where('status', 'closed')
            ->whereNotNull('pips_result')
            ->avg('pips_result');

        $bestPips = Signal::where('status', 'closed')
            ->whereNotNull('pips_result')
            ->max('pips_result');

        $worstPips = Signal::where('status', 'closed')
            ->whereNotNull('pips_result')
            ->min('pips_result');

        return [
            'total' => $total,
            'wins' => $wins,
            'losses' => $losses,
            'pending' => $pending,
            'win_rate' => $total > 0 ? round(($wins / $total) * 100, 1) : 0,
            'total_pips' => round((float) $totalPips, 2),
            'avg_pips' => round((float) $avgPips, 2),
            'best_pips' => round((float) $bestPips, 2),
            'worst_pips' => round((float) $worstPips, 2),
        ];
    }

    private function getBySymbol(): array
    {
        return Signal::where('status', 'closed')
            ->selectRaw("
                symbol,
                COUNT(*) as total,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN result = 'loss' THEN 1 ELSE 0 END) as losses,
                SUM(COALESCE(pips_result, 0)) as total_pips,
                AVG(COALESCE(pips_result, 0)) as avg_pips
            ")
            ->groupBy('symbol')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn ($row) => [
                'symbol' => $row->symbol,
                'total' => $row->total,
                'wins' => $row->wins,
                'losses' => $row->losses,
                'win_rate' => $row->total > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
                'total_pips' => round((float) $row->total_pips, 2),
                'avg_pips' => round((float) $row->avg_pips, 2),
            ])
            ->toArray();
    }

    private function getByCategory(): array
    {
        return Signal::where('status', 'closed')
            ->join('signal_category_signal', 'signals.id', '=', 'signal_category_signal.signal_id')
            ->join('signal_categories', 'signal_categories.id', '=', 'signal_category_signal.signal_category_id')
            ->selectRaw("
                signal_categories.name as category,
                COUNT(*) as total,
                SUM(CASE WHEN signals.result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN signals.result = 'loss' THEN 1 ELSE 0 END) as losses,
                SUM(COALESCE(signals.pips_result, 0)) as total_pips
            ")
            ->groupBy('signal_categories.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category,
                'total' => $row->total,
                'wins' => $row->wins,
                'losses' => $row->losses,
                'win_rate' => $row->total > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
                'total_pips' => round((float) $row->total_pips, 2),
            ])
            ->toArray();
    }

    private function getMonthlyPerformance(): array
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

        $data = Signal::where('status', 'closed')
            ->where('closed_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("
                strftime('%Y-%m', closed_at) as ym,
                COUNT(*) as total,
                SUM(CASE WHEN result = 'win' THEN 1 ELSE 0 END) as wins,
                SUM(COALESCE(pips_result, 0)) as total_pips
            ")
            ->groupBy('ym')
            ->get()
            ->keyBy('ym');

        return $months->map(function ($m) use ($data) {
            $key = sprintf('%04d-%02d', $m['year'], $m['month_num']);
            $row = $data->get($key);
            return [
                'month' => $m['month'],
                'total' => $row->total ?? 0,
                'wins' => $row->wins ?? 0,
                'win_rate' => ($row->total ?? 0) > 0 ? round(($row->wins / $row->total) * 100, 1) : 0,
                'pips' => round((float) ($row->total_pips ?? 0), 2),
            ];
        })->toArray();
    }
}
