<?php

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'db' => getenv('DB_NAME') ?: '',
    'user' => getenv('DB_USER') ?: '',
    'pass' => getenv('DB_PASS') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4'
];
