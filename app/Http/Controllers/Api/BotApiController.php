<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DemoAccountRequest;
use App\Models\Mt5BotConfig;
use App\Models\Mt5BotTrade;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BotApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No bot configured yet',
            ]);
        }

        $botData = $this->formatBotResponse($bot, $request->user());
        return response()->json(['success' => true, 'data' => $botData]);
    }

    public function show(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json(['success' => false, 'message' => 'Bot not found'], 404);
        }

        $botData = $this->formatBotResponse($bot, $request->user());
        return response()->json(['success' => true, 'data' => $botData]);
    }

    public function download(Request $request)
    {
        $user = $request->user('sanctum');

        if (!$user && $request->filled('token')) {
            $personalToken = \Laravel\Sanctum\PersonalAccessToken::findToken($request->query('token'));
            if ($personalToken && $personalToken->tokenable instanceof \App\Models\User) {
                $user = $personalToken->tokenable;
            }
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Please log in to download the Bot file.',
            ], 401);
        }

        $bot = Mt5BotConfig::first();

        if (!$bot || !$bot->hasBotFile()) {
            return response()->json([
                'success' => false,
                'message' => 'No bot software file has been uploaded yet by admin.',
            ], 404);
        }

        $access = $this->checkUserBotFileAccess($user);
        if (!$access['has_access']) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: You must have an approved Demo or Real Account request to download the Bot file.',
                'access_status' => $access['status'],
                'reason' => $access['reason'],
            ], 403);
        }

        $fileName = $bot->bot_file_name ?: basename($bot->bot_file_path);

        ActivityLogger::log('download_bot_file', 'Mt5BotConfig', $bot->id, "User #{$user->id} ({$user->name}) downloaded bot file {$fileName}");

        return Storage::disk('public')->download($bot->bot_file_path, $fileName);
    }

    protected function formatBotResponse(Mt5BotConfig $bot, ?User $user): array
    {
        $fileInfo = $bot->getBotFileInfo();
        $fileAccess = $this->checkUserBotFileAccess($user);

        return [
            'id' => $bot->id,
            'name' => $bot->name,
            'description' => $bot->description,
            'status' => $bot->status,
            'mode' => $bot->mode,
            'auto_trade' => (bool) $bot->auto_trade,
            'base_balance' => (float) $bot->base_balance,
            'base_lot_size' => (float) $bot->base_lot_size,
            'lot_size' => (float) $bot->lot_size,
            'whatsapp_number' => $bot->whatsapp_number,
            'demo_server' => $bot->demo_server,
            'demo_account' => $bot->demo_account,
            'demo_email' => $bot->demo_email,
            'demo_phone' => $bot->demo_phone,
            'demo_deposit' => (float) $bot->demo_deposit,
            'take_profit_pips' => (float) $bot->take_profit_pips,
            'stop_loss_pips' => (float) $bot->stop_loss_pips,
            'max_daily_trades' => (int) $bot->max_daily_trades,
            'max_daily_loss' => (float) $bot->max_daily_loss,
            'balance' => (float) $bot->balance,
            'equity' => (float) $bot->equity,
            'total_profit' => (float) $bot->total_profit,
            'total_loss' => (float) $bot->total_loss,
            'net_profit' => (float) $bot->net_profit,
            'total_trades' => (int) $bot->total_trades,
            'winning_trades' => (int) $bot->winning_trades,
            'losing_trades' => (int) $bot->losing_trades,
            'win_rate' => (float) $bot->win_rate,
            'last_connected_at' => $bot->last_connected_at?->toISOString(),
            'last_trade_at' => $bot->last_trade_at?->toISOString(),
            'error_message' => $bot->error_message,
            'bot_file' => $fileInfo,
            'bot_file_access' => $fileAccess,
        ];
    }

    public function checkUserBotFileAccess(?User $user): array
    {
        if (!$user) {
            return [
                'has_access' => false,
                'status' => 'unauthenticated',
                'reason' => 'Please log in to check download eligibility.',
            ];
        }

        // 1. SuperAdmin / Admin has full access
        if ($user->isSuperAdmin() || $user->hasRole(['admin', 'super-admin']) || $user->hasPermission('mt5_bot_manage')) {
            return [
                'has_access' => true,
                'status' => 'approved',
                'reason' => 'Admin Access Granted',
            ];
        }

        // 2. Approved or Linked DemoAccountRequest
        $approvedRequest = DemoAccountRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'linked'])
            ->latest()
            ->first();

        if ($approvedRequest) {
            return [
                'has_access' => true,
                'status' => 'approved',
                'account_type' => $approvedRequest->account_type,
                'account_number' => $approvedRequest->exness_account_number,
                'reason' => 'Approved Demo/Real Account Request',
            ];
        }

        // 3. User has real_account_id or demo_account_id configured
        if (!empty($user->real_account_id) || !empty($user->demo_account_id)) {
            return [
                'has_access' => true,
                'status' => 'approved',
                'real_account_id' => $user->real_account_id,
                'demo_account_id' => $user->demo_account_id,
                'reason' => 'Active Trading Account Connected',
            ];
        }

        // 4. Pending Request
        $pendingRequest = DemoAccountRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($pendingRequest) {
            return [
                'has_access' => false,
                'status' => 'pending',
                'reason' => 'Your account request is currently under review by admin.',
            ];
        }

        // 5. Rejected Request
        $rejectedRequest = DemoAccountRequest::where('user_id', $user->id)
            ->where('status', 'rejected')
            ->latest()
            ->first();

        if ($rejectedRequest) {
            return [
                'has_access' => false,
                'status' => 'rejected',
                'reason' => 'Your account request was rejected. ' . ($rejectedRequest->admin_notes ? 'Admin note: ' . $rejectedRequest->admin_notes : 'Please contact support.'),
            ];
        }

        return [
            'has_access' => false,
            'status' => 'not_requested',
            'reason' => 'Submit a Demo or Real account request to unlock bot download access.',
        ];
    }

    public function trades(Request $request): JsonResponse
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $trades = Mt5BotTrade::where('bot_config_id', $bot->id)
            ->latest('opened_at')
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
            'base_balance' => 'sometimes|nullable|numeric|min:1|max:1000000',
            'base_lot_size' => 'sometimes|nullable|numeric|min:0.001|max:100',
            'whatsapp_number' => 'sometimes|nullable|string|max:30',
            'demo_server' => 'sometimes|nullable|string|max:100',
            'demo_account' => 'sometimes|nullable|string|max:50',
            'demo_email' => 'sometimes|nullable|string|email|max:100',
            'demo_phone' => 'sometimes|nullable|string|max:30',
            'demo_deposit' => 'sometimes|nullable|numeric|min:0|max:100000000',
            'take_profit_pips' => 'sometimes|nullable|numeric|min:0.1|max:10000',
            'stop_loss_pips' => 'sometimes|nullable|numeric|min:0.1|max:10000',
            'max_daily_trades' => 'sometimes|nullable|integer|min:1|max:500',
            'max_daily_loss' => 'sometimes|nullable|numeric|min:0|max:100000',
            'balance' => 'sometimes|nullable|numeric|min:0',
            'equity' => 'sometimes|nullable|numeric|min:0',
        ]);

        if (isset($validated['auto_trade'])) {
            $validated['auto_trade'] = $request->boolean('auto_trade');
        }

        $bot->update($validated);

        $updated = $bot->fresh();

        return response()->json([
            'success' => true,
            'message' => 'Bot updated successfully',
            'data' => $updated,
        ]);
    }
}
