<?php

class SqlExecutor
{
    public static function execute(PDO $pdo, string $intent, array $params): array
    {
        $sqlTemplates = require __DIR__ . '/../config/sql_templates.php';

        if (!isset($sqlTemplates[$intent])) {
            throw new Exception("No SQL template found for intent: $intent");
        }

        $stmt = $pdo->prepare($sqlTemplates[$intent]);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
