<?php

// --------------------------------------------------
// Load .env manually (REQUIRED for WAMP / Windows)
// --------------------------------------------------
$envFile = __DIR__ . '/.env';

if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line === '' || str_starts_with(trim($line), '#')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

// --------------------------------------------------
// Database bootstrap
// --------------------------------------------------
$config = require __DIR__ . '/config/database.php';

$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";

return new PDO(
    $dsn,
    $config['user'],
    $config['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
