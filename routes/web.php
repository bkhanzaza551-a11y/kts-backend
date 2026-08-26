<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SignalController;
use App\Http\Controllers\Admin\SignalCategoryController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\EducationCategoryController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\Mt5BotController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\StickerController;
use App\Http\Controllers\Admin\MarketDataController;
use App\Http\Controllers\Admin\AiChatbotController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationSettingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\SignalAnalyticsController;
use App\Http\Controllers\Admin\Mt5AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('otp/verify', [AuthController::class, 'showOtpForm'])->name('otp.verify');
    Route::post('otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1')->name('otp.verify.post');
    Route::post('otp/resend', [AuthController::class, 'resendOtp'])->middleware('throttle:3,1')->name('otp.resend');
    Route::get('security-code/verify', [AuthController::class, 'showSecurityCodeForm'])->name('security-code.verify');
    Route::post('security-code/verify', [AuthController::class, 'verifySecurityCode'])->middleware('throttle:5,1')->name('security-code.verify.post');
    Route::post('logout', [AuthController::class, 'logout'])->middleware(['auth', 'prevent.deleted'])->name('logout');

    Route::middleware(['auth', 'admin', 'prevent.deleted'])->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard/stats', [DashboardController::class, 'stats'])
            ->middleware('throttle:30,1')
            ->name('dashboard.stats');
        Route::get('dashboard/user-growth', [DashboardController::class, 'userGrowth'])
            ->middleware('throttle:30,1')
            ->name('dashboard.user-growth');
        Route::get('dashboard/revenue-chart', [DashboardController::class, 'revenueChart'])
            ->middleware('throttle:30,1')
            ->name('dashboard.revenue-chart');
        Route::get('dashboard/gateway-breakdown', [DashboardController::class, 'gatewayBreakdown'])
            ->middleware('throttle:30,1')
            ->name('dashboard.gateway-breakdown');

        Route::get('users', [UserController::class, 'index'])
            ->middleware('permission:users_view')
            ->name('users.index');

        Route::get('users/create', [UserController::class, 'create'])
            ->middleware('permission:users_create')
            ->name('users.create');

        Route::post('users', [UserController::class, 'store'])
            ->middleware('permission:users_create')
            ->name('users.store');

        Route::get('users/{user}', [UserController::class, 'show'])
            ->middleware('permission:users_view')
            ->name('users.show');

        Route::get('users/{user}/edit', [UserController::class, 'edit'])
            ->middleware('permission:users_edit')
            ->name('users.edit');

        Route::put('users/{user}', [UserController::class, 'update'])
            ->middleware('permission:users_edit')
            ->name('users.update');

        Route::delete('users/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users_delete')
            ->name('users.destroy');

        Route::post('users/{user}/toggle-ban', [UserController::class, 'toggleBan'])
            ->middleware('permission:users_ban')
            ->name('users.toggle-ban');

        Route::post('users/{user}/toggle-premium', [UserController::class, 'togglePremium'])
            ->middleware('permission:users_edit')
            ->name('users.toggle-premium');

        Route::post('users/bulk-action', [UserController::class, 'bulkAction'])
            ->middleware('permission:users_delete')
            ->name('users.bulk-action');

        Route::middleware('permission:staff_view')->group(function () {
            Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
            Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
            Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        });
        Route::middleware('permission:staff_create')->group(function () {
            Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
        });
        Route::middleware('permission:staff_edit')->group(function () {
            Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
        });
        Route::middleware('permission:staff_delete')->group(function () {
            Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
        });

        Route::middleware('permission:roles_view')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        });
        Route::middleware('permission:roles_create')->group(function () {
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });
        Route::middleware('permission:roles_edit')->group(function () {
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
        Route::middleware('permission:roles_delete')->group(function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });

        Route::middleware('permission:permissions_view')->group(function () {
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        });

        // Signal Categories (define before signals to avoid route conflicts)
        Route::middleware('permission:signal_categories_view')->group(function () {
            Route::get('signal-categories', [SignalCategoryController::class, 'index'])->name('signal-categories.index');
            Route::get('signal-categories/create', [SignalCategoryController::class, 'create'])->name('signal-categories.create');
            Route::get('signal-categories/{signalCategory}', [SignalCategoryController::class, 'show'])->name('signal-categories.show');
            Route::get('signal-categories/{signalCategory}/edit', [SignalCategoryController::class, 'edit'])->name('signal-categories.edit');
        });

        Route::middleware('permission:signal_categories_create')->group(function () {
            Route::post('signal-categories', [SignalCategoryController::class, 'store'])->name('signal-categories.store');
        });

        Route::middleware('permission:signal_categories_edit')->group(function () {
            Route::put('signal-categories/{signalCategory}', [SignalCategoryController::class, 'update'])->name('signal-categories.update');
        });

        Route::middleware('permission:signal_categories_delete')->group(function () {
            Route::delete('signal-categories/{signalCategory}', [SignalCategoryController::class, 'destroy'])->name('signal-categories.destroy');
        });

        // Signals
        Route::middleware('permission:signals_view')->group(function () {
            Route::get('signals', [SignalController::class, 'index'])->name('signals.index');
            Route::get('signals/symbols', [SignalController::class, 'symbols'])->name('signals.symbols');
            Route::get('signals/create', [SignalController::class, 'create'])->name('signals.create');
            Route::get('signals/{signal}', [SignalController::class, 'show'])->name('signals.show');
            Route::get('signals/{signal}/edit', [SignalController::class, 'edit'])->name('signals.edit');
        });

        Route::middleware('permission:signals_create')->group(function () {
            Route::post('signals', [SignalController::class, 'store'])->name('signals.store');
        });

        Route::middleware('permission:signals_edit')->group(function () {
            Route::put('signals/{signal}', [SignalController::class, 'update'])->name('signals.update');
            Route::post('signals/{signal}/publish', [SignalController::class, 'publish'])->name('signals.publish');
            Route::post('signals/{signal}/close', [SignalController::class, 'close'])->name('signals.close');
        });

        Route::middleware('permission:signals_delete')->group(function () {
            Route::delete('signals/{signal}', [SignalController::class, 'destroy'])->name('signals.destroy');
        });

        // Education Categories
        Route::middleware('permission:education_categories_create')->group(function () {
            Route::get('education-categories/create', [EducationCategoryController::class, 'create'])->name('education-categories.create');
            Route::post('education-categories', [EducationCategoryController::class, 'store'])->name('education-categories.store');
        });

        Route::middleware('permission:education_categories_view')->group(function () {
            Route::get('education-categories', [EducationCategoryController::class, 'index'])->name('education-categories.index');
            Route::get('education-categories/{educationCategory}', [EducationCategoryController::class, 'show'])->name('education-categories.show');
        });

        Route::middleware('permission:education_categories_edit')->group(function () {
            Route::get('education-categories/{educationCategory}/edit', [EducationCategoryController::class, 'edit'])->name('education-categories.edit');
            Route::put('education-categories/{educationCategory}', [EducationCategoryController::class, 'update'])->name('education-categories.update');
        });

        Route::middleware('permission:education_categories_delete')->group(function () {
            Route::delete('education-categories/{educationCategory}', [EducationCategoryController::class, 'destroy'])->name('education-categories.destroy');
            Route::post('education-categories/{educationCategory}/restore', [EducationCategoryController::class, 'restore'])->name('education-categories.restore')->withTrashed();
        });

        // Courses
        Route::middleware('permission:education_create')->group(function () {
            Route::get('courses/create', [EducationController::class, 'create'])->name('courses.create');
            Route::post('courses', [EducationController::class, 'store'])->name('courses.store');
        });

        Route::middleware('permission:education_view')->group(function () {
            Route::get('courses', [EducationController::class, 'index'])->name('courses.index');
            Route::get('courses/{course}', [EducationController::class, 'show'])->name('courses.show');
        });

        Route::middleware('permission:education_edit')->group(function () {
            Route::get('courses/{course}/edit', [EducationController::class, 'edit'])->name('courses.edit');
            Route::put('courses/{course}', [EducationController::class, 'update'])->name('courses.update');
            Route::post('courses/{course}/publish', [EducationController::class, 'publish'])->name('courses.publish');
            Route::post('courses/{course}/unpublish', [EducationController::class, 'unpublish'])->name('courses.unpublish');
        });

        Route::middleware('permission:education_delete')->group(function () {
            Route::delete('courses/{course}', [EducationController::class, 'destroy'])->name('courses.destroy');
            Route::post('courses/{course}/restore', [EducationController::class, 'restore'])->name('courses.restore')->withTrashed();
        });

        // Lessons (nested under courses)
        Route::middleware('permission:lessons_view')->group(function () {
            Route::get('courses/{course}/lessons', [LessonController::class, 'index'])->name('courses.lessons.index');
            Route::get('courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->name('courses.lessons.show');
        });

        Route::middleware('permission:lessons_create')->group(function () {
            Route::get('courses/{course}/lessons/create', [LessonController::class, 'create'])->name('courses.lessons.create');
            Route::post('courses/{course}/lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
        });

        Route::middleware('permission:lessons_edit')->group(function () {
            Route::get('courses/{course}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.lessons.edit');
            Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
            Route::post('courses/{course}/lessons/reorder', [LessonController::class, 'reorder'])->name('courses.lessons.reorder');
        });

        Route::middleware('permission:lessons_delete')->group(function () {
            Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');
            Route::post('courses/{course}/lessons/{lesson}/restore', [LessonController::class, 'restore'])->name('courses.lessons.restore')->withTrashed();
        });

        // MT5 Bot Management
        Route::middleware('permission:mt5_bot_view')->group(function () {
            Route::get('mt5-bot', [Mt5BotController::class, 'index'])->name('mt5-bot.index');
            Route::get('mt5-bot/create', [Mt5BotController::class, 'create'])->name('mt5-bot.create');
            Route::get('mt5-bot/{bot}', [Mt5BotController::class, 'show'])->name('mt5-bot.show');
            Route::get('mt5-bot/{bot}/edit', [Mt5BotController::class, 'edit'])->name('mt5-bot.edit');
            Route::get('mt5-bot/{bot}/logs', [Mt5BotController::class, 'logs'])->name('mt5-bot.logs');
            Route::get('mt5-bot/{bot}/trades', [Mt5BotController::class, 'trades'])->name('mt5-bot.trades');
        });

        Route::middleware('permission:mt5_bot_manage')->group(function () {
            Route::post('mt5-bot', [Mt5BotController::class, 'store'])->name('mt5-bot.store');
            Route::put('mt5-bot/{bot}', [Mt5BotController::class, 'update'])->name('mt5-bot.update');
            Route::delete('mt5-bot/{bot}', [Mt5BotController::class, 'destroy'])->name('mt5-bot.destroy');
            Route::post('mt5-bot/{bot}/restore', [Mt5BotController::class, 'restore'])->name('mt5-bot.restore')->withTrashed();
            Route::patch('mt5-bot/{bot}/toggle-status', [Mt5BotController::class, 'toggleStatus'])->middleware('throttle:10,1')->name('mt5-bot.toggle-status');
            Route::patch('mt5-bot/{bot}/toggle-auto-trade', [Mt5BotController::class, 'toggleAutoTrade'])->middleware('throttle:10,1')->name('mt5-bot.toggle-auto-trade');
            Route::post('mt5-bot/{bot}/recalculate-stats', [Mt5BotController::class, 'recalculateStats'])->name('mt5-bot.recalculate-stats');
        });

        // Chat Moderation
        Route::middleware('permission:chat_view')->group(function () {
            Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
            Route::get('chat/rooms', [ChatController::class, 'rooms'])->name('chat.rooms');
            Route::get('chat/banned-users', [ChatController::class, 'bannedUsers'])->name('chat.banned-users');
            Route::get('chat/restricted-words', [ChatController::class, 'restrictedWords'])->name('chat.restricted-words');
        });

        Route::middleware('permission:chat_moderate')->group(function () {
            Route::patch('chat/messages/{message}/toggle-flag', [ChatController::class, 'toggleFlag'])->name('chat.toggle-flag');
            Route::patch('chat/messages/{message}/pin', [ChatController::class, 'pinMessage'])->name('chat.pin-message');
            Route::patch('chat/messages/{message}/unpin', [ChatController::class, 'unpinMessage'])->name('chat.unpin-message');
            Route::post('chat/ban-user', [ChatController::class, 'banUser'])->name('chat.ban-user');
            Route::delete('chat/banned-users/{ban}/unban', [ChatController::class, 'unbanUser'])->name('chat.unban-user');
            Route::post('chat/restricted-words', [ChatController::class, 'storeRestrictedWord'])->name('chat.store-restricted-word');
            Route::patch('chat/restricted-words/{word}/toggle', [ChatController::class, 'toggleRestrictedWord'])->name('chat.toggle-restricted-word');
            Route::delete('chat/restricted-words/{word}', [ChatController::class, 'destroyRestrictedWord'])->name('chat.destroy-restricted-word');
            Route::post('chat/rooms', [ChatController::class, 'storeRoom'])->name('chat.store-room');
            Route::patch('chat/rooms/{room}/toggle', [ChatController::class, 'toggleRoom'])->name('chat.toggle-room');
            Route::patch('chat/rooms/{room}/toggle-pause', [ChatController::class, 'togglePauseRoom'])->name('chat.toggle-pause-room');
            Route::delete('chat/rooms/{room}', [ChatController::class, 'destroyRoom'])->name('chat.destroy-room');
            Route::get('chat/badges', [ChatController::class, 'badges'])->name('chat.badges');
            Route::post('chat/badges', [ChatController::class, 'updateBadge'])->name('chat.update-badge');
        });

        Route::middleware('permission:chat_delete_message')->group(function () {
            Route::delete('chat/messages/{message}', [ChatController::class, 'destroyMessage'])->name('chat.destroy-message');
            Route::patch('chat/messages/{message}/restore', [ChatController::class, 'restoreMessage'])->name('chat.restore-message');
        });

        // Sticker Management
        Route::middleware('permission:chat_view')->group(function () {
            Route::get('chat/stickers', [StickerController::class, 'index'])->name('chat.stickers.index');
            Route::get('chat/stickers/{pack}/view', [StickerController::class, 'showPack'])->name('chat.stickers.show-pack');
        });

        Route::middleware('permission:chat_moderate')->group(function () {
            Route::get('chat/stickers/create', [StickerController::class, 'createPack'])->name('chat.stickers.create-pack');
            Route::post('chat/stickers', [StickerController::class, 'storePack'])->name('chat.stickers.store-pack');
            Route::get('chat/stickers/{pack}/edit', [StickerController::class, 'editPack'])->name('chat.stickers.edit-pack');
            Route::put('chat/stickers/{pack}', [StickerController::class, 'updatePack'])->name('chat.stickers.update-pack');
            Route::patch('chat/stickers/{pack}/toggle', [StickerController::class, 'togglePack'])->name('chat.stickers.toggle-pack');
            Route::delete('chat/stickers/{pack}', [StickerController::class, 'destroyPack'])->name('chat.stickers.destroy-pack');
            Route::post('chat/stickers/upload', [StickerController::class, 'uploadSticker'])->name('chat.stickers.upload');
            Route::patch('chat/stickers/sticker/{sticker}/toggle', [StickerController::class, 'toggleSticker'])->name('chat.stickers.toggle-sticker');
            Route::delete('chat/stickers/sticker/{sticker}', [StickerController::class, 'destroySticker'])->name('chat.stickers.destroy-sticker');
            Route::post('chat/stickers/bulk-delete', [StickerController::class, 'destroySelected'])->name('chat.stickers.bulk-delete');
        });

        // AI Chatbot
        Route::middleware('permission:ai_chatbot_view')->group(function () {
            Route::get('ai-chatbot/settings', [AiChatbotController::class, 'settings'])->name('ai-chatbot.settings');
            Route::get('ai-chatbot/chat-logs', [AiChatbotController::class, 'chatLogs'])->name('ai-chatbot.chat-logs');
        });

        Route::middleware('permission:ai_chatbot_manage')->group(function () {
            Route::put('ai-chatbot/settings', [AiChatbotController::class, 'updateSettings'])->name('ai-chatbot.update-settings');
            Route::patch('ai-chatbot/chat-logs/{log}/toggle-flag', [AiChatbotController::class, 'toggleFlag'])->name('ai-chatbot.toggle-flag');
        });

        // Notifications
        Route::middleware('permission:notifications_view')->group(function () {
            Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
            Route::get('notifications/templates', [NotificationController::class, 'templates'])->name('notifications.templates');
            Route::get('notifications/tips', [NotificationController::class, 'tips'])->name('notifications.tips');
        });

        Route::middleware('permission:notifications_send')->group(function () {
            Route::post('notifications', [NotificationController::class, 'store'])->middleware('throttle:5,1')->name('notifications.store');
            Route::post('notifications/templates', [NotificationController::class, 'storeTemplate'])->name('notifications.store-template');
            Route::delete('notifications/templates/{template}', [NotificationController::class, 'destroyTemplate'])->name('notifications.destroy-template');
            Route::post('notifications/tips', [NotificationController::class, 'storeTip'])->name('notifications.store-tip');
            Route::post('notifications/tips/generate', [NotificationController::class, 'generateTip'])->name('notifications.generate-tip');
            Route::delete('notifications/tips/{tip}', [NotificationController::class, 'destroyTip'])->name('notifications.destroy-tip');
        });

        // Notification Settings (Controller)
        Route::middleware('permission:notifications_view')->group(function () {
            Route::get('notification-settings', [NotificationSettingController::class, 'index'])->name('notification-settings.index');
            Route::get('notification-settings/stats', [NotificationSettingController::class, 'stats'])->name('notification-settings.stats');
        });
        Route::middleware('permission:notifications_send')->group(function () {
            Route::post('notification-settings/{slug}/toggle', [NotificationSettingController::class, 'toggle'])->name('notification-settings.toggle');
            Route::post('notification-settings/toggle-all', [NotificationSettingController::class, 'toggleAll'])->name('notification-settings.toggle-all');
        });

        // Payments
        Route::middleware('permission:transactions_view')->group(function () {
            Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
            Route::get('payments/{transaction}', [PaymentController::class, 'show'])->name('payments.show');
        });

        Route::middleware('permission:transactions_manage')->group(function () {
            Route::patch('payments/{transaction}/approve', [PaymentController::class, 'approve'])->middleware('throttle:10,1')->name('payments.approve');
            Route::patch('payments/{transaction}/reject', [PaymentController::class, 'reject'])->middleware('throttle:10,1')->name('payments.reject');
        });

        // System Settings
        Route::middleware('permission:settings_view')->group(function () {
            Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
            Route::get('settings/backups', [SettingsController::class, 'backups'])->name('settings.backups');
        });

        Route::middleware('permission:settings_manage')->group(function () {
            Route::put('settings', [SettingsController::class, 'updateSettings'])->name('settings.update');
            Route::patch('settings/maintenance', [SettingsController::class, 'toggleMaintenance'])->middleware('throttle:3,1')->name('settings.toggle-maintenance');
            Route::post('settings/backup', [SettingsController::class, 'createBackup'])->name('settings.create-backup');
            Route::get('settings/backup/{filename}/download', [SettingsController::class, 'downloadBackup'])->name('settings.download-backup');
            Route::delete('settings/backup/{filename}', [SettingsController::class, 'deleteBackup'])->name('settings.delete-backup');
        });

        Route::middleware('auth')->group(function () {
            Route::get('settings/security-code', [\App\Http\Controllers\Admin\SecurityController::class, 'showChangeForm'])->name('security.change-form');
            Route::post('settings/security-code/send-otp', [\App\Http\Controllers\Admin\SecurityController::class, 'sendOtp'])->middleware('throttle:3,1')->name('security.send-otp');
            Route::get('settings/security-code/verify-otp', [\App\Http\Controllers\Admin\SecurityController::class, 'showOtpForm'])->name('security.verify-otp');
            Route::post('settings/security-code/verify-otp', [\App\Http\Controllers\Admin\SecurityController::class, 'verifyOtp'])->middleware('throttle:5,1')->name('security.verify-otp.post');
            Route::get('settings/security-code/new', [\App\Http\Controllers\Admin\SecurityController::class, 'showNewCodeForm'])->name('security.new-code-form');
            Route::post('settings/security-code/update', [\App\Http\Controllers\Admin\SecurityController::class, 'updateCode'])->middleware('throttle:3,1')->name('security.update-code');
        });

        // Audit Logs
        Route::middleware('permission:settings_manage')->group(function () {
            Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });

        // Analytics
        Route::middleware('permission:signals_view')->group(function () {
            Route::get('analytics/signals', [SignalAnalyticsController::class, 'index'])->name('analytics.signals');
        });
        Route::middleware('permission:mt5_bot_view')->group(function () {
            Route::get('analytics/mt5', [Mt5AnalyticsController::class, 'index'])->name('analytics.mt5');
        });

        // Currency Switcher
        Route::post('currency/switch', [\App\Http\Controllers\Admin\CurrencyController::class, 'switch'])->name('currency.switch');
        Route::get('currency/rates', [\App\Http\Controllers\Admin\CurrencyController::class, 'getRates'])->name('currency.rates');

        // Market Data (Binance API)
        Route::middleware('permission:signals_view')->group(function () {
            Route::get('market/search', [MarketDataController::class, 'searchSymbols'])->name('market.search');
            Route::get('market/ticker', [MarketDataController::class, 'getTicker'])->name('market.ticker');
            Route::get('market/overview', [MarketDataController::class, 'getMarketOverview'])->name('market.overview');
            Route::get('market/klines', [MarketDataController::class, 'getKlines'])->name('market.klines');
        });

        // Demo Account Requests
        Route::middleware('permission:demo_accounts_view')->group(function () {
            Route::get('demo-accounts', [\App\Http\Controllers\Admin\DemoAccountController::class, 'index'])->name('demo-accounts.index');
            Route::get('demo-accounts/{demoRequest}', [\App\Http\Controllers\Admin\DemoAccountController::class, 'show'])->name('demo-accounts.show');
        });
        Route::middleware('permission:demo_accounts_manage')->group(function () {
            Route::post('demo-accounts/{demoRequest}/approve', [\App\Http\Controllers\Admin\DemoAccountController::class, 'approve'])->name('demo-accounts.approve');
            Route::post('demo-accounts/{demoRequest}/reject', [\App\Http\Controllers\Admin\DemoAccountController::class, 'reject'])->name('demo-accounts.reject');
            Route::post('demo-accounts/{demoRequest}/link', [\App\Http\Controllers\Admin\DemoAccountController::class, 'link'])->name('demo-accounts.link');
            Route::delete('demo-accounts/{demoRequest}', [\App\Http\Controllers\Admin\DemoAccountController::class, 'destroy'])->name('demo-accounts.destroy');
        });

        // Demo Account Settings
        Route::middleware('permission:demo_accounts_manage')->group(function () {
            Route::get('demo-settings', [\App\Http\Controllers\Admin\DemoAccountSettingsController::class, 'index'])->name('demo-settings.index');
            Route::put('demo-settings', [\App\Http\Controllers\Admin\DemoAccountSettingsController::class, 'update'])->name('demo-settings.update');
        });

        // Legal Pages (Privacy Policy, Terms & Conditions)
        Route::middleware('permission:settings_manage')->group(function () {
            Route::get('legal-pages', [\App\Http\Controllers\Admin\LegalPageController::class, 'index'])->name('legal-pages.index');
            Route::get('legal-pages/create', [\App\Http\Controllers\Admin\LegalPageController::class, 'create'])->name('legal-pages.create');
            Route::post('legal-pages', [\App\Http\Controllers\Admin\LegalPageController::class, 'store'])->name('legal-pages.store');
            Route::get('legal-pages/{slug}/edit', [\App\Http\Controllers\Admin\LegalPageController::class, 'edit'])->name('legal-pages.edit');
            Route::put('legal-pages/{slug}', [\App\Http\Controllers\Admin\LegalPageController::class, 'update'])->name('legal-pages.update');
            Route::post('legal-pages/{slug}/publish', [\App\Http\Controllers\Admin\LegalPageController::class, 'publish'])->name('legal-pages.publish');
            Route::delete('legal-pages/{slug}', [\App\Http\Controllers\Admin\LegalPageController::class, 'destroy'])->name('legal-pages.destroy');
        });
    });
});

// TEST ONLY - Local dev bypass (NO OTP, NO emails)
if (app()->environment('local')) {
    Route::get('/test-login', function () {
        $user = \App\Models\User::where('email', 'admin@kts10pipsbots.com')->first();
        if ($user) {
            Auth::login($user);
            return redirect('/admin/dashboard')->with('success', 'Test login bypassed (local only)');
        }
        return 'User not found';
    });
}
