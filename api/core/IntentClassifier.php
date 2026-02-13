<?php

class IntentClassifier
{
    public static function classify(string $message): array
    {
        $intentConfig = require __DIR__ . '/../config/intents.php';
        $allowedIntents = array_keys($intentConfig);

        $systemPrompt = <<<PROMPT
You are an intent matcher for an internal AMC system.

Given a user message and a list of allowed intents,
return the TOP 3 most relevant intent names in descending order.

Rules:
- Use ONLY the provided intent names
- Do NOT invent new intents
- Do NOT explain anything
- Return valid JSON only

JSON format:
{
  "candidates": ["intent_1", "intent_2", "intent_3"]
}
PROMPT;

        $systemPrompt .= "\n\nAllowed intents:\n- " . implode("\n- ", $allowedIntents);

        $raw = OpenAIClient::classify($systemPrompt, $message);

        $decoded = json_decode($raw, true);

        if (
            !$decoded ||
            !isset($decoded['candidates']) ||
            !is_array($decoded['candidates']) ||
            empty($decoded['candidates'])
        ) {
            throw new Exception('Invalid intent candidate response');
        }

        // -----------------------------------------
        // STAGE 1: Take top candidate
        // -----------------------------------------
        $intent = $decoded['candidates'][0];

        // -----------------------------------------
        // STAGE 2: Apply deterministic derived rules
        // -----------------------------------------
        $intent = self::applyDerivedRules($message, $intent);

        // -----------------------------------------
        // FINAL OUTPUT (LOCKED FORMAT)
        // -----------------------------------------
        return [
            'intent' => $intent,
            'candidates' => $decoded['candidates']
        ];
    }

    private static function applyDerivedRules(string $message, string $intent): string
    {
        $msg = strtolower($message);

        // Aggregate AMC usage questions
        if (
            str_contains($msg, 'how many') &&
            str_contains($msg, 'amc') &&
            (
                str_contains($msg, 'more than') ||
                str_contains($msg, 'above') ||
                str_contains($msg, 'over')
            )
        ) {
            return 'get_clients_above_amc_usage_threshold';
        }

        return $intent;
    }
}
