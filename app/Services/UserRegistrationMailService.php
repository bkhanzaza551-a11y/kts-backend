<?php

namespace App\Services;

use App\Mail\Mt5BotFileMail;
use App\Mail\WelcomeMail;
use App\Models\Mt5BotConfig;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserRegistrationMailService
{
    /**
     * Dispatch post-registration emails (Welcome Email + Optional Auto MT5 Bot File).
     */
    public static function dispatch(User $user, string $plainPassword = 'ktsbot123', string $source = 'system'): void
    {
        // 1. Dispatch official Welcome Email if not already sent
        try {
            $welcomeAlreadySent = DB::table('email_logs')
                ->where('user_id', $user->id)
                ->where('type', 'welcome')
                ->where('status', 'sent')
                ->exists();

            if (!$welcomeAlreadySent) {
                Mail::to($user->email, $user->name)
                    ->send(new WelcomeMail($user, $plainPassword));

                DB::table('email_logs')->insert([
                    'user_id'    => $user->id,
                    'type'       => 'welcome',
                    'status'     => 'sent',
                    'resent_by'  => $source,
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning("WelcomeMail dispatch failed for {$user->email}: " . $e->getMessage());
        }

        // 2. Dispatch MT5 Bot File if Auto-Send toggle is enabled by Super Admin
        try {
            $isAutoBotEnabled = SystemSetting::getValue('auto_send_bot_on_registration', '0') === '1';

            if ($isAutoBotEnabled) {
                $botAlreadySent = DB::table('email_logs')
                    ->where('user_id', $user->id)
                    ->where('type', 'mt5_bot_auto')
                    ->where('status', 'sent')
                    ->exists();

                if (!$botAlreadySent) {
                    $bot = Mt5BotConfig::whereNotNull('bot_file_data')
                        ->orWhereNotNull('bot_file_name')
                        ->orderBy('id', 'asc')
                        ->first() ?? Mt5BotConfig::orderBy('id', 'asc')->first();

                    if ($bot && $bot->hasBotFile()) {
                        Mail::to($user->email, $user->name)
                            ->send(new Mt5BotFileMail($user, $bot, 'Welcome to KTS Markets! Here is your official MT5 automated trading bot file.'));

                        DB::table('email_logs')->insert([
                            'user_id'    => $user->id,
                            'type'       => 'mt5_bot_auto',
                            'status'     => 'sent',
                            'resent_by'  => $source,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Auto Mt5BotFileMail dispatch failed for {$user->email}: " . $e->getMessage());
        }
    }
}
