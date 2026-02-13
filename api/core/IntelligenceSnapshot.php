<?php

class IntelligenceSnapshot
{
    private static string $file = __DIR__ . '/../storage/intelligence/snapshots.json';

    public static function load(string $intent): ?array
    {
        if (!file_exists(self::$file)) {
            return null;
        }

        $all = json_decode(file_get_contents(self::$file), true);
        return $all[$intent] ?? null;
    }

    public static function save(string $intent, array $metrics): void
    {
        $all = file_exists(self::$file)
            ? json_decode(file_get_contents(self::$file), true)
            : [];

        $all[$intent] = array_merge(
            $metrics,
            ['timestamp' => date('c')]
        );

        file_put_contents(self::$file, json_encode($all, JSON_PRETTY_PRINT));
    }
}
