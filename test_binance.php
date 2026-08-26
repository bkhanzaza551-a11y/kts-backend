<?php

echo "=== Testing Binance Public API (No Key Required) ===\n\n";

// Test 1: Get BTC Price
echo "1. BTC Price:\n";
$ch = curl_init('https://api.binance.com/api/v3/ticker/price?symbol=BTCUSDT');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "   HTTP Status: {$httpCode}\n";
echo "   Response: {$response}\n\n";

// Test 2: Get 24hr Ticker
echo "2. BTC 24hr Ticker:\n";
$ch = curl_init('https://api.binance.com/api/v3/ticker/24hr?symbol=BTCUSDT');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "   HTTP Status: {$httpCode}\n";
$data = json_decode($response, true);
if ($data) {
    echo "   Price: \${$data['lastPrice']}\n";
    echo "   24h High: \${$data['highPrice']}\n";
    echo "   24h Low: \${$data['lowPrice']}\n";
    echo "   24h Change: {$data['priceChangePercent']}%\n";
    echo "   Volume: " . number_format($data['volume']) . "\n";
}
echo "\n";

// Test 3: Search Symbols
echo "3. Searching for 'ETH':\n";
$ch = curl_init('https://api.binance.com/api/v3/exchangeInfo');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "   HTTP Status: {$httpCode}\n";
$data = json_decode($response, true);
if ($data && isset($data['symbols'])) {
    $ethSymbols = array_filter($data['symbols'], function($s) {
        return str_starts_with($s['symbol'], 'ETH') && $s['status'] === 'TRADING';
    });
    echo "   Found " . count($ethSymbols) . " ETH trading pairs:\n";
    foreach (array_slice(array_values($ethSymbols), 0, 5) as $s) {
        echo "   - {$s['symbol']} ({$s['baseAsset']}/{$s['quoteAsset']})\n";
    }
}

echo "\n=== Binance Public API is FREE and works! ===\n";
