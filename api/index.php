<?php

// Serverless entrypoint for Vercel Deployment

// Ensure required writable storage paths exist in ephemeral /tmp
$ephemeralPaths = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public',
    '/tmp/views',
    '/tmp/cache',
    '/tmp/sessions',
];

foreach ($ephemeralPaths as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Normalize SCRIPT_NAME so Symfony does not strip /api prefix from routes
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Forward request to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
