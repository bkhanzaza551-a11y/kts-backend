<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('email', 'admin@kts10pipsbots.com')->first();
if (!$user) { echo "NO_USER"; exit; }

// Reset any old OTPs
AdminOtp::where('user_id', $user->id)->where('is_used', false)->update(['is_used' => true]);

// Generate fresh OTP
$otpRecord = AdminOtp::generateFor($user, '127.0.0.1');
echo "OTP=" . $otpRecord->otp . "\n";

// Generate a known security code
$plainCode = 'TSTCODE2';
$securityCode = AdminSecurityCode::create([
    'user_id' => $user->id,
    'code' => Hash::make($plainCode),
    'label' => 'AutoTest',
    'is_active' => true,
]);
echo "SECURITY_CODE=" . $plainCode . "\n";
echo "SECURITY_CODE_ID=" . $securityCode->id . "\n";
