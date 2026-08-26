<?php

namespace Database\Seeders;

use App\Models\Mt5BotConfig;
use App\Models\Mt5BotLog;
use App\Models\Mt5BotTrade;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Crypt;

class Mt5BotSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = \App\Models\User::where('email', 'admin@kts10pipsbots.com')->first()?->id;

        if (!$adminId) {
            $this->command?->warn('Admin user not found, skipping MT5 bot seeder.');
            return;
        }

        $bots = [
            [
                'name' => 'KTS Scalper Pro',
                'description' => 'High-frequency scalping bot targeting 10 pips per trade on major pairs.',
                'mt5_account_number' => '100123456',
                'mt5_server' => 'MetaQuotes-Demo',
                'mt5_password_encrypted' => Crypt::encryptString('Demo123!'),
                'api_key' => null,
                'api_secret' => null,
                'mode' => 'demo',
                'status' => 'active',
                'auto_trade' => true,
                'lot_size' => 0.01,
                'max_lot_size' => 1.00,
                'take_profit_pips' => 10,
                'stop_loss_pips' => 20,
                'max_daily_trades' => 20,
                'max_daily_loss' => 100,
                'balance' => 10000.00,
                'equity' => 10150.00,
                'total_profit' => 2500.00,
                'total_loss' => 1350.00,
                'total_trades' => 156,
                'winning_trades' => 98,
                'losing_trades' => 58,
                'created_by' => $adminId,
            ],
            [
                'name' => 'KTS Trend Follower',
                'description' => 'Medium-term trend following strategy on gold and indices.',
                'mt5_account_number' => '100789012',
                'mt5_server' => 'MetaQuotes-Demo',
                'mt5_password_encrypted' => Crypt::encryptString('Demo123!'),
                'api_key' => null,
                'api_secret' => null,
                'mode' => 'demo',
                'status' => 'inactive',
                'auto_trade' => false,
                'lot_size' => 0.05,
                'max_lot_size' => 5.00,
                'take_profit_pips' => 50,
                'stop_loss_pips' => 30,
                'max_daily_trades' => 5,
                'max_daily_loss' => 500,
                'balance' => 25000.00,
                'equity' => 25000.00,
                'total_profit' => 8000.00,
                'total_loss' => 5200.00,
                'total_trades' => 89,
                'winning_trades' => 52,
                'losing_trades' => 37,
                'created_by' => $adminId,
            ],
            [
                'name' => 'KTS Backtest Engine',
                'description' => 'Backtesting bot for strategy optimization on historical data.',
                'mt5_account_number' => '100345678',
                'mt5_server' => 'MetaQuotes-Demo',
                'mt5_password_encrypted' => Crypt::encryptString('Demo123!'),
                'api_key' => null,
                'api_secret' => null,
                'mode' => 'backtest',
                'status' => 'error',
                'auto_trade' => false,
                'lot_size' => 0.10,
                'max_lot_size' => 10.00,
                'take_profit_pips' => 20,
                'stop_loss_pips' => 15,
                'max_daily_trades' => 50,
                'max_daily_loss' => 1000,
                'balance' => 50000.00,
                'equity' => 49800.00,
                'total_profit' => 15000.00,
                'total_loss' => 15200.00,
                'total_trades' => 312,
                'winning_trades' => 170,
                'losing_trades' => 142,
                'created_by' => $adminId,
            ],
        ];

        foreach ($bots as $botData) {
            $bot = Mt5BotConfig::updateOrCreate(
                ['mt5_account_number' => $botData['mt5_account_number']],
                $botData
            );

            if ($bot->wasRecentlyCreated) {
                $statuses = ['info', 'success', 'warning', 'error'];
                $actions = ['started', 'trade_executed', 'position_closed', 'error_occurred', 'reconnected'];
                $messages = [
                    'Bot started successfully',
                    'EURUSD buy order executed at 1.0845',
                    'GBPUSD position closed with +$12.50 profit',
                    'Connection timeout - retrying in 5s',
                    'Reconnected to MT5 server',
                    'XAUUSD sell order executed at 2415.30',
                    'Daily loss limit approaching: $85/$100',
                ];

                for ($i = 0; $i < 8; $i++) {
                    Mt5BotLog::create([
                        'bot_config_id' => $bot->id,
                        'level' => $statuses[array_rand($statuses)],
                        'action' => $actions[array_rand($actions)],
                        'message' => $messages[array_rand($messages)],
                        'metadata' => ['trade_id' => rand(1000, 9999), 'pair' => ['EURUSD', 'GBPUSD', 'XAUUSD', 'USDJPY'][array_rand([0,1,2,3])]],
                    ]);
                }

                $pairs = ['EURUSD', 'GBPUSD', 'XAUUSD', 'USDJPY', 'AUDUSD'];
                $types = ['buy', 'sell'];
                $tradeStatuses = ['closed', 'open'];
                $ticketBase = rand(100000, 999999);

                for ($i = 0; $i < 5; $i++) {
                    $type = $types[array_rand($types)];
                    $pair = $pairs[array_rand($pairs)];
                    $profit = rand(-500, 500) / 10;
                    $tStatus = $tradeStatuses[array_rand($tradeStatuses)];
                    $lot = $bot->lot_size * rand(1, 5);

                    Mt5BotTrade::create([
                        'bot_config_id' => $bot->id,
                        'ticket' => (string) ($ticketBase + $i),
                        'symbol' => $pair,
                        'type' => $type,
                        'volume' => $lot,
                        'open_price' => $type === 'buy' ? 1.0800 + rand(0, 100) / 10000 : 1.0900 - rand(0, 100) / 10000,
                        'close_price' => $tStatus === 'closed' ? ($type === 'buy' ? 1.0810 + rand(0, 50) / 10000 : 1.0890 - rand(0, 50) / 10000) : null,
                        'stop_loss' => $type === 'buy' ? 1.0780 : 1.0920,
                        'take_profit' => $type === 'buy' ? 1.0820 : 1.0880,
                        'profit' => $tStatus === 'closed' ? $profit : 0,
                        'swap' => $tStatus === 'closed' ? rand(-50, 50) / 10 : 0,
                        'commission' => $lot * -1.5,
                        'status' => $tStatus,
                        'opened_at' => now()->subHours(rand(1, 72)),
                        'closed_at' => $tStatus === 'closed' ? now()->subHours(rand(0, 24)) : null,
                    ]);
                }
            }
        }
    }
}
