<?php

class IntentRegistry
{
    private static array $intents = [];

    public static function load(): array
    {
        if (empty(self::$intents)) {
            self::$intents = require __DIR__ . '/../config/intents.php';
        }

        return self::$intents;
    }

    public static function validate(string $intent, array $params): void
    {
        $intents = self::load();

        if (!isset($intents[$intent])) {
            throw new Exception("Unknown intent: {$intent}");
        }

        $required = $intents[$intent]['parameters'] ?? [];
        foreach ($required as $param) {
            if (!isset($params[$param]) || trim((string)$params[$param]) === '') {
                throw new Exception("Missing parameter: {$param}");
            }
        }
    }
}
