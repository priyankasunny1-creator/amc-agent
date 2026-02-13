<?php

class ConversationMemory
{
    private static function path(string $id): string
    {
        return __DIR__ . "/../storage/conversations/{$id}.json";
    }

    public static function load(string $id): array
    {
        $file = self::path($id);
        if (!file_exists($file)) {
            return [];
        }

        return json_decode(file_get_contents($file), true) ?? [];
    }

    public static function save(string $id, array $memory): void
    {
        $dir = __DIR__ . '/../storage/conversations';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(
            self::path($id),
            json_encode($memory, JSON_PRETTY_PRINT)
        );
    }

    public static function remember(
        string $id,
        string $intent,
        array $metrics,
        array $parameters = []
    ): void {
        $memory = self::load($id);

        // Store successful execution context for follow-up turns.
        $memory['last_intent'] = $intent;
        $memory['last_metrics'] = $metrics;
        $memory['last_parameters'] = $parameters;
        $memory['updated_at'] = date('c');

        self::save($id, $memory);
    }

    public static function rememberClarification(string $id, array $clarification): void
    {
        $memory = self::load($id);

        // Persist unresolved intent context so a follow-up can resume execution.
        $memory['pending_clarification'] = $clarification;
        $memory['updated_at'] = date('c');

        self::save($id, $memory);
    }

    public static function clearClarification(string $id): void
    {
        $memory = self::load($id);

        unset($memory['pending_clarification']);
        $memory['updated_at'] = date('c');

        self::save($id, $memory);
    }
}
