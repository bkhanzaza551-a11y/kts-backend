using System;
using System.IO;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Collections.Generic;

// ─── Configuration ───────────────────────────────────────────────────────────────
const string BASE = "http://localhost:8002";
const string LOGIN_URL = BASE + "/admin/login";
const string LOGIN_POST_URL = BASE + "/admin/login";
const string MT5_INDEX = BASE + "/admin/mt5-bot";
const string MT5_CREATE = BASE + "/admin/mt5-bot/create";
const string MT5_STORE = BASE + "/admin/mt5-bot";
const string MT5_SHOW_1 = BASE + "/admin/mt5-bot/1";
const string MT5_EDIT_1 = BASE + "/admin/mt5-bot/1/edit";
const string MT5_LOGS_1 = BASE + "/admin/mt5-bot/1/logs";
const string MT5_TRADES_1 = BASE + "/admin/mt5-bot/1/trades";
const string MT5_TOGGLE_STATUS_1 = BASE + "/admin/mt5-bot/1/toggle-status";
const string MT5_TOGGLE_AUTO_TRADE_1 = BASE + "/admin/mt5-bot/1/toggle-auto-trade";
const string EMAIL = "admin@kts10pipsbots.com";
const string PASSWORD = "Password123!";

var results = new List<(string Test, string Expected, string Actual, bool Pass)>();

// ─── Helper ──────────────────────────────────────────────────────────────────────
HttpWebRequest MakeReq(string url, string method = "GET", CookieContainer cookies = null)
{
    var req = (HttpWebRequest)WebRequest.Create(url);
    req.Method = method;
    req.AllowAutoRedirect = false;
    req.ContentType = "application/x-www-form-urlencoded";
    if (cookies != null) req.CookieContainer = cookies;
    req.UserAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36";
    req.Accept = "text/html,application/xhtml+xml,*/*";
    req.KeepAlive = true;
    return req;
}

string ExtractToken(string html)
{
    var m = Regex.Match(html, @"name=""_token""\s+value=""([^""]+)""");
    if (m.Success) return m.Groups[1].Value;
    m = Regex.Match(html, @"""_token""\s*:\s*""([^""]+)""");
    if (m.Success) return m.Groups[1].Value;
    return null;
}

(string statusLine, int statusCode, string body, CookieContainer cookies, string redirectUrl)
    SendRequest(string url, string method = "GET", string postData = null, CookieContainer cookies = null,
                bool followRedirect = false, string referer = null)
{
    try
    {
        var req = MakeReq(url, method, cookies);
        if (referer != null) req.Referer = referer;

        if (method == "POST" && postData != null)
        {
            var bytes = Encoding.UTF8.GetBytes(postData);
            req.ContentLength = bytes.Length;
            using (var stream = req.GetRequestStream()) { stream.Write(bytes, 0, bytes.Length); }
        }

        HttpWebResponse resp;
        try { resp = (HttpWebResponse)req.GetResponse(); }
        catch (WebException ex) { resp = (HttpWebResponse)ex.Response; }

        int code = (int)resp.StatusCode;
        string loc = resp.Headers["Location"] ?? "";

        using (var sr = new StreamReader(resp.GetResponseStream()))
        {
            string body = sr.ReadToEnd();
            resp.Close();

            // Handle redirect manually
            if (followRedirect && code >= 300 && code < 400 && !string.IsNullOrEmpty(loc))
            {
                string redir = loc.StartsWith("http") ? loc : BASE + loc;
                return SendRequest(redir, "GET", null, cookies, false, url);
            }

            return (resp.StatusDescription ?? "", code, body, cookies, loc);
        }
    }
    catch (Exception ex)
    {
        return ("EXCEPTION", 0, ex.Message, cookies, "");
    }
}

// ─── Cookie jar for auth session ─────────────────────────────────────────────────
var cookies = new CookieContainer();

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 1: Unauthenticated → expect 302
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("[TEST 1] Unauthenticated GET /admin/mt5-bot → expect 302");
try
{
    var (status, code, body, _, loc) = SendRequest(MT5_INDEX);
    bool pass = code == 302;
    string detail = pass ? "302 → OK" : $"got {code} ({status})";
    results.Add(("1. Unauth → 302", "302", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} ({detail})  Location={loc}");
}
catch (Exception ex)
{
    results.Add(("1. Unauth → 302", "302", "EXCEPTION", false));
    Console.WriteLine($"   Result: FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 2: Login flow
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 2] Login: GET login → extract _token → POST → expect 302");
try
{
    var loginCookies = new CookieContainer();
    var (s1, c1, body1, _, _) = SendRequest(LOGIN_URL, "GET", null, loginCookies);
    Console.WriteLine($"   GET /admin/login → {c1}");

    string token = ExtractToken(body1);
    Console.WriteLine($"   Extracted _token: {(token != null ? token.Substring(0, Math.Min(20, token.Length)) + "..." : "NULL")}");

    if (token == null)
    {
        results.Add(("2. Login flow", "302", "no token found", false));
        Console.WriteLine("   FAIL: Could not extract _token");
    }
    else
    {
        string postBody = $"_token={Uri.EscapeDataString(token)}&email={Uri.EscapeDataString(EMAIL)}&password={Uri.EscapeDataString(PASSWORD)}";
        // Manually handle redirect so we capture session cookie from the redirect response
        var req = MakeReq(LOGIN_POST_URL, "POST", loginCookies);
        var bytes = Encoding.UTF8.GetBytes(postBody);
        req.ContentLength = bytes.Length;
        using (var stream = req.GetRequestStream()) { stream.Write(bytes, 0, bytes.Length); }

        HttpWebResponse resp;
        try { resp = (HttpWebResponse)req.GetResponse(); }
        catch (WebException ex) { resp = (HttpWebResponse)ex.Response; }

        int c2 = (int)resp.StatusCode;
        string loc2 = resp.Headers["Location"] ?? "";
        string body2 = "";
        using (var sr = new StreamReader(resp.GetResponseStream())) { body2 = sr.ReadToEnd(); resp.Close(); }

        Console.WriteLine($"   POST /admin/login → {c2}  Location={loc2}");

        // Follow redirect to get session
        if (c2 >= 300 && c2 < 400 && !string.IsNullOrEmpty(loc2))
        {
            string redirUrl = loc2.StartsWith("http") ? loc2 : BASE + loc2;
            var (s3, c3, body3, _, _) = SendRequest(redirUrl, "GET", null, loginCookies);
            Console.WriteLine($"   Follow → {c3}  URL={redirUrl}");
        }

        // Copy session cookies to main cookie jar
        cookies = loginCookies;
        bool pass = c2 == 302;
        results.Add(("2. Login flow", "302", c2.ToString(), pass));
        Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")}");
    }
}
catch (Exception ex)
{
    results.Add(("2. Login flow", "302", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 3: Index page → expect 200, contains "MT5"
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 3] GET /admin/mt5-bot → expect 200 + contains 'MT5'");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_INDEX, "GET", null, cookies);
    bool hasMt5 = body.IndexOf("MT5", StringComparison.OrdinalIgnoreCase) >= 0;
    bool pass = code == 200 && hasMt5;
    string detail = code == 200 ? (hasMt5 ? "contains MT5" : "missing MT5") : $"got {code}";
    results.Add(("3. Index → 200 + MT5", "200 + MT5", $"{code} {(hasMt5 ? "MT5 found" : "MT5 missing")}", pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} ({detail})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("3. Index → 200 + MT5", "200 + MT5", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 4: Create page → expect 200
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 4] GET /admin/mt5-bot/create → expect 200");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_CREATE, "GET", null, cookies);
    bool pass = code == 200;
    results.Add(("4. Create page → 200", "200", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("4. Create page → 200", "200", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 5: Store (POST with valid data) → expect 302
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 5] POST /admin/mt5-bot (store) → expect 302");
try
{
    // First get fresh token from create page
    var (s1, c1, body1, _, _) = SendRequest(MT5_CREATE, "GET", null, cookies);
    string token = ExtractToken(body1);
    Console.WriteLine($"   Token obtained: {(token != null ? "yes" : "no")}");

    if (token == null)
    {
        results.Add(("5. Store → 302", "302", "no token", false));
        Console.WriteLine("   FAIL: Could not extract token");
    }
    else
    {
        string nonce = DateTime.Now.Ticks.ToString();
        string postData =
            $"_token={Uri.EscapeDataString(token)}" +
            $"&name={Uri.EscapeDataString("TestBot_" + nonce)}" +
            $"&description={Uri.EscapeDataString("Test bot")}" +
            $"&mt5_account_number={Uri.EscapeDataString("12345" + (DateTime.Now.Second % 100))}" +
            $"&mt5_server={Uri.EscapeDataString("MetaQuotes-Demo")}" +
            $"&mt5_password={Uri.EscapeDataString("TestPass123")}" +
            $"&mode=demo" +
            $"&lot_size=0.01" +
            $"&max_lot_size=1.00" +
            $"&take_profit_pips=50" +
            $"&stop_loss_pips=30" +
            $"&max_daily_trades=10" +
            $"&max_daily_loss=100";

        var (s, code, body, _, loc) = SendRequest(MT5_STORE, "POST", postData, cookies, false, MT5_CREATE);
        bool pass = code == 302;
        string detail = pass ? $"302 → {loc}" : $"got {code}";
        results.Add(("5. Store → 302", "302", code.ToString(), pass));
        Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} ({detail})");
    }
}
catch (Exception ex)
{
    results.Add(("5. Store → 302", "302", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 6: Show → expect 200
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 6] GET /admin/mt5-bot/1 → expect 200");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_SHOW_1, "GET", null, cookies);
    bool pass = code == 200;
    results.Add(("6. Show → 200", "200", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("6. Show → 200", "200", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 7: Edit → expect 200
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 7] GET /admin/mt5-bot/1/edit → expect 200");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_EDIT_1, "GET", null, cookies);
    bool pass = code == 200;
    results.Add(("7. Edit → 200", "200", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("7. Edit → 200", "200", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 8: Logs → expect 200
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 8] GET /admin/mt5-bot/1/logs → expect 200");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_LOGS_1, "GET", null, cookies);
    bool pass = code == 200;
    results.Add(("8. Logs → 200", "200", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("8. Logs → 200", "200", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 9: Trades → expect 200
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 9] GET /admin/mt5-bot/1/trades → expect 200");
try
{
    var (s, code, body, _, _) = SendRequest(MT5_TRADES_1, "GET", null, cookies);
    bool pass = code == 200;
    results.Add(("9. Trades → 200", "200", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  BodyLen={body.Length}");
}
catch (Exception ex)
{
    results.Add(("9. Trades → 200", "200", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 10: Toggle Status (PATCH) → expect 302
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 10] POST /admin/mt5-bot/1/toggle-status (PATCH) → expect 302");
try
{
    // Get a CSRF token from the show page
    var (s0, c0, body0, _, _) = SendRequest(MT5_SHOW_1, "GET", null, cookies);
    string token = ExtractToken(body0);
    string postData = token != null ? $"_token={Uri.EscapeDataString(token)}" : "";

    // Use PATCH method
    var req = MakeReq(MT5_TOGGLE_STATUS_1, "POST", cookies);
    req.Headers["X-HTTP-Method-Override"] = "PATCH";
    req.Method = "POST";

    // Add _method=PATCH for Laravel
    string fullPost = postData + "&_method=PATCH";
    var bytes = Encoding.UTF8.GetBytes(fullPost);
    req.ContentLength = bytes.Length;
    using (var stream = req.GetRequestStream()) { stream.Write(bytes, 0, bytes.Length); }

    HttpWebResponse resp;
    try { resp = (HttpWebResponse)req.GetResponse(); }
    catch (WebException ex) { resp = (HttpWebResponse)ex.Response; }

    int code = (int)resp.StatusCode;
    string loc = resp.Headers["Location"] ?? "";
    resp.Close();

    bool pass = code == 302;
    results.Add(("10. Toggle Status → 302", "302", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  Location={loc}");
}
catch (Exception ex)
{
    results.Add(("10. Toggle Status → 302", "302", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  TEST 11: Toggle Auto-Trade (PATCH) → expect 302
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n[TEST 11] POST /admin/mt5-bot/1/toggle-auto-trade (PATCH) → expect 302");
try
{
    // Get a CSRF token from the show page
    var (s0, c0, body0, _, _) = SendRequest(MT5_SHOW_1, "GET", null, cookies);
    string token = ExtractToken(body0);
    string postData = token != null ? $"_token={Uri.EscapeDataString(token)}" : "";

    var req = MakeReq(MT5_TOGGLE_AUTO_TRADE_1, "POST", cookies);
    req.Method = "POST";

    string fullPost = postData + "&_method=PATCH";
    var bytes = Encoding.UTF8.GetBytes(fullPost);
    req.ContentLength = bytes.Length;
    using (var stream = req.GetRequestStream()) { stream.Write(bytes, 0, bytes.Length); }

    HttpWebResponse resp;
    try { resp = (HttpWebResponse)req.GetResponse(); }
    catch (WebException ex) { resp = (HttpWebResponse)ex.Response; }

    int code = (int)resp.StatusCode;
    string loc = resp.Headers["Location"] ?? "";
    resp.Close();

    bool pass = code == 302;
    results.Add(("11. Toggle Auto-Trade → 302", "302", code.ToString(), pass));
    Console.WriteLine($"   Result: {(pass ? "PASS" : "FAIL")} (got {code})  Location={loc}");
}
catch (Exception ex)
{
    results.Add(("11. Toggle Auto-Trade → 302", "302", "EXCEPTION", false));
    Console.WriteLine($"   FAIL ({ex.Message})");
}

// ═══════════════════════════════════════════════════════════════════════════════════
//  RESULTS TABLE
// ═══════════════════════════════════════════════════════════════════════════════════
Console.WriteLine("\n");
Console.WriteLine("╔══════════════════════════════════════════════════════════════════════════════╗");
Console.WriteLine("║            MODULE 6 – MT5 BOT MANAGEMENT — VERIFICATION RESULTS           ║");
Console.WriteLine("╠════╦══════════════════════════════════════╦═══════════╦═══════════╦═════════╣");
Console.WriteLine("║  # ║ Test                                 ║ Expected  ║ Got       ║ Status  ║");
Console.WriteLine("╠════╬══════════════════════════════════════╬═══════════╬═══════════╬═════════╣");

int passCount = 0;
for (int i = 0; i < results.Count; i++)
{
    var (test, expected, actual, pass) = results[i];
    if (pass) passCount++;
    string icon = pass ? "PASS ✓" : "FAIL ✗";
    string num = (i + 1).ToString().PadLeft(2);
    Console.WriteLine($"║ {num} ║ {test,-36} ║ {expected,-9} ║ {actual,-9} ║ {icon,-7} ║");
}

Console.WriteLine("╠════╩══════════════════════════════════════╩═══════════╩═══════════╩═════════╣");
string summary = $"  TOTAL: {passCount}/{results.Count} passed";
Console.WriteLine($"║{summary,-74}║");
Console.WriteLine("╚══════════════════════════════════════════════════════════════════════════════╝");
