<?php
$dir = __DIR__ . '/storage/app/public/stickers/trading-emojis';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$files = ['bull.png', 'bear.png', 'moon.png', 'loss.png', 'profit.png'];

// 1x1 transparent PNG base64
$base64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=";
$image = base64_decode($base64);

foreach ($files as $file) {
    file_put_contents($dir . '/' . $file, $image);
}
echo "Dummy images created!";
