<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Clean state
$user = User::where('email', 'admin@kts10pipsbots.com')->first();
AdminOtp::where('user_id', $user->id)->where('is_used', false)->update(['is_used' => true]);

// Ensure known security code exists
$secCode = AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->first();
if (!$secCode) {
    $result = AdminSecurityCode::generateFor($user, 'AutoTest');
    echo "CREATED_SECURITY_CODE=" . $result['code'] . "\n";
}

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

if (!$otp) {
    echo "ERROR: No OTP found!\n";
    exit(1);
}

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

// Check if there's a new security code shown
preg_match('/show_security_code[^"]*"[^>]*value="([^"]+)"/', $html, $showM);
if (isset($showM[1])) {
    echo "Step 6a: Security code shown in session: " . $showM[1] . "\n";
}

// Check for any visible code in the page
preg_match('/<input[^>]*name="security_code"[^>]*value="([^"]+)"/', $html, $valM);
if (isset($valM[1])) {
    echo "Step 6b: Input value found: " . $valM[1] . "\n";
}

// Find the security code from the form or use known one
$secCodeRecord = AdminSecurityCode::where('user_id', $user->id)->where('is_active', true)->latest()->first();
echo "Step 6c: Active security code ID: " . ($secCodeRecord ? $secCodeRecord->id : 'NONE') . "\n";

// Step 7: Try to verify security code with the active one (it's hashed, so we need the plain text)
// The problem is we don't know the plain text of the existing code.
// Let's use the one we created
$securityCode = null;
// Check if we created one in this session by looking at very recent ones
$newSecCode = AdminSecurityCode::where('user_id', $user->id)->where('label', 'AutoTest')->where('is_active', true)->latest()->first();
if ($newSecCode) {
    // We need the plain text. Let's look for it.
    echo "Step 7: Found AutoTest security code ID: " . $newSecCode->id . "\n";
}

// Generate a new known security code and verify with it
$result = AdminSecurityCode::generateFor($user, 'FreshTest');
$plainCode = $result['code'];
echo "Step 7: Created new security code: " . $plainCode . "\n";

// Now verify with this known code
$resp = $client->post('/admin/security-code/verify', [
    'form_params' => [
        'security_code' => $plainCode,
        '_token' => $secToken,
    ],
]);
echo "Step 8: Security code verify -> " . $resp->getStatusCode() . " Location: " . ($resp->getHeader('location')[0] ?? 'none') . "\n";

// Step 9: Test dashboard
$resp = $client->get('/admin/dashboard');
echo "Step 9: Dashboard -> " . $resp->getStatusCode() . " Length: " . strlen((string)$resp->getBody()) . "\n";

if ($resp->getStatusCode() == 200) {
    echo "\n=== AUTHENTICATION SUCCESSFUL ===\n";
    
    // Now test ALL URLs
    $urls = [
        "/admin/dashboard",
        "/admin/users",
        "/admin/users/create",
        "/admin/signals",
        "/admin/signals/create",
        "/admin/signal-categories",
        "/admin/signal-categories/create",
        "/admin/mt5-bots",
        "/admin/mt5-bots/create",
        "/admin/mt5-logs",
        "/admin/mt5-trades",
        "/admin/mt5-credentials",
        "/admin/education/courses",
        "/admin/education/courses/create",
        "/admin/education/categories",
        "/admin/education/categories/create",
        "/admin/chat/rooms",
        "/admin/chat/badges",
        "/admin/chat/restricted-words",
        "/admin/chat/stickers",
        "/admin/chat/stickers/create",
        "/admin/ai-chatbot/logs",
        "/admin/notifications/templates",
        "/admin/notifications/history",
        "/admin/notifications/templates/create",
        "/admin/payments",
        "/admin/payments/pending",
        "/admin/payments/gateways",
        "/admin/payments/settings",
        "/admin/demo-accounts",
        "/admin/demo-settings",
        "/admin/legal-pages",
        "/admin/legal-pages/create",
        "/admin/settings/general",
        "/admin/settings/smtp",
        "/admin/settings/security",
        "/admin/audit-log",
        "/admin/analytics/mt5",
        "/admin/staff",
        "/admin/staff/create",
        "/admin/roles",
        "/admin/roles/create",
        "/admin/cache",
        "/admin/backups",
    ];

    echo "\n=== TEST RESULTS ===\n";
    echo str_pad("URL", 45) . str_pad("Status", 8) . "Error\n";
    echo str_repeat("-", 80) . "\n";

    foreach ($urls as $url) {
        try {
            $resp = $client->get($url);
            $status = $resp->getStatusCode();
            $error = "";
            
            if ($status >= 400) {
                $body = (string)$resp->getBody();
                // Extract error message
                if (preg_match('/<title>([^<]+)<\/title>/', $body, $titleMatch)) {
                    $error = trim($titleMatch[1]);
                } elseif (preg_match('/"message":"([^"]+)"/', $body, $msgMatch)) {
                    $error = $msgMatch[1];
                } elseif (preg_match('/class="[^"]*error[^"]*"[^>]*>([^<]+)/', $body, $errMatch)) {
                    $error = trim($errMatch[1]);
                }
            }
            
            echo str_pad($url, 45) . str_pad($status, 8) . $error . "\n";
        } catch (\Exception $e) {
            echo str_pad($url, 45) . str_pad("ERR", 8) . $e->getMessage() . "\n";
        }
    }
} else {
    echo "\nAUTHENTICATION FAILED\n";
    echo "Response body snippet: " . substr((string)$resp->getBody(), 0, 500) . "\n";
}
