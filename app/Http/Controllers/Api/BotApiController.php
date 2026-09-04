<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mt5BotConfig;
use App\Models\Mt5BotTrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BotApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::select([
            'id', 'name', 'description', 'status', 'mode', 'auto_trade',
            'lot_size', 'base_balance', 'base_lot_size', 'whatsapp_number',
                'demo_server', 'demo_account', 'demo_email', 'demo_phone', 'demo_deposit',
            'take_profit_pips', 'stop_loss_pips',
            'max_daily_trades', 'max_daily_loss',
            'balance', 'equity', 'total_profit', 'total_loss',
            'total_trades', 'winning_trades', 'losing_trades',
            'last_connected_at', 'last_trade_at', 'error_message', 'error_message',
        ])->first();

        if (!$bot) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No bot configured yet',
            ]);
        }

        return response()->json(['success' => true, 'data' => $bot]);
    }

    public function show(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::select([
            'id', 'name', 'description', 'status', 'mode', 'auto_trade',
            'lot_size', 'base_balance', 'base_lot_size', 'whatsapp_number',
                'demo_server', 'demo_account', 'demo_email', 'demo_phone', 'demo_deposit',
            'take_profit_pips', 'stop_loss_pips',
            'max_daily_trades', 'max_daily_loss',
            'balance', 'equity', 'total_profit', 'total_loss',
            'total_trades', 'winning_trades', 'losing_trades',
            'last_connected_at', 'last_trade_at', 'error_message', 'error_message',
        ])->first();

        if (!$bot) {
            return response()->json(['success' => false, 'message' => 'Bot not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $bot]);
    }

    public function trades(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $trades = Mt5BotTrade::where('bot_config_id', $bot->id)
            ->latest()
            ->paginate(20);

        return response()->json(['success' => true, 'data' => $trades]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json(['success' => false, 'message' => 'Bot not found'], 404);
        }

        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only super admin can toggle auto-trade'], 403);
        }

        $bot->auto_trade = !$bot->auto_trade;
        $bot->save();

        return response()->json([
            'success' => true,
            'message' => 'Auto-trade ' . ($bot->auto_trade ? 'enabled' : 'disabled'),
            'data' => ['auto_trade' => $bot->auto_trade],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Only super admin can update bot'], 403);
        }

        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json(['success' => false, 'message' => 'Bot not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
            'status' => 'sometimes|in:active,inactive,error',
            'mode' => 'sometimes|in:live,demo,backtest',
            'auto_trade' => 'sometimes|boolean',
            'lot_size' => 'sometimes|nullable|numeric|min:0.01|max:100',
            'base_balance' => 'sometimes|nullable|numeric|min:1|max:1000000',
            'base_lot_size' => 'sometimes|nullable|numeric|min:0.01|max:100',
            'whatsapp_number' => 'sometimes|nullable|string|max:20',
            'demo_server' => 'sometimes|nullable|string|max:100',
            'demo_account' => 'sometimes|nullable|string|max:50',
            'demo_email' => 'sometimes|nullable|string|email|max:100',
            'demo_phone' => 'sometimes|nullable|string|max:20',
            'demo_deposit' => 'sometimes|nullable|numeric|min:0|max:100000000',
            'take_profit_pips' => 'sometimes|nullable|numeric|min:1|max:10000',
            'stop_loss_pips' => 'sometimes|nullable|numeric|min:1|max:10000',
            'max_daily_trades' => 'sometimes|nullable|integer|min:1|max:500',
            'max_daily_loss' => 'sometimes|nullable|numeric|min:0|max:100000',
            'balance' => 'sometimes|nullable|numeric|min:0',
            'equity' => 'sometimes|nullable|numeric|min:0',
        ]);

        if (isset($validated['auto_trade'])) {
            $validated['auto_trade'] = $request->boolean('auto_trade');
        }

        $bot->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bot updated successfully',
            'data' => $bot->select([
                'id', 'name', 'description', 'status', 'mode', 'auto_trade',
                'lot_size', 'base_balance', 'base_lot_size', 'whatsapp_number',
                'demo_server', 'demo_account', 'demo_email', 'demo_phone', 'demo_deposit',
                'take_profit_pips', 'stop_loss_pips',
                'max_daily_trades', 'max_daily_loss',
                'balance', 'equity', 'total_profit', 'total_loss',
                'total_trades', 'winning_trades', 'losing_trades',
                'last_connected_at', 'last_trade_at', 'error_message',
            ])->first(),
        ]);
    }
}
