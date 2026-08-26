<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminOtp;
use App\Models\AdminSecurityCode;
use App\Models\User;

$user = User::where('email', 'admin@kts10pipsbots.com')->first();
AdminSecurityCode::where('user_id', $user->id)->update(['is_active' => false]);
AdminOtp::where('user_id', $user->id)->where('is_used', false)->update(['is_used' => true]);

$client = new \GuzzleHttp\Client([
    'base_uri' => 'http://127.0.0.1:8002',
    'cookies' => true,
    'allow_redirects' => false,
    'verify' => false,
    'connect_timeout' => 10,
    'timeout' => 30,
    'http_errors' => false,
]);

// Login
$resp = $client->get('/admin/login');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$resp = $client->post('/admin/login', [
    'form_params' => ['email' => 'admin@kts10pipsbots.com', 'password' => 'Password123!', '_token' => $m[1]],
]);

// OTP form
$resp = $client->get('/admin/otp/verify');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$otpToken = $m[1];
$otp = AdminOtp::where('user_id', $user->id)->where('is_used', false)->where('expires_at', '>', now())->latest()->first();

// Verify OTP
$resp = $client->post('/admin/otp/verify', [
    'form_params' => ['otp' => $otp->otp, '_token' => $otpToken],
]);
echo "OTP Verify: " . $resp->getStatusCode() . "\n";

// Security code form - get the generated code from page
$resp = $client->get('/admin/security-code/verify');
$html = (string)$resp->getBody();
preg_match('/name="_token"[^>]*value="([^"]+)"/', $html, $m);
$secToken = $m[1];
preg_match('/<code[^>]*>([A-Z0-9]{8})<\/code>/', $html, $codeMatch);
if (!$codeMatch) {
    preg_match('/text-success[^>]*>([A-Z0-9]{8})/', $html, $codeMatch);
}
if (!$codeMatch) {
    preg_match('/letter-spacing:4px;">([A-Z0-9]{8})/', $html, $codeMatch);
}
$securityCode = $codeMatch[1] ?? 'NONE';
echo "Security code from page: $securityCode\n";

// Verify security code
$resp = $client->post('/admin/security-code/verify', [
    'form_params' => ['security_code' => $securityCode, '_token' => $secToken],
]);
echo "Security Code Verify: " . $resp->getStatusCode() . " -> " . ($resp->getHeader('location')[0] ?? 'none') . "\n";

sleep(2);

// Test dashboard
$resp = $client->get('/admin/dashboard');
$dashStatus = $resp->getStatusCode();
echo "Dashboard: $dashStatus\n";

if ($dashStatus != 200) {
    echo "AUTH FAILED\n";
    exit(1);
}

echo "\nAUTH SUCCESS - Running full URL tests...\n\n";

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

foreach ($urls as $url) {
    usleep(500000); // 500ms delay
    try {
        $resp = $client->get($url);
        $status = $resp->getStatusCode();
        $error = "";
        
        if ($status >= 400) {
            $body = (string)$resp->getBody();
            if (preg_match('/<title>([^<]+)<\/title>/', $body, $titleMatch)) {
                $error = trim($titleMatch[1]);
            }
            if (preg_match('/"message":"([^"]+)"/', $body, $msgMatch)) {
                $error = $msgMatch[1];
            }
        }
        
        echo "$status|$url|$error\n";
    } catch (\Exception $e) {
        echo "ERR|$url|" . $e->getMessage() . "\n";
    }
}
