<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mt5BotCredential;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\SystemHealthService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const STATS_CACHE_TTL = 60;

    public function index()
    {
        $stats = $this->getStats();
        $chartData = $this->getChartData();
        $health = SystemHealthService::getMetrics();
        $healthScore = SystemHealthService::getHealthScore($health);
        $recentActivity = ActivityLogger::getRecent(30);

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'health',
            'healthScore',
            'recentActivity'
        ));
    }

    public function stats()
    {
        return response()->json($this->getStats());
    }

    public function userGrowth()
    {
        return response()->json($this->getUserGrowthData());
    }

    public function revenueChart()
    {
        return response()->json($this->getRevenueChartData());
    }

    public function gatewayBreakdown()
    {
        return response()->json($this->getGatewayBreakdown());
    }

    private function getStats(): array
    {
        return Cache::remember('dashboard_stats', self::STATS_CACHE_TTL, function () {
            $now = Carbon::now();

            $statusCounts = User::selectRaw("status, COUNT(*) as count")
                ->groupBy('status')->pluck('count', 'status');

            $staffCount = User::whereHas('roles', function ($q) {
                $q->where('slug', '!=', 'user');
            })->count();

            $pendingTransactions = Transaction::where('status', 'pending')->count();
            $pendingAmount = Transaction::where('status', 'pending')->sum('amount');

            $thisMonthRevenue = Transaction::where('status', 'approved')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->sum('amount');

            $lastMonthRevenue = Transaction::where('status', 'approved')
                ->whereMonth('created_at', $now->copy()->subMonth()->month)
                ->whereYear('created_at', $now->copy()->subMonth()->year)
                ->sum('amount');

            $revenueChange = $lastMonthRevenue > 0
                ? round((($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
                : ($thisMonthRevenue > 0 ? 100 : 0);

            return [
                'total_users' => User::count(),
                'active_users' => $statusCounts->get('active', 0),
                'premium_users' => User::where('is_premium', true)
                    ->where('premium_expires_at', '>', $now)->count(),
                'banned_users' => User::where('is_banned', true)->count(),
                'total_staff' => $staffCount,
                'new_today' => User::whereDate('created_at', $now)->count(),
                'new_this_week' => User::whereDate('created_at', '>=', $now->copy()->startOfWeek())->count(),
                'new_this_month' => User::whereDate('created_at', '>=', $now->copy()->startOfMonth())->count(),
                'mt5_bot_users' => Mt5BotCredential::where('is_active', true)
                    ->distinct('user_id')->count('user_id'),
                'total_revenue' => Transaction::where('status', 'approved')->sum('amount'),
                'this_month_revenue' => $thisMonthRevenue,
                'revenue_change' => $revenueChange,
                'pending_transactions' => $pendingTransactions,
                'pending_amount' => $pendingAmount,
                'cached_at' => now()->toDateTimeString(),
            ];
        });
    }

    private function getChartData(): array
    {
        return Cache::remember('dashboard_chart_data', self::STATS_CACHE_TTL, function () {
            return [
                'user_growth' => $this->getUserGrowthData(),
                'status_distribution' => $this->getStatusDistribution(),
                'monthly_registrations' => $this->getMonthlyRegistrations(),
                'revenue' => $this->getRevenueChartData(),
                'gateway_breakdown' => $this->getGatewayBreakdown(),
            ];
        });
    }

    private function getUserGrowthData(): array
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();

        $dailyCounts = User::whereBetween('created_at', [$startDate, Carbon::now()->endOfDay()])
            ->selectRaw("DATE(created_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->pluck('count', 'date')
            ->mapWithKeys(fn ($count, $date) => [Carbon::parse($date)->format('M d') => $count]);

        $growth = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('M d');
            $growth[] = ['date' => $key, 'count' => $dailyCounts->get($key, 0)];
        }
        return $growth;
    }

    private function getStatusDistribution(): array
    {
        $counts = User::selectRaw("status, COUNT(*) as count")
            ->groupBy('status')->pluck('count', 'status');

        return [
            'active' => $counts->get('active', 0),
            'inactive' => $counts->get('inactive', 0),
            'suspended' => $counts->get('suspended', 0),
        ];
    }

    private function getMonthlyRegistrations(): array
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

        $counts = User::selectRaw("strftime('%Y-%m', created_at) as ym, COUNT(*) as count")
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')
            ->pluck('count', 'ym');

        return $months->map(function ($m) use ($counts) {
            $key = sprintf('%04d-%02d', $m['year'], $m['month_num']);
            return ['month' => $m['month'], 'count' => $counts->get($key, 0)];
        })->toArray();
    }

    private function getRevenueChartData(): array
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

        $revenue = Transaction::where('status', 'approved')
            ->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->selectRaw("strftime('%Y-%m', created_at) as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        return $months->map(function ($m) use ($revenue) {
            $key = sprintf('%04d-%02d', $m['year'], $m['month_num']);
            return [
                'month' => $m['month'],
                'revenue' => (float) $revenue->get($key, 0),
            ];
        })->toArray();
    }

    private function getGatewayBreakdown(): array
    {
        $gatewayData = Transaction::where('status', 'approved')
            ->selectRaw("gateway, COUNT(*) as count, SUM(amount) as total")
            ->groupBy('gateway')
            ->get();

        return $gatewayData->map(fn ($row) => [
            'gateway' => ucfirst(str_replace('_', ' ', $row->gateway)),
            'count' => $row->count,
            'total' => (float) $row->total,
        ])->toArray();
    }
}
