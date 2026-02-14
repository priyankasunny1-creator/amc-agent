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
require_once __DIR__ . '/core/PlaybookAdvisor.php';

function ensureConversationId(?string $conversationId): string
{
    $clean = trim((string)$conversationId);
    return $clean !== '' ? $clean : bin2hex(random_bytes(8));
}

function extractFollowupParameters(array $pendingClarification, string $message): array
{
    $params = $pendingClarification['parameters'] ?? [];
    $missing = $pendingClarification['missing_parameters'] ?? [];

    foreach ($missing as $param) {
        if ($param === 'client_name') {
            $candidate = trim(preg_replace('/^(for|client|about|for client|same for)\s+/i', '', $message));
            if ($candidate !== '') {
                $params['client_name'] = $candidate;
            }
        }
    }

    return $params;
}

function isFollowupMessage(string $message): bool
{
    $normalized = strtolower(trim($message));
    $patterns = [
        'what about',
        'show only',
        'how about',
        'same for',
        'and for',
        'also for',
        'only for',
        'now for'
    ];

    foreach ($patterns as $phrase) {
        if (str_contains($normalized, $phrase)) {
            return true;
        }
    }

    return false;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim((string)($input['message'] ?? ''));
$conversationId = ensureConversationId($input['conversation_id'] ?? null);
$memory = ConversationMemory::load($conversationId);

if ($message === '') {
    ApiResponse::error('Please enter a query.', $traceId, $conversationId, 400, 'EMPTY_MESSAGE');
    exit;
}

try {
    $playbook = PlaybookAdvisor::match($message);
    if ($playbook && PlaybookAdvisor::shouldHandle($message)) {
        $summary = $playbook['summary'];
        $checklist = $playbook['checklist'];

        ApiResponse::success([
            'intent' => 'playbook_' . $playbook['topic'],
            'response' => [
                'summary' => $summary,
                'title' => $playbook['title'],
                'details' => $checklist,
                'highlights' => $checklist,
                'needs_clarification' => false
            ],
            'meta' => [
                'confidence' => 'high',
                'confidence_score' => 1,
                'source' => 'amc_playbook',
                'playbook_topics' => PlaybookAdvisor::allTopics(),
                'match_score' => $playbook['match_score'] ?? 0
            ]
        ], $traceId, $conversationId, 200);
        exit;
    }

    $existingParameters = [];

    if (!empty($memory['pending_clarification'])) {
        $pending = $memory['pending_clarification'];
        $existingParameters = extractFollowupParameters($pending, $message);

        $intentData = IntentResolver::resolve([$pending['intent']], $message, $existingParameters);
    } else {
        $classified = IntentClassifier::classify($message);

        if (!empty($memory['last_intent']) && isFollowupMessage($message)) {
            $message = $memory['last_intent'] . ' ' . $message;
        }

        $intentData = IntentResolver::resolve($classified['candidates'], $message, []);
    }

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
                'confidence' => $intentData['confidence'] ?? 'low',
                'follow_up_question' => $intentData['clarification']['question'] ?? null
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

    $data = SqlExecutor::execute($pdo, $intentData['intent'], $intentData['parameters'] ?? []);
    $data = ResultNarrower::narrow($data, $message);

    $intelligence = ResponseIntelligence::analyse($intentData['intent'], $data, $message);

    ConversationMemory::clearClarification($conversationId);
    ConversationMemory::remember(
        $conversationId,
        $intentData['intent'],
        $intelligence['metrics'] ?? [],
        $intentData['parameters'] ?? []
    );

    $response = ResponseFormatter::format($intentData['intent'], $data);

    if (!is_array($response) || trim((string)($response['summary'] ?? '')) === '') {
        $response = [
            'summary' => 'I could not confidently summarize this result set. Try refining your prompt with client, timeframe, or task type.',
            'action' => 'Example: "show overdue WordPress tasks for Acme in last 30 days".'
        ];
    }

    $response['intelligence'] = $intelligence;

    ApiResponse::success([
        'intent' => $intentData['intent'],
        'response' => $response,
        'meta' => [
            'confidence' => $intentData['confidence'] ?? 'high',
            'confidence_score' => $intentData['confidence_score'] ?? 1,
            'row_count' => count($data),
            'filters_used' => $intentData['parameters'] ?? [],
            'result_scope' => count($data) === 0 ? 'empty' : 'rows_returned'
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
        'Unable to process this request. Please retry with more specific details (client, time range, or task type).',
        $traceId,
        $conversationId,
        500,
        'INTERNAL_ERROR'
    );
    exit;
}
