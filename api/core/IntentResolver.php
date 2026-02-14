<?php

class IntentResolver
{
    public static function resolve(array $candidates, string $message, array $existingParameters = []): array
    {
        $intentConfig = require __DIR__ . '/../config/intents.php';

        $msg = self::normalizeMessage($message);

        if (self::isWpClientListQuery($msg)) {
            return [
                'intent' => 'get_wp_active_clients_list',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.96,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isDrupalClientListQuery($msg)) {
            return [
                'intent' => 'get_drupal_active_clients_list',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.96,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isZeroHoursQuery($msg)) {
            return [
                'intent' => 'get_clients_with_zero_hours',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.96,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isAmcThresholdQuery($msg)) {
            return [
                'intent' => 'get_clients_above_amc_usage_threshold',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.96,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isUnassignedTasksListQuery($msg)) {
            return [
                'intent' => 'get_unassigned_open_tasks_list',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.96,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isClientTasksQuery($msg)) {
            return [
                'intent' => 'get_tasks_by_client',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.94,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isWeeklyHealthSummaryQuery($msg)) {
            return [
                'intent' => 'get_workload_health_summary',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.95,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isOverdueByAssigneeQuery($msg)) {
            return [
                'intent' => 'get_tasks_by_assignee',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.95,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isWpPendingTodayQuery($msg)) {
            return [
                'intent' => 'get_wp_tasks_summary',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.95,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        if (self::isActiveClientsQuery($msg)) {
            return [
                'intent' => 'get_active_clients_count',
                'parameters' => [],
                'confidence' => 'high',
                'confidence_score' => 0.95,
                'needs_clarification' => false,
                'clarification' => null,
                'missing_parameters' => []
            ];
        }

        $scores = [];

        foreach ($candidates as $rank => $intent) {
            if (!isset($intentConfig[$intent])) {
                continue;
            }

            $score = 0;
            $meta = $intentConfig[$intent];

            if (($meta['tier'] ?? '') === 'A') {
                $score += 3;
            }

            if (!empty($meta['parameters'])) {
                $score += 1;
            }

            $score += max(0, 3 - $rank);

            $descriptionWords = explode(' ', strtolower($meta['description'] ?? ''));
            foreach ($descriptionWords as $word) {
                if (strlen($word) > 4 && str_contains($msg, $word)) {
                    $score += 1;
                }
            }

            $scores[$intent] = $score;
        }

        if (empty($scores)) {
            throw new ApiException('Unable to determine intent for this request.', 422, ['phase' => 'intent_resolution']);
        }

        arsort($scores);
        $finalIntent = array_key_first($scores);
        $scoreValues = array_values($scores);
        $topScore = $scoreValues[0];
        $secondScore = $scoreValues[1] ?? 0;

        $confidence = 'high';
        if ($topScore <= 4) {
            $confidence = 'low';
        } elseif (($topScore - $secondScore) <= 1) {
            $confidence = 'medium';
        }

        $requiredParams = $intentConfig[$finalIntent]['parameters'] ?? [];
        $parameters = $existingParameters;
        $missing = [];

        foreach ($requiredParams as $param) {
            $value = trim((string)($parameters[$param] ?? ''));
            if ($value === '') {
                $missing[] = $param;
            }
        }

        if ($confidence === 'low' || $confidence === 'medium' || !empty($missing)) {
            $promptReason = $confidence === 'low'
                ? 'I am not confident I understood your request.'
                : 'I need one more detail before I can run this query.';

            return [
                'intent' => $finalIntent,
                'parameters' => $parameters,
                'confidence' => $confidence,
                'confidence_score' => round($topScore / max(1, $topScore + $secondScore), 2),
                'needs_clarification' => true,
                'clarification' => [
                    'reason' => $promptReason,
                    'question' => !empty($missing)
                        ? ('Please provide: ' . implode(', ', $missing) . '.')
                        : 'Can you clarify the exact metric or client scope you want?'
                ],
                'missing_parameters' => $missing
            ];
        }

        return [
            'intent' => $finalIntent,
            'parameters' => $parameters,
            'confidence' => $confidence,
            'confidence_score' => round($topScore / max(1, $topScore + $secondScore), 2),
            'needs_clarification' => false,
            'clarification' => null,
            'missing_parameters' => []
        ];
    }


    private static function isWpClientListQuery(string $msg): bool
    {
        return str_contains($msg, 'wordpress')
            && str_contains($msg, 'client')
            && (str_contains($msg, 'list') || str_contains($msg, 'which') || str_contains($msg, 'active'));
    }

    private static function isDrupalClientListQuery(string $msg): bool
    {
        return str_contains($msg, 'drupal')
            && str_contains($msg, 'client')
            && (str_contains($msg, 'list') || str_contains($msg, 'which') || str_contains($msg, 'active'));
    }

    private static function isAmcThresholdQuery(string $msg): bool
    {
        return str_contains($msg, 'amc')
            && (
                str_contains($msg, 'threshold') ||
                str_contains($msg, 'usage') ||
                str_contains($msg, 'above') ||
                str_contains($msg, 'over') ||
                str_contains($msg, 'more than')
            )
            && str_contains($msg, 'client');
    }

    private static function isZeroHoursQuery(string $msg): bool
    {
        return str_contains($msg, 'amc')
            && (
                str_contains($msg, 'zero') ||
                str_contains($msg, 'no ') ||
                str_contains($msg, 'exhaust')
            )
            && str_contains($msg, 'hour')
            && str_contains($msg, 'client');
    }

    private static function isUnassignedTasksListQuery(string $msg): bool
    {
        return str_contains($msg, 'unassigned')
            && str_contains($msg, 'task')
            && (str_contains($msg, 'list') || str_contains($msg, 'show'));
    }

    private static function isClientTasksQuery(string $msg): bool
    {
        return str_contains($msg, 'task')
            && (str_contains($msg, 'client') || str_contains($msg, 'for '));
    }

    private static function isWeeklyHealthSummaryQuery(string $msg): bool
    {
        return str_contains($msg, 'weekly')
            && str_contains($msg, 'amc')
            && str_contains($msg, 'health')
            && str_contains($msg, 'summary');
    }

    private static function isOverdueByAssigneeQuery(string $msg): bool
    {
        return str_contains($msg, 'overdue')
            && str_contains($msg, 'assignee');
    }

    private static function normalizeMessage(string $message): string
    {
        $msg = strtolower(trim($message));
        $msg = str_replace(['wp'], ['wordpress'], $msg);
        return preg_replace('/\s+/', ' ', $msg);
    }

    private static function isWpPendingTodayQuery(string $msg): bool
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
}
