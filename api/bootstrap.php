<?php

$envFile = __DIR__ . '/.env';

if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line === '' || str_starts_with(trim($line), '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

$config = require __DIR__ . '/config/database.php';

$required = ['host', 'db', 'user', 'charset'];
foreach ($required as $key) {
    if (trim((string)($config[$key] ?? '')) === '') {
        return null;
    }
}

$dsn = "mysql:host={$config['host']};dbname={$config['db']};charset={$config['charset']}";

try {
    return new PDO(
        $dsn,
        $config['user'],
        $config['pass'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (Throwable $e) {
    return null;
}
