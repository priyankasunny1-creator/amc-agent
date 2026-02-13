<?php

class IntentValidator
{
    public static function validate(array $intentData): void
    {
        if (!isset($intentData['intent'])) {
            throw new ApiException('Intent missing from resolver output.', 422, ['phase' => 'intent_validation']);
        }

        $registry = require __DIR__ . '/../config/intents.php';

        if (!isset($registry[$intentData['intent']])) {
            throw new ApiException('Unsupported intent.', 422, ['intent' => $intentData['intent']]);
        }

        $params = $intentData['parameters'] ?? [];
        $requiredParams = $registry[$intentData['intent']]['parameters'] ?? [];

        foreach ($requiredParams as $p) {
            if (!isset($params[$p]) || trim((string)$params[$p]) === '') {
                throw new ApiException("Missing parameter: {$p}", 422, ['missing_parameter' => $p]);
            }
        }
    }
}
