<?php

use App\Models\Signal;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Cleanup any incomplete test signals with missing prices
        $invalidSignals = Signal::whereNull('entry_price')
            ->orWhereNull('take_profit')
            ->orWhereNull('stop_loss')
            ->orWhere('entry_price', '<=', 0)
            ->get();

        foreach ($invalidSignals as $signal) {
            $signal->categories()->detach();
            $signal->forceDelete();
        }
    }

    public function down(): void
    {
        // No rollback needed
    }
};
