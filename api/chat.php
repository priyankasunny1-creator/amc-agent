<?php
header('Content-Type: application/json');

$pdo = require __DIR__ . '/bootstrap.php';

require __DIR__ . '/core/IntentRegistry.php';
require __DIR__ . '/core/SqlExecutor.php';

/*
 TEMPORARY: hardcoded intent to test pipeline
 This will be replaced by OpenAI in next step
*/
$intent = 'get_open_workload_summary';
$params = [];

try {
    IntentRegistry::validate($intent, $params);
    $data = SqlExecutor::run($pdo, $intent, $params);

    echo json_encode([
        'status' => 'ok',
        'intent' => $intent,
        'data' => $data
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
