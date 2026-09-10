<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $defaultRooms = [
            [
                'name' => 'Global Chat',
                'slug' => 'general',
                'description' => 'Global community trading discussion',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'VIP Signals',
                'slug' => 'vip-signals',
                'description' => 'Exclusive VIP trading signals',
                'is_active' => true,
                'is_public' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Analysis',
                'slug' => 'analysis',
                'description' => 'Technical analysis discussions',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Beginner Help',
                'slug' => 'beginner-help',
                'description' => 'Ask questions, get help',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Signals',
                'slug' => 'signals',
                'description' => 'Live signal discussions',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($defaultRooms as $r) {
            ChatRoom::updateOrCreate(['slug' => $r['slug']], $r);
        }

        $generalRoom = ChatRoom::where('slug', 'general')->first();
        if ($generalRoom && ChatMessage::where('room_id', $generalRoom->id)->count() === 0) {
            $adminUser = User::where('email', 'admin@ktsmarkets.com')->orWhere('email', 'admin@kts10pipsbots.com')->first()
                ?? User::first();
            
            $users = User::all();
            if ($users->count() === 0 && $adminUser) {
                $users = collect([$adminUser]);
            }

            if ($adminUser && $users->count() > 0) {
                $messages = [
                    'Welcome to KTS Markets Global Community! 🚀',
                    'EURUSD looking bullish today, watch out for the London session breakout.',
                    'Just hit TP on Gold XAUUSD +45 pips! 💰',
                    'Remember to always manage risk with 1-2% max per trade guys.',
                    'The AI bot scalper performance this week has been exceptional! 🔥',
                    'Anyone watching US30 for the NY open?',
                    'Welcome new traders, feel free to ask any questions in chat! 📈',
                ];

                foreach ($messages as $idx => $msg) {
                    $sender = $idx === 0 ? $adminUser : $users->random();
                    ChatMessage::create([
                        'room_id' => $generalRoom->id,
                        'user_id' => $sender->id,
                        'message' => $msg,
                        'type' => 'text',
                        'is_pinned' => $idx === 0,
                        'created_at' => now()->subMinutes((count($messages) - $idx) * 15),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
    }
};
