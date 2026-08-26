<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->handle($input = new Symfony\Component\Console\Input\ArgvInput, new Symfony\Component\Console\Output\ConsoleOutput);

$user = \App\Models\User::where('email', 'huntergaming5555566@gmail.com')->first();

if ($user) {
    $existing = \App\Models\AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->first();
    if (!$existing) {
        $result = \App\Models\AdminSecurityCode::generateFor($user, 'Initial Security Code');
        echo "Security code created: " . $result['code'] . "\n";
        echo "Save this code - it will only be shown once!\n";
    } else {
        echo "Security code already exists\n";
    }
} else {
    echo "User not found\n";
}
