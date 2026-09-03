<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StickerApiController;
use App\Http\Controllers\Api\ChatApiController;
use App\Http\Controllers\Api\DemoAccountApiController;
use App\Http\Controllers\Api\LegalPageApiController;
use App\Http\Controllers\Api\SignalApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\EducationApiController;
use App\Http\Controllers\Api\BotApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\AiChatbotApiController;
use App\Http\Controllers\Api\SupportTicketApiController;
use App\Http\Controllers\Api\MarketDataApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:3,1')->name('api.register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('api.login');
    Route::post('google-auth', [AuthController::class, 'googleAuth'])->middleware('throttle:10,1')->name('api.google-auth');

    // Legal Pages (Public - no auth needed)
    Route::get('legal', [LegalPageApiController::class, 'list'])->name('api.legal.list');
    Route::get('legal/{slug}', [LegalPageApiController::class, 'show'])->name('api.legal.show');

    // Demo Account Instructions (Public - mobile needs before login)
    Route::get('demo-account/instructions', [DemoAccountApiController::class, 'instructions'])->name('api.demo.instructions');

    // Sticker List (Public - users browse stickers)
    Route::get('stickers', [StickerApiController::class, 'index'])->name('api.stickers.index');

    // Auth verification (public - part of login flow)
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1')->name('api.verify-otp');
    Route::post('verify-security-code', [AuthController::class, 'verifySecurityCode'])->middleware('throttle:5,1')->name('api.verify-security-code');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1')->name('api.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1')->name('api.reset-password');
    Route::post('verify-email-otp', [AuthController::class, 'verifyEmailOtp'])->middleware('throttle:5,1')->name('api.verify-email-otp');
    Route::post('resend-email-otp', [AuthController::class, 'resendEmailOtp'])->middleware('throttle:3,1')->name('api.resend-email-otp');

    // Live Email Diagnostics & Test Route (LOCAL ONLY)
    if (app()->environment('local')) {
        Route::get('test-email', function (\Illuminate\Http\Request $request) {
            $to = $request->query('to', 'huntergaming5555566@gmail.com');
            $start = microtime(true);
            
            $diag = [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'username' => config('mail.mailers.smtp.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ];

            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Hello!\n\nThis is a live test email from KTS Markets on Railway production server.\n\n" .
                    "Server Time: " . now()->toDateTimeString() . " UTC\n" .
                    "Recipient: {$to}\n" .
                    "Host: {$diag['host']}:{$diag['port']}\n" .
                    "Encryption: {$diag['encryption']}\n\n" .
                    "If you are seeing this, Gmail SMTP is 100% working on Railway!\n\n" .
                    "Regards,\nKTS Markets Team",
                    function ($message) use ($to) {
                        $message->to($to)
                            ->subject('KTS Markets - Railway Live SMTP Test');
                    }
                );

                $duration = round((microtime(true) - $start) * 1000, 2);

                return response()->json([
                    'success' => true,
                    'status' => 'EMAIL_SENT_SUCCESSFULLY',
                    'message' => "Test email successfully sent to {$to}",
                    'duration_ms' => $duration,
                    'server_diagnostics' => $diag,
                ], 200);
            } catch (\Throwable $e) {
                $duration = round((microtime(true) - $start) * 1000, 2);

                return response()->json([
                    'success' => false,
                    'status' => 'EMAIL_DISPATCH_FAILED',
                    'error_message' => $e->getMessage(),
                    'error_class' => get_class($e),
                    'duration_ms' => $duration,
                    'server_diagnostics' => $diag,
                ], 500);
            }
        })->name('api.test-email');

        Route::get('seed-db', function () {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                $output = \Illuminate\Support\Facades\Artisan::output();
                return response()->json([
                    'success' => true,
                    'message' => 'Database migrated and seeded successfully with test users!',
                    'users' => [
                        ['email' => 'user@ktsmarkets.com', 'password' => 'Password123!'],
                        ['email' => 'test@ktsmarkets.com', 'password' => 'Password123!'],
                        ['email' => 'admin@ktsmarkets.com', 'password' => 'Password123!'],
                    ],
                    'output' => $output,
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                ], 500);
            }
        })->name('api.seed-db');
    }

    // Payment Plans (public - mobile shows before login)
    Route::get('payments/plans', [PaymentApiController::class, 'plans'])->name('api.payments.plans');

    Route::middleware(['auth:sanctum', 'prevent.deleted'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('profile', [AuthController::class, 'profile'])->name('api.profile');
        Route::put('profile', [AuthController::class, 'updateProfile'])->name('api.profile.update');
        Route::delete('profile', [AuthController::class, 'deleteAccount'])->name('api.profile.delete');
        Route::put('change-password', [AuthController::class, 'changePassword'])->middleware('throttle:5,1')->name('api.password.change');

        // Device Registration (FCM tokens)
        Route::post('device/register', [\App\Http\Controllers\Api\DeviceController::class, 'register'])->name('api.device.register');
        Route::post('device/unregister', [\App\Http\Controllers\Api\DeviceController::class, 'unregister'])->name('api.device.unregister');

        // Chat - Rooms & Messages
        Route::get('chat/rooms', [ChatApiController::class, 'rooms'])->name('api.chat.rooms');
        Route::get('chat/rooms/{room:slug}/messages', [ChatApiController::class, 'messages'])->name('api.chat.messages');
        Route::post('chat/rooms/{room:slug}/messages', [ChatApiController::class, 'send'])->middleware('throttle:30,1')->name('api.chat.send');
        Route::post('chat/messages/{id}/report', [ChatApiController::class, 'reportMessage'])->name('api.chat.messages.report');
        Route::post('chat/users/{id}/block', [ChatApiController::class, 'blockUser'])->name('api.chat.users.block');
        Route::post('chat/users/{id}/unblock', [ChatApiController::class, 'unblockUser'])->name('api.chat.users.unblock');
        Route::get('chat/blocked-users', [ChatApiController::class, 'blockedUsers'])->name('api.chat.blocked-users');
        Route::get('chat/rooms/{room}/pinned', [ChatApiController::class, 'pinnedMessages'])->name('api.chat.pinned');

        // Chat - Stickers
        Route::get('chat/stickers', [ChatApiController::class, 'stickers'])->name('api.chat.stickers');

        // Sticker usage tracking (auth required)
        Route::post('stickers/use', [StickerApiController::class, 'store'])->name('api.stickers.store');

        // Demo Account Requests
        Route::post('demo-account/request', [DemoAccountApiController::class, 'store'])->name('api.demo.store');
        Route::get('demo-account/requests', [DemoAccountApiController::class, 'myRequests'])->name('api.demo.my-requests');
        Route::get('demo-account/requests/{demoRequest}', [DemoAccountApiController::class, 'show'])->name('api.demo.show');

        // Signals
        Route::get('signals', [SignalApiController::class, 'index'])->name('api.signals.index');
        Route::get('signals/latest', [SignalApiController::class, 'latest'])->name('api.signals.latest');
        Route::get('signals/closed', [SignalApiController::class, 'closed'])->name('api.signals.closed');
        Route::get('signals/{signal}', [SignalApiController::class, 'show'])->name('api.signals.show');

        // Notifications
        Route::get('notifications', [NotificationApiController::class, 'index'])->name('api.notifications.index');
        Route::get('notifications/unread', [NotificationApiController::class, 'unread'])->name('api.notifications.unread');
        Route::post('notifications/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('api.notifications.read');

        // Notification Settings (for mobile app to check)
        Route::get('notification-settings', [NotificationApiController::class, 'settings'])->name('api.notifications.settings');
        Route::get('notification-settings/{slug}', [NotificationApiController::class, 'checkSetting'])->name('api.notifications.check-setting');
        Route::post('notification-settings/{slug}/toggle', [NotificationApiController::class, 'toggleSetting'])->name('api.notifications.toggle-setting');
        Route::post('notification-settings/toggle-all', [NotificationApiController::class, 'toggleAllCategory'])->name('api.notifications.toggle-all');

        // Support Tickets
        Route::get('support/tickets', [SupportTicketApiController::class, 'index']);
        Route::post('support/tickets', [SupportTicketApiController::class, 'store'])->middleware('throttle:5,1');
        Route::get('support/tickets/{id}', [SupportTicketApiController::class, 'show']);
        Route::post('support/tickets/{id}/reply', [SupportTicketApiController::class, 'reply'])->middleware('throttle:30,1');
        Route::post('support/tickets/{id}/close', [SupportTicketApiController::class, 'close']);

        // Education
        Route::get('courses', [EducationApiController::class, 'courses'])->name('api.courses.index');
        Route::get('courses/{id}', [EducationApiController::class, 'course'])->name('api.courses.show');
        Route::get('education/categories', [EducationApiController::class, 'categories'])->name('api.education.categories');

        // MT5 Bots
        Route::get('bots', [BotApiController::class, 'index'])->name('api.bots.index');
        Route::get('bots/{id}', [BotApiController::class, 'show'])->name('api.bots.show');
        Route::get('bots/{id}/trades', [BotApiController::class, 'trades'])->name('api.bots.trades');
        Route::post('bots/{id}/toggle', [BotApiController::class, 'toggle'])->name('api.bots.toggle');

        // Payments
        Route::get('payments/history', [PaymentApiController::class, 'history'])->name('api.payments.history');
        Route::get('payments/subscription', [PaymentApiController::class, 'subscription'])->name('api.payments.subscription');

        // AI Chatbot
        Route::post('ai-chat', [AiChatbotApiController::class, 'chat'])->middleware('throttle:20,1')->name('api.ai-chat.send');
        Route::get('ai-chat/status', [AiChatbotApiController::class, 'status'])->name('api.ai-chat.status');
        Route::get('ai-chat/stats', [AiChatbotApiController::class, 'stats'])->name('api.ai-chat.stats');
        Route::post('ai-chat/report', [AiChatbotApiController::class, 'report'])->middleware('throttle:10,1')->name('api.ai-chat.report');

        // Market Data
        Route::get('market/ticker', [MarketDataApiController::class, 'getTicker'])->name('api.market.ticker');
        Route::get('market/overview', [MarketDataApiController::class, 'getMarketOverview'])->name('api.market.overview');
    });
});

// TEST ONLY - Local dev token bypass (NO login needed)
if (app()->environment('local')) {
    Route::get('v1/test-token', function () {
        $user = \App\Models\User::where('email', 'admin@kts10pipsbots.com')->first();
        if ($user) {
            $user->tokens()->delete();
            $token = $user->createToken('test-token')->plainTextToken;
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            ]);
        }
        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    });
}


