<?php

class IntentRegistry
{
    private static $intents;

    public static function load()
    {
        if (!self::$intents) {
            self::$intents = require __DIR__ . '/../config/intents.php';
        }
        return self::$intents;
    }

    public static function validate($intent, $params)
    {
        $intents = self::load();

        if (!isset($intents[$intent])) {
            throw new Exception("Unknown intent: $intent");
        }

        foreach ($intents[$intent]['required_params'] as $param) {
            if (!isset($params[$param])) {
                throw new Exception("Missing parameter: $param");
            }
        }
    }
}
