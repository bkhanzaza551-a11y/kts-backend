<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends Controller
{
    private array $defaultPlans = [
        [
            'id' => 'basic',
            'name' => 'Basic',
            'price' => 49,
            'currency' => 'USD',
            'duration_days' => 30,
            'features' => ['5 Signals Daily', 'Basic Bots', 'Email Support', 'Basic Education'],
            'color' => '#9CA3AF',
        ],
        [
            'id' => 'premium',
            'name' => 'Premium',
            'price' => 99,
            'currency' => 'USD',
            'duration_days' => 30,
            'features' => ['15 Signals Daily', 'All Bots', 'Priority Support', 'Full Education', 'VIP Chat'],
            'color' => '#D4A843',
            'popular' => true,
        ],
        [
            'id' => 'platinum',
            'name' => 'Platinum',
            'price' => 199,
            'currency' => 'USD',
            'duration_days' => 30,
            'features' => ['Unlimited Signals', 'All Bots + Custom', '24/7 Support', 'Full Education', 'VIP Chat', 'Personal Mentor'],
            'color' => '#E8C975',
        ],
    ];

    public function plans(): JsonResponse
    {
        $stored = SystemSetting::getValue('subscription_plans');
        $plans = $stored ? json_decode($stored, true) : $this->defaultPlans;

        return response()->json(['success' => true, 'data' => $plans]);
    }

    public function history(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function subscription(Request $request): JsonResponse
    {
        $latest = Transaction::where('user_id', $request->user()->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'is_active' => $latest && $latest->approved_at
                    ? $latest->approved_at->addDays($latest->plan_duration_days)->isFuture()
                    : false,
                'plan' => $latest,
            ],
        ]);
    }
}
