<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$user = User::where('email', 'admin@kts10pipsbots.com')->first();
if (!$user) { echo "NO_USER"; exit; }

// Generate a known security code
$plainCode = 'TESTCODE';
$securityCode = AdminSecurityCode::create([
    'user_id' => $user->id,
    'code' => Hash::make($plainCode),
    'label' => 'Auto Test',
    'is_active' => true,
]);
echo "SECURITY_CODE=" . $plainCode . "\n";
echo "SECURITY_CODE_ID=" . $securityCode->id . "\n";

// Generate OTP
$otpRecord = AdminOtp::generateFor($user, '127.0.0.1');
echo "OTP=" . $otpRecord->otp . "\n";
