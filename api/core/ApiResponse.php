<?php

/**
 * ApiResponse enforces one JSON envelope shape for all backend responses.
 */
class ApiResponse
{
    public static function success(array $data, string $traceId, ?string $conversationId = null, int $status = 200): void
    {
        http_response_code($status);

        echo json_encode([
            'ok' => true,
            'status' => $status,
            'trace_id' => $traceId,
            'conversation_id' => $conversationId,
            'data' => $data,
            'error' => null
        ]);
    }

    public static function error(
        string $message,
        string $traceId,
        ?string $conversationId = null,
        int $status = 400,
        string $code = 'REQUEST_ERROR',
        array $details = []
    ): void {
        http_response_code($status);

        echo json_encode([
            'ok' => false,
            'status' => $status,
            'trace_id' => $traceId,
            'conversation_id' => $conversationId,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details
            ]
        ]);
    }
}
?>