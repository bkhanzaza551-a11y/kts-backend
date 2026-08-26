<?php

echo "=== Testing Free Market Data APIs ===\n\n";

$apis = [
    'CoinGecko' => 'https://api.coingecko.com/api/v3/ping',
    'CoinCap' => 'https://api.coincap.io/v2/assets?limit=1',
    'CryptoCompare' => 'https://min-api.cryptocompare.com/data/pricemultifull?fsyms=BTC&tsyms=USD',
];

foreach ($apis as $name => $url) {
    echo "Testing {$name}:\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP: {$httpCode}\n";
    if ($error) echo "   Error: {$error}\n";
    if ($httpCode === 200) {
        echo "   ✅ WORKS!\n";
    } else {
        echo "   ❌ Failed\n";
    }
    echo "\n";
}
