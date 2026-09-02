<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default Mailer: " . config('mail.default') . "\n";
echo "SMTP Host: " . config('mail.mailers.smtp.host') . "\n";
echo "SMTP Port: " . config('mail.mailers.smtp.port') . "\n";
echo "SMTP Username: " . config('mail.mailers.smtp.username') . "\n";
echo "SMTP Encryption: " . config('mail.mailers.smtp.encryption') . "\n";
echo "From Address: " . config('mail.from.address') . "\n";
echo "From Name: " . config('mail.from.name') . "\n";

echo "\nAttempting to send email to ahmedbilalkhangl09@gmail.com...\n";

try {
    \Illuminate\Support\Facades\Mail::raw("Hello Ahmed Bilal Khan,\n\nThis is a live test email sent successfully from your cPanel Business Email:\nSender: Kts10Pips@forextutoracademy.com\nPlatform: KTS Markets (10 Pips Bots)\nTime: " . date('Y-m-d H:i:s') . "\n\nAll SMTP settings are verified and operational!", function ($message) {
        $message->to('ahmedbilalkhangl09@gmail.com')
                ->subject('Test Email - KTS Markets cPanel SMTP Verified');
    });
    echo "\n>>> SUCCESS: Email sent successfully to ahmedbilalkhangl09@gmail.com! <<<\n";
} catch (\Exception $e) {
    echo "\n>>> ERROR: Email failed: " . $e->getMessage() . "\n";
}
