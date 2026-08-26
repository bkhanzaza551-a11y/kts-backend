<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->handle($input = new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);

echo "Mail Mailer: " . config('mail.mailer') . "\n";
echo "Mail Host: " . config('mail.host') . "\n";
echo "Mail Port: " . config('mail.port') . "\n";
echo "Mail Username: " . config('mail.username') . "\n";
echo "Mail Encryption: " . config('mail.encryption') . "\n";
echo "Mail From: " . config('mail.from.address') . "\n";

try {
    \Illuminate\Support\Facades\Mail::raw('Test email from KTS Markets', function ($message) {
        $message->to('huntergaming5555566@gmail.com')
                ->subject('Test Email - KTS Markets');
    });
    echo "Email sent successfully!\n";
} catch (\Exception $e) {
    echo "Email failed: " . $e->getMessage() . "\n";
}
