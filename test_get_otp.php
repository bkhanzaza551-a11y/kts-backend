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

// Clean up - mark old unused OTPs
AdminOtp::where('user_id', $user->id)->where('is_used', false)->update(['is_used' => true]);

// Generate fresh OTP  
$otpRecord = AdminOtp::generateFor($user, '127.0.0.1');

// Ensure we have a known security code
$secCode = AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->latest()->first();
if (!$secCode) {
    $result = AdminSecurityCode::generateFor($user, 'AutoTest');
    echo "NEW_SECURITY_CODE=" . $result['code'] . "\n";
}

echo "OTP=" . $otpRecord->otp . "\n";
