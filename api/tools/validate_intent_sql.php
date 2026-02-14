<?php

$intents = require __DIR__ . '/../config/intents.php';
$sql = require __DIR__ . '/../config/sql_templates.php';

$intentNames = array_keys($intents);
$sqlNames = array_keys($sql);

$missingSql = array_values(array_diff($intentNames, $sqlNames));
$orphanSql = array_values(array_diff($sqlNames, $intentNames));

$result = [
    'intent_count' => count($intentNames),
    'sql_count' => count($sqlNames),
    'missing_sql_templates' => $missingSql,
    'orphan_sql_templates' => $orphanSql,
    'ok' => empty($missingSql)
];

echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;

exit(empty($missingSql) ? 0 : 1);
