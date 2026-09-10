<?php

use App\Models\Mt5BotConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Consolidate into 1 single master bot
        $bots = Mt5BotConfig::orderBy('id')->get();

        if ($bots->count() > 1) {
            $primary = $bots->first();
            // Re-assign any trades/logs from extra bots to the primary bot
            foreach ($bots->skip(1) as $extraBot) {
                DB::table('mt5_bot_trades')->where('bot_config_id', $extraBot->id)->update(['bot_config_id' => $primary->id]);
                DB::table('mt5_bot_logs')->where('bot_config_id', $extraBot->id)->update(['bot_config_id' => $primary->id]);
                $extraBot->forceDelete();
            }
        }

        $bot = Mt5BotConfig::first();
        if ($bot) {
            $bot->update([
                'name' => 'KTS10 Pips Bot',
                'description' => 'Automated Gold (XAUUSD) & Major Forex Pairs High-Precision Trading Bot',
                'base_balance' => 100.00,
                'base_lot_size' => 0.01,
                'take_profit_pips' => 10.00,
                'stop_loss_pips' => 5.00,
                'max_daily_trades' => 10,
                'status' => 'active',
                'mode' => 'live',
                'auto_trade' => true,
                'whatsapp_number' => '+923371244640',
            ]);
        } else {
            Mt5BotConfig::create([
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
    }

    public function down(): void
    {
        // No down needed
    }
};
