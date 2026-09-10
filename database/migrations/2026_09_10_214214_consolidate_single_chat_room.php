<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            // 1. Ensure master Global Chat room exists
            $masterRoom = \App\Models\ChatRoom::firstOrCreate(
                ['slug' => 'general'],
                [
                    'name' => 'Global Chat',
                    'description' => 'Global community trading discussion',
                    'is_active' => true,
                    'is_public' => true,
                    'sort_order' => 1,
                ]
            );

            // Update master room properties if needed
            $masterRoom->update([
                'name' => 'Global Chat',
                'description' => 'Global community trading discussion',
                'is_active' => true,
                'is_public' => true,
                'sort_order' => 1,
            ]);

            // 2. Reassign all existing messages from other rooms to the master Global Chat room
            \App\Models\ChatMessage::where('room_id', '!=', $masterRoom->id)
                ->update(['room_id' => $masterRoom->id]);

            // 3. Delete all other chat rooms so only 1 master room remains
            \App\Models\ChatRoom::where('id', '!=', $masterRoom->id)->delete();
        } catch (\Throwable $e) {
            // Ignored if tables not yet present
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
