<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::where('email', 'admin@kts10pipsbots.com')->first();

// Deactivate ALL security codes so the system generates a fresh one
AdminSecurityCode::where('user_id', $user->id)->update(['is_active' => false]);

// Mark old OTPs as used
AdminOtp::where('user_id', $user->id)->where('is_used', false)->update(['is_used' => true]);

echo "STATE RESET DONE\n";

// Now do the full HTTP flow via Guzzle
$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://127.0.0.1:8002',
    'cookies' => true,
    'allow_redirects' => false,
    'verify' => false,
]);

// Step 1: GET login page
$resp = $client->get('/admin/login');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$token = $m[1];
echo "Step 1: Got CSRF token\n";

// Step 2: POST login
$resp = $client->post('/admin/login', [
    'form_params' => [
        'email' => 'admin@kts10pipsbots.com',
        'password' => 'Password123!',
        '_token' => $token,
    ],
]);
echo "Step 2: Login -> " . $resp->getStatusCode() . " Location: " . ($resp->getHeader('location')[0] ?? 'none') . "\n";

// Step 3: Follow redirect to OTP page
$resp = $client->get('/admin/otp/verify');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$otpToken = $m[1];
echo "Step 3: OTP page -> " . $resp->getStatusCode() . "\n";

// Step 4: Get OTP from DB (should be the one created by login)
$otp = AdminOtp::where('user_id', $user->id)->where('is_used', false)->where('expires_at', '>', now())->latest()->first();
echo "Step 4: OTP from DB: " . ($otp ? $otp->otp : 'NONE') . "\n";

// Step 5: Verify OTP
$resp = $client->post('/admin/otp/verify', [
    'form_params' => [
        'otp' => $otp->otp,
        '_token' => $otpToken,
    ],
]);
echo "Step 5: OTP verify -> " . $resp->getStatusCode() . " Location: " . ($resp->getHeader('location')[0] ?? 'none') . "\n";

// Step 6: Follow to security code page
$resp = $client->get('/admin/security-code/verify');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$secToken = $m[1];
echo "Step 6: Security code page -> " . $resp->getStatusCode() . "\n";

// Check if the page shows a new security code (since we deactivated all)
// The code is shown in the session via the controller, we need to extract it from the HTML
if (preg_match('/code-display[^>]*>([^<]+)/i', $html, $codeDisplay)) {
    echo "Step 6a: Code display: " . trim($codeDisplay[1]) . "\n";
}
// Also try to find the code in any visible element
if (preg_match('/<span[^>]*id="security[^"]*"[^>]*>([^<]+)/i', $html, $codeSpan)) {
    echo "Step 6b: Code span: " . trim($codeSpan[1]) . "\n";
}
// Try any strong/bold element that might contain the code
if (preg_match('/<strong[^>]*>([A-Z0-9]{8})<\/strong>/', $html, $codeStrong)) {
    echo "Step 6c: Code from strong: " . $codeStrong[1] . "\n";
}
// Try finding an 8-char uppercase alphanumeric code
if (preg_match('/([A-Z0-9]{8})/', $html, $codeMatch)) {
    echo "Step 6d: Possible code: " . $codeMatch[1] . "\n";
}

// Check DB for the newly generated code
$newCode = AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->latest()->first();
echo "Step 6e: New active code ID: " . ($newCode ? $newCode->id : 'NONE') . "\n";

// The controller generates a new code and puts it in the session as 'show_security_code'
// But we can't access that directly. Let me check if the view has a visible code.
// Let's save a snippet of the body
file_put_contents(__DIR__ . '/sec_page.html', $html);
echo "Step 6f: Saved security page HTML\n";

// Step 7: The system shows the code in the session (show_security_code), 
// but since we deactivated all codes, the controller generated a new one.
// Let's find it by looking at the latest code with label 'Initial Security Code'
$initialCode = AdminSecurityCode::where('user_id', $user->id)->where('label', 'Initial Security Code')->latest()->first();
if ($initialCode) {
    echo "Step 7: Found Initial Security Code ID: " . $initialCode->id . "\n";
}

// Actually, we can't get the plain text from DB since it's hashed.
// But we can find it if it was displayed in the page HTML. Let me search more carefully.
echo "Step 7: Searching for 8-char code in HTML...\n";
$allMatches = [];
preg_match_all('/\b([A-Z][A-Z0-9]{7})\b/', $html, $allMatches);
if (!empty($allMatches[1])) {
    foreach ($allMatches[1] as $match) {
        echo "  Found: $match\n";
    }
}
