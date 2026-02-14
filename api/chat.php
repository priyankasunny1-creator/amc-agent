<?php
header('Content-Type: application/json');

http_response_code(410);
echo json_encode([
    'ok' => false,
    'status' => 410,
    'message' => 'Legacy endpoint removed. Use /api/index.php for chat requests.'
]);
