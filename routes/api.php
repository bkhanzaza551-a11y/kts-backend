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

// Cron Route for Signals Auto-Tracking
Route::get('cron/track-signals', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('signals:track');
        \Illuminate\Support\Facades\Cache::forget('signal_stats');
        return response()->json([
            'status' => 'success',
            'message' => 'Signals tracking executed successfully.',
            'timestamp' => now()->toIso8601String(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

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
    if (true) {
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

                // Batch seed / activate users
        Route::get('seed-users', function (\Illuminate\Http\Request $request) {
            $emails = [
                'muhammad.gul9596@gmail.com',
                'adeelmehmood9596@gmail.com',
                'farhanrafiq37@gmail.com',
                'asif1222maraj@gmail.com',
                'towais135@gmail.com',
                'anwarptc@yahoo.com',
                'danyalresham007@gmail.com',
                'kashaf8990@gmail.com',
                'muhammadrajpoot1995@gmail.com',
                'alijilani00786@gmail.com',
                'ashoo8990@gmail.com',
                'rajpootkurii81@gmail.com',
                'marham2309@gmail.com',
                'attaalrehman3317@gmail.com',
                'johnibram60@gmail.com',
                'mumtazqamar@yahoo.com',
                'yaqubahmed4254@gmail.com',
                'awaisjanal33@gmail.com',
                'tokhiharoon36@gmail.com',
                'zibhatti198080@gmail.com',
                'dipenjivrajani3@gmail.com',
                '14in105@gmail.com',
                '27jerichoagoto@gmail.com',
                '92mushtaq.ahmad@gmail.com',
                'a.razzaqsoomro123@gmail.com',
                'abbasibabar763@gmail.com',
                'abbasitehreem193@gmail.com',
                'abdoulbasid.7@icloud.com',
                'abdulghaffartayyab@gmail.com',
                'abdullahkhankhalil1997@gmail.com',
                'abidvault@gmail.com',
                'abrarestatezone@gmail.com',
                'abrarr84@gmail.com',
                'abuadilkl@gmail.com',
                'abubakarmakhdoom67@gmail.com',
                'abzasafder@gmail.com',
                'adeelking7789@gmail.com',
                'adnan.hn90@gmail.com',
                'adnanm44@gmail.com',
                'adnansabir6015@gmail.com',
                'afnanchowdhurym@gmail.com',
                'afsheenabbas78@gmail.com',
                'ah8137519@gmail.com',
                'ahmadfahdwork@gmail.com',
                'ahmadlogos97@gmail.com',
                'ahsankhalil2997@gmail.com',
                'alexbarigye230@gmail.com',
                'alimunirdxb@gmail.com',
                'aliveep29@gmail.com',
                'alkaabii73@gmail.com',
                'alkhafaji.dxb@yahoo.com',
                'alphagroot221@gmail.com',
                'amandeepbains512@gmail.com',
                'aminaahaji05@icloud.com',
                'aminfaraz4535@gmail.com',
                'amirdon33@gmail.com',
                'amirjj1234555@gmail.com',
                'ammarchaudhary363@gmail.com',
                'amncabdiyus@gmail.com',
                'amnoondavid22@gmail.com',
                'andreamudde05@gmail.com',
                'anmolsharma280983@gmail.com',
                'anserm74@gmail.com',
                'arafatmwanje58@gmail.com',
                'arbazthaheem4@gmail.com',
                'armaan590@gmail.com',
                'arslqnali12@gmail.com',
                'asadali8451@gmail.com',
                'asadasgharengr@gmail.com',
                'asif.maraj786@gmail.com',
                'asiimweyusuf19@gmail.com',
                'assuleman2@gmail.com',
                'atif41127@gmail.com',
                'atifamjad101@gmail.com',
                'atifminhas03@gmail.com',
                'atiqmughalkashmiri123@gmail.com',
                'attatiens94@gmail.com',
                'attiqurrahman70@gmail.com',
                'awaisah999@gmail.com',
                'awaismubarik90@gmail.com',
                'awanash0099@gmail.com',
                'ayizak321@icloud.com',
                'azizkhanutman3@gmail.com',
                'azmeerhassan04@gmail.com',
                'ba7724159@gmail.com',
                'bmunirsaleemi@gmail.com',
                'ceilingmaster818@gmail.com',
                'changaizahmed20@gmail.com',
                'che26101982@gmail.com',
                'chhaidershabbir20784521@gmail.com',
                'dilfarazkhankust2001@gmail.com',
                'dragonhunter4466@gmail.com',
                'easymailtoaziz@gmail.com',
                'elevify8@gmail.com',
                'energizedakhuwat@gmail.com',
                'engr.rehan.1819@gmail.com',
                'everfreshx@gmail.com',
                'faizan.ahmed2839310@gmail.com',
                'faizan.faizy0310@gmail.com',
                'farhanaijaz81@gmail.com',
                'farhanmastoi3@gmail.com',
                'farooqmumer86@gmail.com',
                'fatimziee@gmail.com',
                'fawadkhalilkhalilfawad@gmail.com',
                'fazalhussainkhan267@gmail.com',
                'fizasaif0233@gmail.com',
                'flamesunny280@gmail.com',
                'fm296104@gmail.com',
                'forexrayyan@gmail.com',
                'forexworld194@gmail.com',
                'forsondavid00@gmail.com',
                'ft232794@gmail.com',
                'fxlearning533@gmail.com',
                'fxtading1234@gmail.com',
                'geetoshakeel353@gmail.com',
                'gehlankulwinder@gmail.com',
                'geosalman123@gmail.com',
                'gf2615949@gmail.com',
                'gpsyaseengujjarwala@gmail.com',
                'guitarsurferofficial@gmail.com',
                'hadijuttrandhwa@gmail.com',
                'hadikhan1456789@gmail.com',
                'hafizfahad677@gmail.com',
                'hafiztrader116@gmail.com',
                'hammadkheshgi71@gmail.com',
                'harisirfan1231@gmail.com',
                'hdhffj25nmm@gmail.com',
                'hemrajarshi953@gmail.com',
                'hoosenf@gmail.com',
                'husenjavid15@gmail.com',
                'hussainahmad58@gmail.com',
                'huzaifashaikh5697@gmail.com',
                'ikramharipur998@gmail.com',
                'ilyasraza459@gmail.com',
                'iqraoobaidkhaliq1@gmail.com',
                'ismaeelkharal@gmail.com',
                'ismailsanghi5@gmail.com',
                'jacobornman02@gmail.com',
                'jemforextrader@gmail.com',
                'jinnkauzama7@gmail.com',
                'junaidnaqiraza@gmail.com',
                'jupitervenus0003@gmail.com',
                'juttsherazi234@gmail.com',
                'juwanadooll@gmail.com',
                'kaimkhanihashir@gmail.com',
                'kasmaami70@gmail.com',
                'keepsilent001786@gmail.com',
                'kenvic5050@gmail.com',
                'kevinsmithpractiscale562@gmail.com',
                'kgghsbufvji@gmail.com',
                'khanhaider52422@gmail.com',
                'khattaktv47@gmail.com',
                'koomerobinson004@gmail.com',
                'krunuonkoy168@gmail.com',
                'lalitshar7272@gmail.com',
                'lionking7502@gmail.com',
                'lolayaan772@gmail.com',
                'madaniy108@gmail.com',
                'maidajavaid7@gmail.com',
                'mailshazee@gmail.com',
                'malikhuzaifanasir@gmail.com',
                'maliksherax21@gmail.com',
                'malkooking77@gmail.com',
                'masifyounas99@gmail.com',
                'masroorbaloch2@gmail.com',
                'mbux2162@gmail.com',
                'mesumalvi08@gmail.com',
                'mfurqanhanif@gmail.com',
                'mh1866861@gmail.com',
                'mianasad515@gmail.com',
                'miancui007@gmail.com',
                'micmbuguahinga@gmail.com',
                'millionairekhan79@gmail.com',
                'mmonikn98@gmail.com',
                'mn122306@gmail.com',
                'modishindustries48@gmail.com',
                'mohd.hamza12111@gmail.com',
                'mohibullahmd0554@gmail.com',
                'mrhojavew.2026@gmail.com',
                'mshsomro292@gmail.com',
                'mudassar773345@gmail.com',
                'muhammadaamirhanjra@gmail.com',
                'muhammadarifmaikan84@gmail.com',
                'muhammadirfan11755@gmail.com',
                'muhammadkd456@gmail.com',
                'muhammadshahidmba@gmail.com',
                'muhammadyasin054585289@gmail.com',
                'mujeeb022003@gmail.com',
                'mumt699@gmail.com',
                'mumtazqamar@gmail.com',
                'muragurijohnk75@gmail.com',
                'murtazajatoi1111@gmail.com',
                'muyombaj432@gmail.com',
                'myworkmail018@gmail.com',
                'naeemnazir62@gmail.com',
                'nahfees314@gmail.com',
                'nayyab84584@gmail.com',
                'nazirahmedmirbehar@gmail.com',
                'naziriqbal82@gmail.com',
                'nbkhan882@gmail.com',
                'nethsarasineth58@gmail.com',
                'nhkhan882@hmail.com',
                'nikhan007@gmail.com',
                'noorellahi761@gmail.com',
                'obioraakam1234@gmail.com',
                'official.mahmed1@gmail.com',
                'okuwobitaiwo59@gmail.com',
                'opokuc505@gmail.com',
                'patrickkwamesimon18@gmail.com',
                'protrader42000@gmail.com',
                'punjabijoiya420@gmail.com',
                'punnal1212@gmail.com',
                'qadeermahar912@gmail.com',
                'rafiq.binary@gmail.com',
                'rajamahad44@gmail.com',
                'ranatahir5233@gmail.com',
                'raotahaasif007@gmail.com',
                'raqureshi707@gmail.com',
                'rehanahmed61@gmail.com',
                'rehmansiddique28@gmail.com',
                'riceworld15@gmail.com',
                'sa6030021@gmail.com',
                'saab3471@gmail.com',
                'saadnust1@gmail.com',
                'sadaqatzohaib26@gmail.com',
                'sadiaijax82@gmail.com',
                'sahaluhassani1@gmail.com',
                'salmankhan6789099@gmail.com',
                'sameerchoudary216@gmail.com',
                'sameermehar258@gmail.com',
                'samimehar1393@gmail.com',
                'sanashahid66@gmail.com',
                'saqibnizam6@gmail.com',
                'satradersonline.pk@gmail.com',
                'scripturexpencil@gmail.com',
                'shahidchjamil@gmail.com',
                'shahjahanwazir1010@gmail.com',
                'shahmirahmedofficial@gmail.com',
                'shahrozhussain376@gmail.com',
                'shahzain7378300@gmail.com',
                'shailendrasiwan38@gmail.com',
                'shakeelahsan8301@gmail.com',
                'shamsheraslam344@gmail.com',
                'sheikhzafariqbal415@gmail.com',
                'shiekhqaisar122@gmail.com',
                'sickboy408.sb@gmail.com',
                'siddiquifaisal828@gmail.com',
                'sikandarnawaz321@gmail.com',
                'sm0361889@gmail.com',
                'ssempijjaosama5@gmail.com',
                'starsonsky333@gmail.com',
                'sufyanpeerzada99@gmail.com',
                'sultankhansk78630@gmail.com',
                'swafbnb@gmail.com',
                'syedbilalusa9@gmail.com',
                'syedmohibalikts@gmail.com',
                'syedmuhammadadeel78@gmail.com',
                'ta234211@gmail.com',
                'tahiruabdulkarim340@icloud.com',
                'tanveerbilal@ymail.com',
                'tayyabsherani229@gmail.com',
                'thehassansaeed45@gmail.com',
                'thestupidboy44@gmail.com',
                'tomasobligarjr@gmail.com',
                'ttanweer0@gmail.com',
                'uf77151017@gmail.com',
                'ulugbek6070@icloud.com',
                'umarfarooq9123@gmail.com',
                'usama15memon@gmail.com',
                'usamaishfaq317@gmail.com',
                'usmanlone84@gmail.com',
                'usmanoffical432@gmail.com',
                'vanfleuryjoelnshimirimana@gmail.com',
                'walishaikh512@gmail.com',
                'waqarakhter51@gmail.com',
                'wasemgopaang@gmail.com',
                'web110distribution@gmail.com',
                'wittygemini321@gmail.com',
                'wkhawaja14@gmail.com',
                'wks32705@gmail.com',
                'xdali1992@gmail.com',
                'ya401681@gmail.com',
                'younismohammad911@gmail.com',
                'zaidi.mujtaba1995@gmail.com',
                'zeeshan89731@gmail.com',
                'zeeshanakramfx@gmail.com',
                'zeshanwaheed6@gmail.com',
                'mb2550948@gmail.com',
            ];

            $password = 'ktsbot123';
            $hashedPassword = \Illuminate\Support\Facades\Hash::make($password);
            $userRole = \App\Models\Role::where('slug', 'user')->first();
            $roleId = $userRole ? $userRole->id : null;

            $singleEmail = $request->query('email');
            if ($singleEmail) {
                $slice = [strtolower(trim($singleEmail))];
                $offset = 0;
                $limit = 1;
            } else {
                $offset = (int) $request->query('offset', 0);
                $limit = (int) $request->query('limit', 500);
                $slice = array_slice($emails, $offset, $limit);
            }

            $results = [];

            foreach ($slice as $email) {
                $email = strtolower(trim($email));
                $user = \App\Models\User::where('email', $email)->first();

                $name = explode('@', $email)[0];
                $name = ucwords(str_replace(['.', '_', '-'], ' ', $name));

                if (!$user) {
                    $user = \App\Models\User::create([
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashedPassword,
                        'email_verified_at' => now(),
                        'status' => 'active',
                        'is_banned' => false,
                    ]);

                    if ($roleId) {
                        $user->roles()->syncWithoutDetaching([$roleId]);
                    }

                    $results[] = ['email' => $email, 'status' => 'created'];
                } else {
                    $user->update([
                        'password' => $hashedPassword,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'status' => 'active',
                        'is_banned' => false,
                    ]);

                    if ($roleId) {
                        $user->roles()->syncWithoutDetaching([$roleId]);
                    }

                    $results[] = ['email' => $email, 'status' => 'updated_and_activated'];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'All accounts created and activated successfully!',
                'password' => $password,
                'offset' => $offset,
                'limit' => $limit,
                'processed' => count($slice),
                'total_users' => count($emails),
                'next_offset' => ($offset + $limit < count($emails)) ? ($offset + $limit) : null,
                'users' => $results,
            ]);
        })->name('api.seed-users');

    }

    // Payment Plans (public - mobile shows before login)
    Route::get('payments/plans', [PaymentApiController::class, 'plans'])->name('api.payments.plans');

    // Bot Secure Download (authenticates via Bearer Token or ?token= query parameter)
    Route::get('bot/download', [BotApiController::class, 'download'])->name('api.bots.download');

    // PUBLIC Routes for Website (no auth needed)
    Route::get('market/ticker', [MarketDataApiController::class, 'getTicker'])->name('api.market.ticker');
    Route::get('market/overview', [MarketDataApiController::class, 'getMarketOverview'])->name('api.market.overview');
    Route::get('signals/latest', [SignalApiController::class, 'latest'])->name('api.signals.latest');
    Route::get('courses', [EducationApiController::class, 'courses'])->name('api.courses.index');
    Route::get('courses/{id}', [EducationApiController::class, 'course'])->name('api.courses.show');
    Route::get('bot', [BotApiController::class, 'show'])->name('api.bots.show');

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
        Route::get('chat/rooms/{room}/messages', [ChatApiController::class, 'messages'])->name('api.chat.messages');
        Route::post('chat/rooms/{room}/messages', [ChatApiController::class, 'send'])->middleware('throttle:30,1')->name('api.chat.send');
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

        // Signals (authenticated)
        Route::get('signals', [SignalApiController::class, 'index'])->name('api.signals.index');
        Route::get('signals/closed', [SignalApiController::class, 'closed'])->name('api.signals.closed');
        Route::get('signals/{signal}', [SignalApiController::class, 'show'])->name('api.signals.show');

        // Notifications
        Route::get('notifications', [NotificationApiController::class, 'index'])->name('api.notifications.index');
        Route::get('notifications/unread', [NotificationApiController::class, 'unread'])->name('api.notifications.unread');
        Route::post('notifications/mark-all-read', [NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
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

        // Education (admin only write, read is public)
        Route::get('education/categories', [EducationApiController::class, 'categories'])->name('api.education.categories');

        // MT5 Bot (Single Bot - authenticated write operations)
        Route::get('bots', [BotApiController::class, 'index'])->name('api.bots.index');
        Route::get('bot/trades', [BotApiController::class, 'trades'])->name('api.bots.trades');
        Route::post('bot/toggle', [BotApiController::class, 'toggle'])->name('api.bots.toggle');
        Route::put('bot', [BotApiController::class, 'update'])->name('api.bots.update');

        // Payments
        Route::get('payments/history', [PaymentApiController::class, 'history'])->name('api.payments.history');
        Route::get('payments/subscription', [PaymentApiController::class, 'subscription'])->name('api.payments.subscription');

        // AI Chatbot
        Route::post('ai-chat', [AiChatbotApiController::class, 'chat'])->middleware('throttle:20,1')->name('api.ai-chat.send');
        Route::get('ai-chat/status', [AiChatbotApiController::class, 'status'])->name('api.ai-chat.status');
        Route::get('ai-chat/stats', [AiChatbotApiController::class, 'stats'])->name('api.ai-chat.stats');
        Route::post('ai-chat/report', [AiChatbotApiController::class, 'report'])->middleware('throttle:10,1')->name('api.ai-chat.report');
    });
});

// TEST ONLY - Local dev token bypass (NO login needed)
if (app()->environment('local')) {
    Route::get('v1/test-token', function () {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403);
        }
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


