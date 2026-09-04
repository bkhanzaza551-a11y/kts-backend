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
        $query = Mt5BotConfig::query();

        if ($search = trim($request->input('search', ''))) {
            $safeSearch = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($safeSearch) {
                $q->where('name', 'like', "%{$safeSearch}%")
                  ->orWhere('mt5_account_number', 'like', "%{$safeSearch}%")
                  ->orWhere('mt5_server', 'like', "%{$safeSearch}%");
            });
        }

        if ($status = $request->input('status')) {
            if (in_array($status, ['active', 'inactive', 'error'])) {
                $query->where('status', $status);
            }
        }

        if ($mode = $request->input('mode')) {
            if (in_array($mode, ['live', 'demo', 'backtest'])) {
                $query->where('mode', $mode);
            }
        }

        $bots = $query->latest()->paginate(15)->withQueryString();

        $stats = Cache::remember('mt5_bot_stats', 60, function () {
            $row = DB::table('mt5_bot_configs')
                ->whereNull('deleted_at')
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as errors,
                    COALESCE(SUM(balance), 0) as total_balance,
                    COALESCE(SUM(equity), 0) as total_equity,
                    COALESCE(SUM(total_profit), 0) as total_profit,
                    COALESCE(SUM(total_loss), 0) as total_loss,
                    COALESCE(SUM(total_trades), 0) as total_trades
                ")->first();

            return [
                'total' => (int) $row->total,
                'active' => (int) $row->active,
                'inactive' => (int) $row->inactive,
                'errors' => (int) $row->errors,
                'total_balance' => (float) $row->total_balance,
                'total_equity' => (float) $row->total_equity,
                'total_profit' => (float) $row->total_profit,
                'total_loss' => (float) $row->total_loss,
                'total_trades' => (int) $row->total_trades,
            ];
        });

        return view('admin.mt5-bot.index', compact('bots', 'stats'));
    }

    public function create()
    {
        return view('admin.mt5-bot.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mt5_account_number' => 'required|string|max:50|unique:mt5_bot_configs,mt5_account_number',
            'mt5_server' => 'required|string|max:255',
            'bot_file' => 'nullable|file|max:20480',
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'mode' => 'required|in:live,demo,backtest',
            'take_profit_pips' => 'required|numeric|min:1|max:10000',
            'stop_loss_pips' => 'required|numeric|min:1|max:10000',
            'max_daily_trades' => 'required|integer|min:1|max:1000',
            'max_daily_loss' => 'required|numeric|min:1|max:1000000',
            'whatsapp_number' => 'nullable|string|max:20',
            'base_balance' => 'nullable|numeric|min:1|max:1000000',
            'base_lot_size' => 'nullable|numeric|min:0.01|max:100',
            'demo_server' => 'nullable|string|max:100',
            'demo_account' => 'nullable|string|max:50',
            'demo_email' => 'nullable|email|max:100',
            'demo_phone' => 'nullable|string|max:20',
            'demo_deposit' => 'nullable|numeric|min:0|max:100000000',
        ]);

        if ($request->hasFile('bot_file')) {
            $validated['bot_file_path'] = $request->file('bot_file')->store('mt5-bots', 'public');
        }
        unset($validated['bot_file']);

        if (!empty($validated['api_key'])) {
            $validated['api_key'] = Crypt::encryptString($validated['api_key']);
        }

        if (!empty($validated['api_secret'])) {
            $validated['api_secret'] = Crypt::encryptString($validated['api_secret']);
        }

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'inactive';

        $bot = Mt5BotConfig::create($validated);

        Mt5BotLog::create([
            'bot_config_id' => $bot->id,
            'level' => 'info',
            'action' => 'created',
            'message' => "Bot configuration created: {$bot->name}",
        ]);

        ActivityLogger::log('create', 'Mt5BotConfig', $bot->id, "Created MT5 bot config: {$bot->name}");
        Cache::forget('mt5_bot_stats');

        return redirect()->route('admin.mt5-bot.show', $bot)->with('success', 'Bot configuration created successfully.');
    }

    public function show(Mt5BotConfig $bot)
    {
        $bot->load(['creator', 'logs' => function ($q) {
            $q->latest()->limit(20);
        }]);

        $counts = $bot->trades()->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_count,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_count
        ")->first();

        $tradesCount = (int) ($counts->total ?? 0);
        $openTradesCount = (int) ($counts->open_count ?? 0);
        $closedTradesCount = (int) ($counts->closed_count ?? 0);

        $recentTrades = $bot->trades()->latest('opened_at')->limit(10)->get();

        return view('admin.mt5-bot.show', compact('bot', 'tradesCount', 'openTradesCount', 'closedTradesCount', 'recentTrades'));
    }

    public function edit(Mt5BotConfig $bot)
    {
        $bot->api_key = null;
        $bot->api_secret = null;

        return view('admin.mt5-bot.edit', compact('bot'));
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
            'auto_trade' => 'boolean',
            'take_profit_pips' => 'required|numeric|min:1|max:10000',
            'stop_loss_pips' => 'required|numeric|min:1|max:10000',
            'max_daily_trades' => 'required|integer|min:1|max:1000',
            'max_daily_loss' => 'required|numeric|min:1|max:1000000',
            'whatsapp_number' => 'nullable|string|max:20',
            'base_balance' => 'nullable|numeric|min:1|max:1000000',
            'base_lot_size' => 'nullable|numeric|min:0.01|max:100',
            'demo_server' => 'nullable|string|max:100',
            'demo_account' => 'nullable|string|max:50',
            'demo_email' => 'nullable|email|max:100',
            'demo_phone' => 'nullable|string|max:20',
            'demo_deposit' => 'nullable|numeric|min:0|max:100000000',
        ]);

        if ($request->hasFile('bot_file')) {
            if ($bot->bot_file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($bot->bot_file_path);
            }
            $validated['bot_file_path'] = $request->file('bot_file')->store('mt5-bots', 'public');
        }
        unset($validated['bot_file']);

        $validated['auto_trade'] = $request->boolean('auto_trade');

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

        $oldValues = $bot->only(['name', 'mt5_account_number', 'mt5_server', 'mode', 'auto_trade', 'take_profit_pips', 'stop_loss_pips', 'max_daily_trades', 'max_daily_loss', 'whatsapp_number', 'base_balance', 'base_lot_size', 'demo_server', 'demo_account', 'demo_email', 'demo_phone', 'demo_deposit']);
        $bot->update($validated);
        $newValues = $bot->only(array_keys($oldValues));

        ActivityLogger::log('update', 'Mt5BotConfig', $bot->id, "Updated MT5 bot config: {$bot->name}", $oldValues, $newValues);
        Cache::forget('mt5_bot_stats');

        return redirect()->route('admin.mt5-bot.show', $bot)->with('success', 'Bot configuration updated successfully.');
    }

    public function destroy(Mt5BotConfig $bot)
    {
        $title = $bot->name;
        $oldValues = $bot->only(['name', 'mt5_account_number', 'status', 'mode']);

        DB::transaction(function () use ($bot) {
            $bot->logs()->delete();
            $bot->trades()->delete();
            $bot->delete();
        });

        ActivityLogger::log('delete', 'Mt5BotConfig', $bot->id, "Deleted MT5 bot config: {$title}", $oldValues, null);
        Cache::forget('mt5_bot_stats');

        return redirect()->route('admin.mt5-bot.index')->with('success', 'Bot configuration deleted successfully.');
    }

    public function restore(Mt5BotConfig $bot)
    {
        if (!$bot->trashed()) {
            return back()->with('error', 'Bot is not deleted.');
        }

        $bot->restore();
        Cache::forget('mt5_bot_stats');

        ActivityLogger::log('restore', 'Mt5BotConfig', $bot->id, "Restored MT5 bot config: {$bot->name}");

        return redirect()->route('admin.mt5-bot.show', $bot)->with('success', 'Bot restored successfully.');
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

        return back()->with('success', "Bot {$newStatus} successfully.");
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

        return back()->with('success', 'Stats recalculated successfully.');
    }
}
