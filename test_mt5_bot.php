<?php
/**
 * Module 6 — MT5 Bot Management — Comprehensive Verification Tests
 * Uses PHP curl with cookie jar (equivalent to .NET CookieContainer)
 */

$BASE      = 'http://localhost:8002';
$LOGIN_URL = "$BASE/admin/login";
$MT5_INDEX = "$BASE/admin/mt5-bot";
$EMAIL     = 'admin@kts10pipsbots.com';
$PASSWORD  = 'Password123!';

$results = [];
$cookieFile = sys_get_temp_dir() . '/mt5_test_cookies.txt';

// ─── curl helper ────────────────────────────────────────────────────────────────
function http($url, $method = 'GET', $postFields = null, $cookieFile = null,
             $followRedirect = false, $referer = null, $extraHeaders = []) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => $followRedirect,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) TestBot/1.0',
        CURLOPT_ENCODING       => '',
    ]);

    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($postFields !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }
    }

    if ($referer)  curl_setopt($ch, CURLOPT_REFERER, $referer);
    if (!empty($extraHeaders)) curl_setopt($ch, CURLOPT_HTTPHEADER, $extraHeaders);

    $response = curl_exec($ch);
    $info     = curl_getinfo($ch);
    curl_close($ch);

    // Split headers and body
    $headerSize = $info['header_size'];
    $headersRaw = substr($response, 0, $headerSize);
    $body       = substr($response, $headerSize);

    // Extract status code from first header line
    preg_match('#HTTP/\S+\s+(\d+)#', $headersRaw, $m);
    $statusCode = isset($m[1]) ? (int)$m[1] : 0;

    // Extract Location header
    $location = '';
    if (preg_match('#^Location:\s*(.+)$#mi', $headersRaw, $lm)) {
        $location = trim($lm[1]);
    }

    return [
        'statusCode' => $statusCode,
        'body'       => $body,
        'location'   => $location,
        'headers'    => $headersRaw,
    ];
}

function extractToken($html) {
    if (preg_match('#name="_token"\s+value="([^"]+)"#', $html, $m)) return $m[1];
    if (preg_match('#"_token"\s*:\s*"([^"]+)"#', $html, $m)) return $m[1];
    return null;
}

function record($test, $expected, $actual, $pass) {
    global $results;
    $results[] = compact('test', 'expected', 'actual', 'pass');
}

// Clean up old cookies
if (file_exists($cookieFile)) unlink($cookieFile);

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 1: Unauthenticated → expect 302
// ═════════════════════════════════════════════════════════════════════════════════
echo "[TEST 1] Unauthenticated GET /admin/mt5-bot → expect 302\n";
$tmpCookie = sys_get_temp_dir() . '/mt5_test_tmp.txt';
if (file_exists($tmpCookie)) unlink($tmpCookie);
$r = http($MT5_INDEX, 'GET', null, $tmpCookie);
$pass = $r['statusCode'] === 302;
record('1. Unauth → 302', '302', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']}) Location={$r['location']}\n";
unlink($tmpCookie);

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 2: Login flow → expect 302
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 2] Login: GET login → extract _token → POST → expect 302\n";
if (file_exists($cookieFile)) unlink($cookieFile);

$loginPage = http($LOGIN_URL, 'GET', null, $cookieFile);
echo "   GET /admin/login → {$loginPage['statusCode']}\n";

$token = extractToken($loginPage['body']);
echo "   _token: " . ($token ? substr($token, 0, 20) . '...' : 'NULL') . "\n";

if (!$token) {
    record('2. Login flow', '302', 'no token', false);
    echo "   FAIL: Could not extract _token\n";
} else {
    $postBody = http_build_query([
        '_token'   => $token,
        'email'    => $EMAIL,
        'password' => $PASSWORD,
    ]);
    $loginResp = http($LOGIN_URL, 'POST', $postBody, $cookieFile);
    echo "   POST /admin/login → {$loginResp['statusCode']}  Location={$loginResp['location']}\n";

    // Follow redirect to finalize session
    if ($loginResp['statusCode'] >= 300 && $loginResp['statusCode'] < 400 && $loginResp['location']) {
        $redir = str_starts_with($loginResp['location'], 'http')
            ? $loginResp['location']
            : $BASE . $loginResp['location'];
        $dash = http($redir, 'GET', null, $cookieFile);
        echo "   Follow → {$dash['statusCode']}  URL={$redir}\n";
    }

    $pass = $loginResp['statusCode'] === 302;
    record('2. Login flow', '302', (string)$loginResp['statusCode'], $pass);
    echo "   Result: " . ($pass ? "PASS" : "FAIL") . "\n";
}

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 3: Index → expect 200 + contains "MT5"
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 3] GET /admin/mt5-bot → expect 200 + contains 'MT5'\n";
$r = http($MT5_INDEX, 'GET', null, $cookieFile);
$hasMt5 = stripos($r['body'], 'MT5') !== false;
$pass = $r['statusCode'] === 200 && $hasMt5;
$detail = $r['statusCode'] === 200 ? ($hasMt5 ? 'contains MT5' : 'MT5 missing') : "got {$r['statusCode']}";
record('3. Index → 200 + MT5', '200 + MT5', "{$r['statusCode']} " . ($hasMt5 ? 'MT5 found' : 'MT5 missing'), $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " ({$detail})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 4: Create page → expect 200
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 4] GET /admin/mt5-bot/create → expect 200\n";
$r = http("$BASE/admin/mt5-bot/create", 'GET', null, $cookieFile);
$pass = $r['statusCode'] === 200;
record('4. Create page → 200', '200', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 5: Store (POST) → expect 302
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 5] POST /admin/mt5-bot (store) → expect 302\n";
$createPage = http("$BASE/admin/mt5-bot/create", 'GET', null, $cookieFile);
$token = extractToken($createPage['body']);
echo "   Token: " . ($token ? 'obtained' : 'NULL') . "\n";

if (!$token) {
    record('5. Store → 302', '302', 'no token', false);
    echo "   FAIL: no token\n";
} else {
    $nonce = time();
    $storeData = http_build_query([
        '_token'              => $token,
        'name'                => "TestBot_{$nonce}",
        'description'         => 'Automated test bot',
        'mt5_account_number'  => "12345" . ($nonce % 100),
        'mt5_server'          => 'MetaQuotes-Demo',
        'mt5_password'        => 'TestPass123!',
        'mode'                => 'demo',
        'lot_size'            => '0.01',
        'max_lot_size'        => '1.00',
        'take_profit_pips'    => '50',
        'stop_loss_pips'      => '30',
        'max_daily_trades'    => '10',
        'max_daily_loss'      => '100',
    ]);
    $r = http("$BASE/admin/mt5-bot", 'POST', $storeData, $cookieFile, false,
              "$BASE/admin/mt5-bot/create");
    $pass = $r['statusCode'] === 302;
    $detail = $pass ? "302 → {$r['location']}" : "got {$r['statusCode']}";
    record('5. Store → 302', '302', (string)$r['statusCode'], $pass);
    echo "   Result: " . ($pass ? "PASS" : "FAIL") . " ({$detail})\n";
}

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 6: Show → expect 200
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 6] GET /admin/mt5-bot/1 → expect 200\n";
$r = http("$BASE/admin/mt5-bot/1", 'GET', null, $cookieFile);
$pass = $r['statusCode'] === 200;
record('6. Show → 200', '200', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 7: Edit → expect 200
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 7] GET /admin/mt5-bot/1/edit → expect 200\n";
$r = http("$BASE/admin/mt5-bot/1/edit", 'GET', null, $cookieFile);
$pass = $r['statusCode'] === 200;
record('7. Edit → 200', '200', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 8: Logs → expect 200
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 8] GET /admin/mt5-bot/1/logs → expect 200\n";
$r = http("$BASE/admin/mt5-bot/1/logs", 'GET', null, $cookieFile);
$pass = $r['statusCode'] === 200;
record('8. Logs → 200', '200', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 9: Trades → expect 200
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 9] GET /admin/mt5-bot/1/trades → expect 200\n";
$r = http("$BASE/admin/mt5-bot/1/trades", 'GET', null, $cookieFile);
$pass = $r['statusCode'] === 200;
record('9. Trades → 200', '200', (string)$r['statusCode'], $pass);
echo "   Result: " . ($pass ? "PASS" : "FAIL") . " (got {$r['statusCode']})  BodyLen=" . strlen($r['body']) . "\n";

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 10: Toggle Status (PATCH via _method=PATCH) → expect 302
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 10] POST /admin/mt5-bot/1/toggle-status (PATCH) → expect 302\n";
$showPage = http("$BASE/admin/mt5-bot/1", 'GET', null, $cookieFile);
$token = extractToken($showPage['body']);
$tokenPart = $token ? substr($token, 0, 20) . '...' : 'NULL';
echo "   Token from show page: {$tokenPart}\n";

if (!$token) {
    record('10. Toggle Status → 302', '302', 'no token', false);
    echo "   FAIL: no token\n";
} else {
    $toggleData = "_token=" . rawurlencode($token) . "&_method=PATCH";
    $r = http("$BASE/admin/mt5-bot/1/toggle-status", 'POST', $toggleData, $cookieFile, false,
              "$BASE/admin/mt5-bot/1");
    $pass = $r['statusCode'] === 302;
    $detail = $pass ? "302 → {$r['location']}" : "got {$r['statusCode']}";
    record('10. Toggle Status → 302', '302', (string)$r['statusCode'], $pass);
    echo "   Result: " . ($pass ? "PASS" : "FAIL") . " ({$detail})\n";
}

// ═════════════════════════════════════════════════════════════════════════════════
//  TEST 11: Toggle Auto-Trade (PATCH via _method=PATCH) → expect 302
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n[TEST 11] POST /admin/mt5-bot/1/toggle-auto-trade (PATCH) → expect 302\n";
$showPage = http("$BASE/admin/mt5-bot/1", 'GET', null, $cookieFile);
$token = extractToken($showPage['body']);
$tokenPart = $token ? substr($token, 0, 20) . '...' : 'NULL';
echo "   Token from show page: {$tokenPart}\n";

if (!$token) {
    record('11. Toggle Auto-Trade → 302', '302', 'no token', false);
    echo "   FAIL: no token\n";
} else {
    $toggleData = "_token=" . rawurlencode($token) . "&_method=PATCH";
    $r = http("$BASE/admin/mt5-bot/1/toggle-auto-trade", 'POST', $toggleData, $cookieFile, false,
              "$BASE/admin/mt5-bot/1");
    $pass = $r['statusCode'] === 302;
    $detail = $pass ? "302 → {$r['location']}" : "got {$r['statusCode']}";
    record('11. Toggle Auto-Trade → 302', '302', (string)$r['statusCode'], $pass);
    echo "   Result: " . ($pass ? "PASS" : "FAIL") . " ({$detail})\n";
}

// ═════════════════════════════════════════════════════════════════════════════════
//  RESULTS TABLE
// ═════════════════════════════════════════════════════════════════════════════════
echo "\n\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║            MODULE 6 — MT5 BOT MANAGEMENT — VERIFICATION RESULTS           ║\n";
echo "╠════╦══════════════════════════════════════╦═══════════╦═══════════╦═════════╣\n";
echo "║  # ║ Test                                 ║ Expected  ║ Got       ║ Status  ║\n";
echo "╠════╬══════════════════════════════════════╬═══════════╬═══════════╬═════════╣\n";

$passCount = 0;
foreach ($results as $i => $r) {
    if ($r['pass']) $passCount++;
    $num   = str_pad((string)($i + 1), 2);
    $test  = str_pad($r['test'], 36);
    $exp   = str_pad($r['expected'], 9);
    $got   = str_pad($r['actual'], 9);
    $icon  = $r['pass'] ? 'PASS ✓ ' : 'FAIL ✗ ';
    echo "║ {$num} ║ {$test} ║ {$exp} ║ {$got} ║ {$icon}║\n";
}

echo "╠════╩══════════════════════════════════════╩═══════════╩═══════════╩═════════╣\n";
$total    = count($results);
$summary  = "  TOTAL: {$passCount}/{$total} passed";
$summaryP = str_pad($summary, 74);
echo "║{$summaryP}║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

// Cleanup
if (file_exists($cookieFile)) unlink($cookieFile);

exit($passCount === $total ? 0 : 1);
