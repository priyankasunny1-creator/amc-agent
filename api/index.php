<?php
header('Content-Type: application/json');

$traceId = bin2hex(random_bytes(8));

$pdo = require __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/core/ApiException.php';
require_once __DIR__ . '/core/ApiResponse.php';
require_once __DIR__ . '/core/OpenAIClient.php';
require_once __DIR__ . '/core/IntentClassifier.php';
require_once __DIR__ . '/core/IntentResolver.php';
require_once __DIR__ . '/core/IntentValidator.php';
require_once __DIR__ . '/core/SqlExecutor.php';
require_once __DIR__ . '/core/ResultNarrower.php';
require_once __DIR__ . '/core/ResponseIntelligence.php';
require_once __DIR__ . '/core/ResponseFormatter.php';
require_once __DIR__ . '/core/ConversationMemory.php';

/**
 * Generate a lightweight conversation identifier when caller does not provide one.
 */
function ensureConversationId(?string $conversationId): string
{
    $clean = trim((string)$conversationId);
    return $clean !== '' ? $clean : bin2hex(random_bytes(8));
}

/**
 * Extract minimal parameter hints from a follow-up message.
 */
function extractFollowupParameters(array $pendingClarification, string $message): array
{
    $params = $pendingClarification['parameters'] ?? [];
    $missing = $pendingClarification['missing_parameters'] ?? [];

    foreach ($missing as $param) {
        if ($param === 'client_name') {
            // Basic parser for names in replies like "for Acme" / "Acme Corp".
            $candidate = trim(preg_replace('/^(for|client|about)\s+/i', '', $message));
            if ($candidate !== '') {
                $params['client_name'] = $candidate;
            }
        }
    }

    return $params;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim((string)($input['message'] ?? ''));
$conversationId = ensureConversationId($input['conversation_id'] ?? null);
$memory = ConversationMemory::load($conversationId);

if ($message === '') {
    ApiResponse::error(
        'Please enter a query.',
        $traceId,
        $conversationId,
        400,
        'EMPTY_MESSAGE'
    );
    exit;
}

try {
    $existingParameters = [];

    // Clarification follow-up: resume unresolved intent context before new classification.
    if (!empty($memory['pending_clarification'])) {
        $pending = $memory['pending_clarification'];
        $existingParameters = extractFollowupParameters($pending, $message);

        $intentData = IntentResolver::resolve(
            [$pending['intent']],
            $message,
            $existingParameters
        );
    } else {
        $classified = IntentClassifier::classify($message);

        // Reuse prior intent for contextual follow-up phrasing.
        if (!empty($memory['last_intent']) && (
            str_contains(strtolower($message), 'what about') ||
            str_contains(strtolower($message), 'show only')
        )) {
            $message = $memory['last_intent'] . ' ' . $message;
        }

        $intentData = IntentResolver::resolve(
            $classified['candidates'],
            $message,
            []
        );
    }

    // Safety gate: never run SQL when confidence is low/medium or params are missing.
    if (!empty($intentData['needs_clarification'])) {
        ConversationMemory::rememberClarification($conversationId, [
            'intent' => $intentData['intent'],
            'parameters' => $intentData['parameters'] ?? [],
            'missing_parameters' => $intentData['missing_parameters'] ?? []
        ]);

        ApiResponse::success([
            'intent' => $intentData['intent'],
            'response' => [
                'summary' => ($intentData['clarification']['reason'] ?? 'Clarification required') . ' ' . ($intentData['clarification']['question'] ?? ''),
                'needs_clarification' => true,
                'confidence' => $intentData['confidence'] ?? 'low'
            ],
            'meta' => [
                'blocked_execution' => true,
                'confidence' => $intentData['confidence'] ?? 'low',
                'confidence_score' => $intentData['confidence_score'] ?? 0,
                'missing_parameters' => $intentData['missing_parameters'] ?? []
            ]
        ], $traceId, $conversationId, 200);
        exit;
    }

    IntentValidator::validate($intentData);

    $data = SqlExecutor::execute(
        $pdo,
        $intentData['intent'],
        $intentData['parameters'] ?? []
    );

    $data = ResultNarrower::narrow($data, $message);

    $intelligence = ResponseIntelligence::analyse(
        $intentData['intent'],
        $data,
        $message
    );

    ConversationMemory::clearClarification($conversationId);
    ConversationMemory::remember(
        $conversationId,
        $intentData['intent'],
        $intelligence['metrics'] ?? [],
        $intentData['parameters'] ?? []
    );

    $response = ResponseFormatter::format(
        $intentData['intent'],
        $data,
        $intelligence
    );

    if (!is_array($response) || trim((string)($response['summary'] ?? '')) === '') {
        $response = [
            'summary' => 'No results available for this query. Please check the live queue at https://pm.gmi-projects.com/amc-dashboard-oauth/public/asana_fetch_queue.php'
        ];
    }

    ApiResponse::success([
        'intent' => $intentData['intent'],
        'response' => $response,
        'meta' => [
            'confidence' => $intentData['confidence'] ?? 'high',
            'confidence_score' => $intentData['confidence_score'] ?? 1
        ]
    ], $traceId, $conversationId, 200);
    exit;
} catch (ApiException $e) {
    error_log("[{$traceId}] " . $e->getMessage());

    ApiResponse::error(
        $e->getMessage(),
        $traceId,
        $conversationId,
        $e->getHttpStatus(),
        'API_ERROR',
        $e->getDetails()
    );
    exit;
} catch (Throwable $e) {
    error_log("[{$traceId}] Unhandled: " . $e->getMessage());

    ApiResponse::error(
        'Unable to process this request with the current data.',
        $traceId,
        $conversationId,
        500,
        'INTERNAL_ERROR'
    );
    exit;
}
