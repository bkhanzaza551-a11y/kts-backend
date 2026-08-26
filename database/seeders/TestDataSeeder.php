<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\ChatSticker;
use App\Models\ChatStickerPack;
use App\Models\Course;
use App\Models\EducationCategory;
use App\Models\LegalPage;
use App\Models\Mt5BotConfig;
use App\Models\NotificationTemplate;
use App\Models\Signal;
use App\Models\SignalCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSignalCategories();
        $this->seedSignals();
        $this->seedEducation();
        $this->seedChatRooms();
        $this->seedStickerPacks();
        $this->seedNotificationTemplates();
        $this->seedMt5Bots();
    }

    private function seedSignalCategories(): void
    {
        $categories = [
            ['name' => 'Forex', 'slug' => 'forex', 'description' => 'Currency pair signals', 'icon' => 'bi-currency-exchange', 'color' => '#0d6efd', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Gold', 'slug' => 'gold', 'description' => 'XAUUSD and gold signals', 'icon' => 'bi-gem', 'color' => '#ffc107', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Crypto', 'slug' => 'crypto', 'description' => 'Cryptocurrency signals', 'icon' => 'bi-bitcoin', 'color' => '#f7931a', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Indices', 'slug' => 'indices', 'description' => 'Stock market indices', 'icon' => 'bi-graph-up', 'color' => '#198754', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Scalping', 'slug' => 'scalping', 'description' => 'Quick in/out trades', 'icon' => 'bi-lightning', 'color' => '#dc3545', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($categories as $cat) {
            SignalCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }

    private function seedSignals(): void
    {
        if (Signal::count() > 0) return;

        Signal::create([
            'title' => 'BUY EUR/USD @ 1.0850',
            'symbol' => 'EURUSD',
            'direction' => 'buy',
            'entry_price' => 1.0850,
            'take_profit' => 1.0900,
            'stop_loss' => 1.0820,
            'status' => 'active',
            'result' => 'pending',
            'created_by' => 1,
        ]);

        Signal::create([
            'title' => 'SELL XAUUSD @ 2420',
            'symbol' => 'XAUUSD',
            'direction' => 'sell',
            'entry_price' => 2420.00,
            'take_profit' => 2380.00,
            'stop_loss' => 2440.00,
            'status' => 'active',
            'result' => 'pending',
            'created_by' => 1,
        ]);

        Signal::create([
            'title' => 'BUY BTCUSDT @ 68500',
            'symbol' => 'BTCUSDT',
            'direction' => 'buy',
            'entry_price' => 68500.00,
            'take_profit' => 71000.00,
            'stop_loss' => 67000.00,
            'status' => 'closed',
            'result' => 'win',
            'pips_result' => 2500,
            'created_by' => 1,
        ]);
    }

    private function seedEducation(): void
    {
        if (Course::count() > 0) return;

        $cats = EducationCategory::all();
        if ($cats->isEmpty()) return;

        Course::create([
            'category_id' => $cats->first()->id,
            'created_by' => 1,
            'title' => 'Trading Basics',
            'slug' => 'trading-basics',
            'description' => 'Learn the fundamentals of trading.',
            'difficulty' => 'beginner',
            'estimated_hours' => 5,
            'is_free' => true,
            'is_published' => true,
            'is_featured' => true,
            'sort_order' => 1,
        ]);

        Course::create([
            'category_id' => $cats->last()->id ?? $cats->first()->id,
            'created_by' => 1,
            'title' => 'Advanced Technical Analysis',
            'slug' => 'advanced-technical-analysis',
            'description' => 'Master candlestick patterns, indicators, and chart analysis.',
            'difficulty' => 'advanced',
            'estimated_hours' => 12,
            'is_free' => false,
            'price' => 49.99,
            'is_published' => true,
            'sort_order' => 2,
        ]);
    }

    private function seedChatRooms(): void
    {
        if (ChatRoom::count() > 0) return;

        $rooms = [
            ['name' => 'VIP Signals', 'slug' => 'vip-signals', 'description' => 'Exclusive VIP trading signals', 'is_public' => false, 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Analysis', 'slug' => 'analysis', 'description' => 'Technical analysis discussions', 'is_public' => true, 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Beginner Help', 'slug' => 'beginner-help', 'description' => 'Ask questions, get help', 'is_public' => true, 'is_active' => true, 'sort_order' => 4],
        ];

        foreach ($rooms as $room) {
            ChatRoom::create($room);
        }
    }

    private function seedStickerPacks(): void
    {
        if (ChatStickerPack::count() > 0) return;

        $pack1 = ChatStickerPack::create([
            'name' => 'Trading Emojis',
            'slug' => 'trading-emojis',
            'description' => 'Express your trading mood',
            'is_active' => true,
            'sort_order' => 1,
            'created_by' => 1,
        ]);

        $stickers = [
            ['name' => 'Bull Run', 'image_url' => 'stickers/trading-emojis/bull.png', 'file_size' => '12KB', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Bear Market', 'image_url' => 'stickers/trading-emojis/bear.png', 'file_size' => '11KB', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'To The Moon', 'image_url' => 'stickers/trading-emojis/moon.png', 'file_size' => '10KB', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Loss', 'image_url' => 'stickers/trading-emojis/loss.png', 'file_size' => '9KB', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Profit', 'image_url' => 'stickers/trading-emojis/profit.png', 'file_size' => '11KB', 'is_active' => true, 'sort_order' => 5],
        ];

        foreach ($stickers as $s) {
            ChatSticker::create(array_merge($s, ['pack_id' => $pack1->id]));
        }

        $pack2 = ChatStickerPack::create([
            'name' => 'Fun Stickers',
            'slug' => 'fun-stickers',
            'description' => 'Have fun in chat',
            'is_active' => true,
            'sort_order' => 2,
            'created_by' => 1,
        ]);

        ChatSticker::create(['pack_id' => $pack2->id, 'name' => 'Fire', 'image_url' => 'stickers/fun-stickers/fire.png', 'file_size' => '8KB', 'is_active' => true, 'sort_order' => 1]);
        ChatSticker::create(['pack_id' => $pack2->id, 'name' => 'Rocket', 'image_url' => 'stickers/fun-stickers/rocket.png', 'file_size' => '10KB', 'is_active' => true, 'sort_order' => 2]);
        ChatSticker::create(['pack_id' => $pack2->id, 'name' => 'Diamond', 'image_url' => 'stickers/fun-stickers/diamond.png', 'file_size' => '9KB', 'is_active' => true, 'sort_order' => 3]);
    }

    private function seedNotificationTemplates(): void
    {
        if (NotificationTemplate::count() > 0) return;

        $templates = [
            ['name' => 'Signal Alert', 'slug' => 'signal-alert', 'title' => 'New Signal: :symbol', 'body' => 'A new :type signal for :symbol has been posted. Entry: :entry, TP: :tp, SL: :sl', 'type' => 'signal', 'event' => 'signal.created', 'channel' => 'push', 'is_active' => true],
            ['name' => 'Signal Closed', 'slug' => 'signal-closed', 'title' => 'Signal Closed: :symbol', 'body' => 'Your signal for :symbol has been closed. Result: :result', 'type' => 'signal', 'event' => 'signal.closed', 'channel' => 'push', 'is_active' => true],
            ['name' => 'Welcome', 'slug' => 'welcome', 'title' => 'Welcome to KTS 10 Pips Bots!', 'body' => 'Thank you for joining. Start trading with our signals today!', 'type' => 'info', 'event' => 'user.registered', 'channel' => 'email', 'is_active' => true],
            ['name' => 'Subscription Expiry', 'slug' => 'subscription-expiry', 'title' => 'Subscription Expiring Soon', 'body' => 'Your subscription expires in :days days. Renew now to keep access.', 'type' => 'warning', 'event' => 'subscription.expiring', 'channel' => 'push', 'is_active' => true],
            ['name' => 'Password Reset', 'slug' => 'password-reset', 'title' => 'Password Reset Request', 'body' => 'Click the link to reset your password: :link', 'type' => 'security', 'event' => 'user.password_reset', 'channel' => 'email', 'is_active' => true],
        ];

        foreach ($templates as $t) {
            NotificationTemplate::create($t);
        }
    }

    private function seedMt5Bots(): void
    {
        if (Mt5BotConfig::where('name', 'KTS Scalper Bot')->exists()) return;

        Mt5BotConfig::create([
            'name' => 'KTS Scalper Bot',
            'description' => 'Automated scalping bot for EURUSD',
            'mt5_account_number' => '12345678',
            'mt5_server' => 'Exness-MT5Real',
            'mt5_password_encrypted' => encrypt('test_password'),
            'status' => 'active',
            'mode' => 'demo',
            'auto_trade' => true,
            'lot_size' => 0.01,
            'max_lot_size' => 1.00,
            'take_profit_pips' => 10,
            'stop_loss_pips' => 20,
            'balance' => 10000.00,
            'equity' => 10250.00,
            'total_profit' => 250.00,
            'total_trades' => 45,
            'winning_trades' => 30,
            'losing_trades' => 15,
            'created_by' => 1,
        ]);

        Mt5BotConfig::create([
            'name' => 'KTS Swing Trader',
            'description' => 'Swing trading on gold and indices',
            'mt5_account_number' => '87654321',
            'mt5_server' => 'Exness-MT5Real',
            'mt5_password_encrypted' => encrypt('test_password2'),
            'status' => 'active',
            'mode' => 'live',
            'auto_trade' => false,
            'lot_size' => 0.05,
            'max_lot_size' => 5.00,
            'take_profit_pips' => 100,
            'stop_loss_pips' => 50,
            'balance' => 25000.00,
            'equity' => 25800.00,
            'total_profit' => 800.00,
            'total_trades' => 12,
            'winning_trades' => 8,
            'losing_trades' => 4,
            'created_by' => 1,
        ]);
    }
}
