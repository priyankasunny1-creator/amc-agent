<?php
require_once __DIR__ . '/IntelligenceSnapshot.php';

class ResponseIntelligence
{
    private static array $thresholds;

    private static function thresholds(): array
    {
        if (!isset(self::$thresholds)) {
            self::$thresholds = require __DIR__ . '/../config/thresholds.php';
        }
        return self::$thresholds;
    }

    public static function analyse(
        string $intent,
        array $data,
        string $message = ''
    ): array {

        if (empty($data)) {
            return [
                'severity' => 'low',
                'signal'   => 'No relevant data found.',
                'action'   => 'No action required.',
                'insight'  => null,
                'metrics'  => []
            ];
        }

        return match ($intent) {

            /* =====================================================
             * CS — AMC INTELLIGENCE
             * ===================================================== */

            'get_client_amc_usage' =>
                self::singleClientAmc($data),

            'get_clients_above_amc_usage_threshold' =>
                self::aggregateAmcOveruse($data),

            'get_clients_at_risk' =>
                self::clientsAtRisk($data),

            /* =====================================================
             * OPS — WORKLOAD
             * ===================================================== */

            'get_ongoing_tasks_summary' =>
                self::ongoingTasks($data),

            'get_assignee_task_load' =>
                self::assigneeLoad($data),

            'get_unassigned_tasks_count' =>
                self::unassignedTasks($data),

            default =>
                self::generic($data)
        };
    }

    /* =====================================================
     * AMC — SINGLE CLIENT
     * ===================================================== */

    private static function singleClientAmc(array $data): array
    {
        $t = self::thresholds()['amc']['usage'];
        $row = $data[0];

        $usage = (float) $row['usage_percentage'];
        $remaining = (float) $row['remaining_hours'];

        $severity =
            $usage >= $t['critical'] ? 'critical' :
            ($usage >= $t['warning'] ? 'high' : 'low');

        return [
            'severity' => $severity,
            'signal'   => "{$row['client_name']} has used {$usage}% of AMC hours.",
            'action'   =>
                $remaining <= 0
                    ? 'Immediate renewal or work stoppage decision required.'
                    : ($severity === 'high'
                        ? 'Plan renewal proactively.'
                        : 'Monitor usage periodically.'),
            'insight'  => "Remaining hours: {$remaining}",
            'metrics'  => [
                'usage_percentage' => $usage,
                'remaining_hours'  => $remaining
            ]
        ];
    }

    /* =====================================================
     * AMC — AGGREGATE OVERUSE
     * ===================================================== */

    private static function aggregateAmcOveruse(array $data): array
    {
        $t = self::thresholds()['amc']['aggregate'];
        $count = count($data);

        $previous = IntelligenceSnapshot::load('get_clients_above_amc_usage_threshold');
        $delta = $previous ? $count - ($previous['count'] ?? 0) : 0;

        $trend =
            $delta > 0 ? 'increasing' :
            ($delta < 0 ? 'decreasing' : 'stable');

        IntelligenceSnapshot::save(
            'get_clients_above_amc_usage_threshold',
            ['count' => $count]
        );

        $severity =
            $count >= $t['critical'] ? 'critical' :
            ($count >= $t['high'] ? 'high' :
            ($count >= $t['medium'] ? 'medium' : 'low'));

        return [
            'severity' => $severity,
            'signal' =>
                "{$count} client(s) have exceeded safe AMC usage levels.",

            'action' =>
                $trend === 'increasing'
                    ? 'Escalate renewals — risk is increasing.'
                    : 'Monitor and continue proactive engagement.',

            'insight' =>
                $previous
                    ? "Trend is {$trend} ({$delta} change since last check)."
                    : 'Baseline established for future trend analysis.',

            'metrics' => [
                'affected_clients' => $count,
                'delta' => $delta,
                'trend' => $trend
            ]
        ];
    }




    /* =====================================================
     * CS — CLIENTS AT RISK
     * ===================================================== */

    private static function clientsAtRisk(array $data): array
    {
        $count = count($data);

        return [
            'severity' => $count >= 10 ? 'critical' : 'high',
            'signal'   => "{$count} client(s) are at risk due to AMC expiry or exhaustion.",
            'action'   => 'Immediate renewal prioritization required.',
            'insight'  => 'Potential revenue leakage detected.',
            'metrics'  => [
                'at_risk_clients' => $count
            ]
        ];
    }

    /* =====================================================
     * OPS — TASK LOAD
     * ===================================================== */

    private static function ongoingTasks(array $data): array
    {
        $t = self::thresholds()['tasks']['ongoing'];
        $count = (int) $data[0]['ongoing_tasks'];

        $severity =
            $count >= $t['critical'] ? 'critical' :
            ($count >= $t['high'] ? 'high' :
            ($count >= $t['medium'] ? 'medium' : 'low'));

        return [
            'severity' => $severity,
            'signal'   => "{$count} tasks are currently in progress.",
            'action'   => 'Review workload distribution and delivery capacity.',
            'insight'  => $severity === 'critical'
                ? 'Sustained overload risk detected.'
                : null,
            'metrics'  => [
                'ongoing_tasks' => $count
            ]
        ];
    }

    private static function assigneeLoad(array $data): array
    {
        $limit = self::thresholds()['tasks']['assignee_load']['high'];
        $count = count($data);

        return [
            'severity' => $count > 0 ? 'high' : 'low',
            'signal'   => "{$count} assignee(s) exceed {$limit} open tasks.",
            'action'   => 'Redistribute workload to prevent burnout.',
            'insight'  => 'Delivery velocity may degrade if overload persists.',
            'metrics'  => [
                'overloaded_assignees' => $count
            ]
        ];
    }

    private static function unassignedTasks(array $data): array
    {
        $t = self::thresholds()['tasks']['unassigned'];
        $count = (int) $data[0]['unassigned_tasks'];

        $severity =
            $count >= $t['high'] ? 'high' :
            ($count >= $t['medium'] ? 'medium' : 'low');

        return [
            'severity' => $severity,
            'signal'   => "{$count} task(s) are unassigned.",
            'action'   => 'Assign ownership to avoid delivery delays.',
            'insight'  => null,
            'metrics'  => [
                'unassigned_tasks' => $count
            ]
        ];
    }

    /* =====================================================
     * FALLBACK
     * ===================================================== */

    private static function generic(array $data): array
    {
        return [
            'severity' => 'info',
            'signal'   => 'Data retrieved successfully.',
            'action'   => 'Review details as required.',
            'insight'  => null,
            'metrics'  => []
        ];
    }
}
