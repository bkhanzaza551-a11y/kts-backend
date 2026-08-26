<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE mt5_bot_configs_backup AS SELECT id, name, mt5_account_number, mt5_server, mt5_password_encrypted, api_key, api_secret, mode, status, auto_trade, lot_size, max_lot_size, take_profit_pips, stop_loss_pips, max_daily_trades, max_daily_loss, balance, equity, total_profit, total_loss, total_trades, winning_trades, losing_trades, error_message, created_by, updated_at, created_at, deleted_at FROM mt5_bot_configs');
            DB::statement('DROP TABLE mt5_bot_configs');
            Schema::create('mt5_bot_configs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('mt5_account_number')->unique();
                $table->string('mt5_server');
                $table->text('mt5_password_encrypted');
                $table->text('api_key')->nullable();
                $table->text('api_secret')->nullable();
                $table->enum('mode', ['live', 'demo', 'backtest'])->default('demo');
                $table->enum('status', ['active', 'inactive', 'error'])->default('inactive');
                $table->boolean('auto_trade')->default(false);
                $table->decimal('lot_size', 10, 2)->default(0.01);
                $table->decimal('max_lot_size', 10, 2)->default(1.00);
                $table->decimal('take_profit_pips', 10, 2)->default(10);
                $table->decimal('stop_loss_pips', 10, 2)->default(20);
                $table->integer('max_daily_trades')->default(10);
                $table->decimal('max_daily_loss', 12, 2)->default(100);
                $table->decimal('balance', 12, 2)->default(0);
                $table->decimal('equity', 12, 2)->default(0);
                $table->decimal('total_profit', 12, 2)->default(0);
                $table->decimal('total_loss', 12, 2)->default(0);
                $table->integer('total_trades')->default(0);
                $table->integer('winning_trades')->default(0);
                $table->integer('losing_trades')->default(0);
                $table->text('error_message')->nullable();
                $table->foreignId('created_by')->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement('INSERT INTO mt5_bot_configs (id, name, mt5_account_number, mt5_server, mt5_password_encrypted, api_key, api_secret, mode, status, auto_trade, lot_size, max_lot_size, take_profit_pips, stop_loss_pips, max_daily_trades, max_daily_loss, balance, equity, total_profit, total_loss, total_trades, winning_trades, losing_trades, error_message, created_by, updated_at, created_at, deleted_at) SELECT id, name, mt5_account_number, mt5_server, mt5_password_encrypted, api_key, api_secret, mode, status, auto_trade, lot_size, max_lot_size, take_profit_pips, stop_loss_pips, max_daily_trades, max_daily_loss, balance, equity, total_profit, total_loss, total_trades, winning_trades, losing_trades, error_message, created_by, updated_at, created_at, deleted_at FROM mt5_bot_configs_backup');
            DB::statement('DROP TABLE mt5_bot_configs_backup');
        } else {
            Schema::table('mt5_bot_configs', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
                $table->integer('max_daily_trades')->default(10)->change();
                $table->text('api_key')->nullable()->change();
                $table->text('api_secret')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('CREATE TABLE mt5_bot_configs_backup AS SELECT * FROM mt5_bot_configs');
            DB::statement('DROP TABLE mt5_bot_configs');
            Schema::create('mt5_bot_configs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('mt5_account_number')->unique();
                $table->string('mt5_server');
                $table->text('mt5_password_encrypted');
                $table->string('api_key')->nullable();
                $table->string('api_secret')->nullable();
                $table->enum('mode', ['live', 'demo', 'backtest'])->default('demo');
                $table->enum('status', ['active', 'inactive', 'error'])->default('inactive');
                $table->boolean('auto_trade')->default(false);
                $table->decimal('lot_size', 10, 2)->default(0.01);
                $table->decimal('max_lot_size', 10, 2)->default(1.00);
                $table->decimal('take_profit_pips', 10, 2)->default(10);
                $table->decimal('stop_loss_pips', 10, 2)->default(20);
                $table->decimal('max_daily_trades', 10, 0)->default(10);
                $table->decimal('max_daily_loss', 12, 2)->default(100);
                $table->decimal('balance', 12, 2)->default(0);
                $table->decimal('equity', 12, 2)->default(0);
                $table->decimal('total_profit', 12, 2)->default(0);
                $table->decimal('total_loss', 12, 2)->default(0);
                $table->integer('total_trades')->default(0);
                $table->integer('winning_trades')->default(0);
                $table->integer('losing_trades')->default(0);
                $table->text('error_message')->nullable();
                $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->softDeletes();
            });
            DB::statement('INSERT INTO mt5_bot_configs SELECT * FROM mt5_bot_configs_backup');
            DB::statement('DROP TABLE mt5_bot_configs_backup');
        } else {
            Schema::table('mt5_bot_configs', function (Blueprint $table) {
                $table->string('description')->nullable()->change();
                $table->decimal('max_daily_trades', 10, 0)->default(10)->change();
                $table->string('api_key')->nullable()->change();
                $table->string('api_secret')->nullable()->change();
            });
        }
    }
};
