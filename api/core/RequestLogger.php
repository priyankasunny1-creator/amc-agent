<?php

class RequestLogger
{
    private static string $logDir = __DIR__ . '/../logs/';

    public static function log(array $payload): void
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
        }

        $file = self::$logDir . 'amc_requests_' . date('Y-m-d') . '.jsonl';

        $payload['timestamp'] = date('c');

        file_put_contents(
            $file,
            json_encode($payload, JSON_UNESCAPED_UNICODE) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }
}
