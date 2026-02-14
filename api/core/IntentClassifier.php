<?php

class IntentClassifier
{
    public static function classify(string $message): array
    {
        $normalized = self::normalizeMessage($message);

        // Deterministic shortcuts for high-frequency queries.
        if (self::isWpPendingQuery($normalized)) {
            return [
                'intent' => 'get_wp_tasks_summary',
                'candidates' => ['get_wp_tasks_summary', 'get_ongoing_tasks_summary', 'get_overdue_tasks_summary']
            ];
        }

        if (self::isActiveClientsQuery($normalized)) {
            return [
                'intent' => 'get_active_clients_count',
                'candidates' => ['get_active_clients_count', 'get_clients_by_task_load', 'get_ongoing_tasks_summary']
            ];
        }

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

        $raw = OpenAIClient::classify($systemPrompt, $normalized);

        $decoded = json_decode($raw, true);

        if (
            !$decoded ||
            !isset($decoded['candidates']) ||
            !is_array($decoded['candidates']) ||
            empty($decoded['candidates'])
        ) {
            throw new ApiException(
                'I could not confidently classify this query. Please rephrase with clear business terms.',
                422,
                ['phase' => 'intent_classification', 'raw_response' => $raw]
            );
        }

        $intent = $decoded['candidates'][0];
        $intent = self::applyDerivedRules($normalized, $intent);

        return [
            'intent' => $intent,
            'candidates' => $decoded['candidates']
        ];
    }

    private static function normalizeMessage(string $message): string
    {
        $msg = strtolower(trim($message));
        $msg = str_replace(['wp', 'wordpresses'], ['wordpress', 'wordpress'], $msg);
        return preg_replace('/\s+/', ' ', $msg);
    }

    private static function isWpPendingQuery(string $msg): bool
    {
        return str_contains($msg, 'wordpress')
            && (str_contains($msg, 'pending') || str_contains($msg, 'active') || str_contains($msg, 'ongoing'))
            && str_contains($msg, 'task');
    }

    private static function isActiveClientsQuery(string $msg): bool
    {
        return (
            str_contains($msg, 'active clients') ||
            str_contains($msg, 'number of active clients') ||
            str_contains($msg, 'count of active clients')
        );
    }

    private static function applyDerivedRules(string $message, string $intent): string
    {
        $msg = strtolower($message);

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

        if (self::isActiveClientsQuery($msg)) {
            return 'get_active_clients_count';
        }

        return $intent;
    }
}
