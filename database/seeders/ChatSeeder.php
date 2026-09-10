<?php

namespace Database\Seeders;

use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatRestrictedWord;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = \App\Models\User::where('email', 'admin@kts10pipsbots.com')->first()?->id;
        if (!$adminId) return;

        $rooms = [
            ['name' => 'Global Chat', 'slug' => 'general', 'description' => 'Global community trading discussion', 'is_active' => true, 'is_public' => true, 'sort_order' => 1],
        ];

        foreach ($rooms as $roomData) {
            ChatRoom::updateOrCreate(['slug' => $roomData['slug']], $roomData);
        }

        ChatRoom::where('slug', '!=', 'general')->delete();

        $users = \App\Models\User::inRandomOrder()->limit(5)->get();
        $generalRoom = ChatRoom::where('slug', 'general')->first();

        if ($generalRoom && $users->count()) {
            $messages = [
                'Hello everyone! How is trading today?',
                'EURUSD looking bullish today',
                'Just closed a profitable trade on GBPJPY',
                'Anyone using the KTS bot? How are results?',
                'Gold is pumping! XAUUSD buy signal was perfect',
                'Thanks for the signals team!',
                'What lot size are you guys using?',
                'The education videos are really helpful',
                'Can someone explain stop loss placement?',
                'Demo account is working great for practice',
            ];

            foreach ($messages as $i => $msg) {
                $user = $users[$i % $users->count()];
                ChatMessage::create([
                    'room_id' => $generalRoom->id,
                    'user_id' => $user->id,
                    'message' => $msg,
                    'type' => 'text',
                    'is_flagged' => $i === 3,
                ]);
            }
        }


        $restrictedWords = [
            ['word' => 'scam', 'replacement' => '***', 'is_active' => true, 'created_by' => $adminId],
            ['word' => 'spam', 'replacement' => '***', 'is_active' => true, 'created_by' => $adminId],
            ['word' => 'hack', 'replacement' => '***', 'is_active' => true, 'created_by' => $adminId],
        ];

        foreach ($restrictedWords as $wordData) {
            ChatRestrictedWord::updateOrCreate(['word' => $wordData['word']], $wordData);
        }
    }
}
