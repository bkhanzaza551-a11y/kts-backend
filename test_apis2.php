<?php

echo "=== Testing More APIs ===\n\n";

$apis = [
    'Binance (data-stream)' => 'https://data-stream.binance.vision/api/v3/ticker/price?symbol=BTCUSDT',
    'Binance (fapi)' => 'https://fapi.binance.com/fapi/v1/ticker/price?symbol=BTCUSDT',
    'Kraken' => 'https://api.kraken.com/0/public/Ticker?pair=XBTUSD',
    'KuCoin' => 'https://api.kucoin.com/api/v1/market/orderbook/level1?symbol=BTC-USDT',
    'Bybit' => 'https://api.bybit.com/v5/market/tickers?category=spot&symbol=BTCUSDT',
    'OKX' => 'https://www.okx.com/api/v5/market/ticker?instId=BTC-USDT',
    'Gate.io' => 'https://api.gateio.ws/api/v4/spot/tickers?currency_pair=BTC_USDT',
    'MEXC' => 'https://api.mexc.com/api/v3/ticker/24hr?symbol=BTCUSDT',
    'CoinGecko (v3)' => 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd',
    'Binance (api1)' => 'https://api1.binance.com/api/v3/ticker/price?symbol=BTCUSDT',
    'Binance (api2)' => 'https://api2.binance.com/api/v3/ticker/price?symbol=BTCUSDT',
    'Binance (api3)' => 'https://api3.binance.com/api/v3/ticker/price?symbol=BTCUSDT',
];

foreach ($apis as $name => $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $status = $httpCode === 200 ? '✅' : '❌';
    echo "{$status} {$name}: HTTP {$httpCode}";
    if ($httpCode !== 200 && $error) echo " ({$error})";
    echo "\n";
    if ($httpCode === 200 && $response) {
        echo "   Data: " . substr($response, 0, 120) . "\n";
    }
}
