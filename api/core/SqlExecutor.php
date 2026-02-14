<?php

require_once __DIR__ . '/ApiException.php';

class SqlExecutor
{
    public static function execute(?PDO $pdo, string $intent, array $params): array
    {
        if (!$pdo) {
            throw new ApiException(
                'Database is not configured for this environment. Please set DB_HOST, DB_NAME, DB_USER, and DB_PASS.',
                503,
                ['phase' => 'db_bootstrap']
            );
        }

        $sqlTemplates = require __DIR__ . '/../config/sql_templates.php';

        if (!isset($sqlTemplates[$intent])) {
            throw new ApiException(
                "No SQL template found for intent: {$intent}",
                422,
                ['phase' => 'sql_template_lookup', 'intent' => $intent]
            );
        }

        $stmt = $pdo->prepare($sqlTemplates[$intent]);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
