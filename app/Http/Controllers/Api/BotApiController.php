<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mt5BotConfig;
use App\Models\Mt5BotTrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BotApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Mt5BotConfig::select([
            'id', 'created_by', 'name', 'description', 'status', 'mode', 'auto_trade',
            'balance', 'equity', 'total_profit', 'total_loss',
            'total_trades', 'winning_trades', 'losing_trades',
            'last_connected_at', 'last_trade_at',
        ]);

        if (!$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('status', 'active')
                  ->orWhere('created_by', $user->id);
            });
        }

        $bots = $query->get();

        return response()->json(['success' => true, 'data' => $bots]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $bot = Mt5BotConfig::select([
            'id', 'created_by', 'name', 'description', 'status', 'mode', 'auto_trade',
            'take_profit_pips', 'stop_loss_pips',
            'max_daily_trades', 'max_daily_loss', 'balance', 'equity',
            'total_profit', 'total_loss', 'total_trades', 'winning_trades',
            'losing_trades', 'last_connected_at', 'last_trade_at', 'error_message',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $bot]);
    }

    public function trades(Request $request, $id): JsonResponse
    {
        $bot = Mt5BotConfig::findOrFail($id);

        $trades = Mt5BotTrade::where('bot_config_id', $id)
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $trades]);
    }

    public function toggle(Request $request, $id): JsonResponse
    {
        $bot = Mt5BotConfig::findOrFail($id);

        $bot->auto_trade = !$bot->auto_trade;
        $bot->save();

        return response()->json([
            'success' => true,
            'message' => 'Auto-trade ' . ($bot->auto_trade ? 'enabled' : 'disabled'),
            'data' => ['auto_trade' => $bot->auto_trade],
        ]);
    }
}

