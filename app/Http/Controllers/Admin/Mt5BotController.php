<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mt5BotConfig;
use App\Models\Mt5BotLog;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class Mt5BotController extends Controller
{
    public function index(Request $request)
    {
        $bot = Mt5BotConfig::first();

        if (!$bot) {
            $bot = Mt5BotConfig::create([
                'name' => 'KTS10 Pips Bot',
                'description' => 'Automated Gold (XAUUSD) & Major Forex Pairs High-Precision Trading Bot',
                'mt5_account_number' => '87654321',
                'mt5_server' => 'Exness-MT5Real',
                'status' => 'active',
                'mode' => 'live',
                'auto_trade' => true,
                'lot_size' => 0.01,
                'base_balance' => 100.00,
                'base_lot_size' => 0.01,
                'take_profit_pips' => 10.00,
                'stop_loss_pips' => 5.00,
                'max_daily_trades' => 10,
                'max_daily_loss' => 500.00,
                'whatsapp_number' => '+923371244640',
                'balance' => 25000.00,
                'equity' => 25850.00,
                'total_profit' => 4500.00,
                'total_loss' => 950.00,
                'total_trades' => 128,
                'winning_trades' => 108,
                'losing_trades' => 20,
            ]);
        }

        $bot->load(['creator', 'logs' => function ($q) {
            $q->latest()->limit(15);
        }]);

        $tradesCount = $bot->trades()->count();
        $openTradesCount = $bot->trades()->where('status', 'open')->count();
        $closedTradesCount = $bot->trades()->where('status', 'closed')->count();

        $recentTrades = $bot->trades()->latest('opened_at')->limit(15)->get();

        return view('admin.mt5-bot.index', compact('bot', 'tradesCount', 'openTradesCount', 'closedTradesCount', 'recentTrades'));
    }

    public function create()
    {
        return redirect()->route('admin.mt5-bot.index');
    }

    public function store(Request $request)
    {
        $bot = Mt5BotConfig::first();
        if ($bot) {
            return $this->update($request, $bot);
        }

        return redirect()->route('admin.mt5-bot.index');
    }

    public function show(Mt5BotConfig $bot)
    {
        return redirect()->route('admin.mt5-bot.index');
    }

    public function edit(Mt5BotConfig $bot)
    {
        return redirect()->route('admin.mt5-bot.index');
    }

    public function update(Request $request, Mt5BotConfig $bot)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mt5_account_number' => 'required|string|max:50|unique:mt5_bot_configs,mt5_account_number,' . $bot->id,
            'mt5_server' => 'required|string|max:255',
            'bot_file' => 'nullable|file|max:20480',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'mode' => 'required|in:live,demo,backtest',
            'status' => 'required|in:active,inactive,error',
            'auto_trade' => 'nullable|boolean',
            'take_profit_pips' => 'required|numeric|min:0.1|max:10000',
            'stop_loss_pips' => 'required|numeric|min:0.1|max:10000',
            'max_daily_trades' => 'required|integer|min:1|max:1000',
            'max_daily_loss' => 'required|numeric|min:1|max:1000000',
            'whatsapp_number' => 'nullable|string|max:30',
            'base_balance' => 'required|numeric|min:1|max:1000000',
            'base_lot_size' => 'required|numeric|min:0.001|max:100',
            'demo_server' => 'nullable|string|max:100',
            'demo_account' => 'nullable|string|max:50',
            'demo_email' => 'nullable|email|max:100',
            'demo_phone' => 'nullable|string|max:30',
            'demo_deposit' => 'nullable|numeric|min:0|max:100000000',
            // Performance stats overrides
            'balance' => 'nullable|numeric|min:0|max:100000000',
            'equity' => 'nullable|numeric|min:0|max:100000000',
            'total_profit' => 'nullable|numeric|min:0|max:100000000',
            'total_loss' => 'nullable|numeric|min:0|max:100000000',
            'total_trades' => 'nullable|integer|min:0',
            'winning_trades' => 'nullable|integer|min:0',
            'losing_trades' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('bot_file')) {
            if ($bot->bot_file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($bot->bot_file_path);
            }
            $validated['bot_file_path'] = $request->file('bot_file')->store('mt5-bots', 'public');
        }
        unset($validated['bot_file']);

        $validated['auto_trade'] = $request->has('auto_trade') ? $request->boolean('auto_trade') : false;

        if (!empty($validated['api_key'])) {
            $validated['api_key'] = Crypt::encryptString($validated['api_key']);
        } else {
            unset($validated['api_key']);
        }

        if (!empty($validated['api_secret'])) {
            $validated['api_secret'] = Crypt::encryptString($validated['api_secret']);
        } else {
            unset($validated['api_secret']);
        }

        $oldValues = $bot->only(['name', 'mt5_account_number', 'mt5_server', 'mode', 'status', 'auto_trade', 'take_profit_pips', 'stop_loss_pips', 'max_daily_trades', 'max_daily_loss', 'whatsapp_number', 'base_balance', 'base_lot_size']);
        $bot->update($validated);
        $newValues = $bot->only(array_keys($oldValues));

        ActivityLogger::log('update', 'Mt5BotConfig', $bot->id, "Updated MT5 bot config: {$bot->name}", $oldValues, $newValues);
        Cache::forget('mt5_bot_stats');

        return redirect()->route('admin.mt5-bot.index')->with('success', 'KTS Trading Bot configuration updated successfully! Changes are live on the mobile app.');
    }

    public function destroy(Mt5BotConfig $bot)
    {
        return redirect()->route('admin.mt5-bot.index')->with('info', 'Single master bot cannot be deleted, but can be configured or deactivated.');
    }

    public function restore(Mt5BotConfig $bot)
    {
        if ($bot->trashed()) {
            $bot->restore();
            Cache::forget('mt5_bot_stats');
        }
        return redirect()->route('admin.mt5-bot.index')->with('success', 'Bot restored successfully.');
    }

    public function logs(Mt5BotConfig $bot)
    {
        $logs = $bot->logs()->latest()->paginate(50);
        return view('admin.mt5-bot.logs', compact('bot', 'logs'));
    }

    public function trades(Mt5BotConfig $bot)
    {
        $trades = $bot->trades()->latest('opened_at')->paginate(50);
        return view('admin.mt5-bot.trades', compact('bot', 'trades'));
    }

    public function toggleStatus(Mt5BotConfig $bot)
    {
        $newStatus = $bot->status === 'active' ? 'inactive' : 'active';
        $oldStatus = $bot->status;

        $updateData = ['status' => $newStatus];
        if ($newStatus === 'active') {
            $updateData['error_message'] = null;
        }
        $bot->update($updateData);

        Mt5BotLog::create([
            'bot_config_id' => $bot->id,
            'level' => $newStatus === 'active' ? 'success' : 'info',
            'action' => 'status_changed',
            'message' => "Bot status changed from {$oldStatus} to {$newStatus}",
        ]);

        ActivityLogger::log('toggle_status', 'Mt5BotConfig', $bot->id, "Toggled bot status: {$bot->name} ({$oldStatus} → {$newStatus})", ['status' => $oldStatus], ['status' => $newStatus]);
        Cache::forget('mt5_bot_stats');

        return back()->with('success', "Bot status changed to {$newStatus} successfully.");
    }

    public function toggleAutoTrade(Mt5BotConfig $bot)
    {
        $newState = !$bot->auto_trade;

        if ($newState && $bot->status !== 'active') {
            return back()->with('error', 'Bot must be active to enable auto-trade.');
        }

        $oldState = $bot->auto_trade;
        $bot->update(['auto_trade' => $newState]);

        Mt5BotLog::create([
            'bot_config_id' => $bot->id,
            'level' => 'info',
            'action' => 'auto_trade_changed',
            'message' => "Auto-trade " . ($newState ? 'enabled' : 'disabled'),
        ]);

        ActivityLogger::log('toggle_auto_trade', 'Mt5BotConfig', $bot->id, "Toggled auto-trade: {$bot->name} (" . ($oldState ? 'on' : 'off') . " → " . ($newState ? 'on' : 'off') . ")", ['auto_trade' => $oldState], ['auto_trade' => $newState]);
        Cache::forget('mt5_bot_stats');

        return back()->with('success', 'Auto-trade ' . ($newState ? 'enabled' : 'disabled') . ' successfully.');
    }

    public function recalculateStats(Mt5BotConfig $bot)
    {
        $stats = $bot->trades()->selectRaw("
            COUNT(*) as total_trades,
            SUM(CASE WHEN status = 'closed' AND profit > 0 THEN 1 ELSE 0 END) as winning_trades,
            SUM(CASE WHEN status = 'closed' AND profit < 0 THEN 1 ELSE 0 END) as losing_trades,
            COALESCE(SUM(CASE WHEN profit > 0 THEN profit ELSE 0 END), 0) as total_profit,
            COALESCE(ABS(SUM(CASE WHEN profit < 0 THEN profit ELSE 0 END)), 0) as total_loss
        ")->first();

        $bot->update([
            'total_trades' => (int) $stats->total_trades,
            'winning_trades' => (int) $stats->winning_trades,
            'losing_trades' => (int) $stats->losing_trades,
            'total_profit' => (float) $stats->total_profit,
            'total_loss' => (float) $stats->total_loss,
        ]);

        Cache::forget('mt5_bot_stats');

        return back()->with('success', 'Stats recalculated successfully from trade logs.');
    }
}
