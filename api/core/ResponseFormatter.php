<?php


class ResponseFormatter
{
    public static function format(string $intent, array $data): array
    {
        switch ($intent) {

            /* ============================
             * OPS – TASK & WORKLOAD
             * ============================
             */

            case 'get_ongoing_tasks_summary':
                return self::ongoingTasks($data);

            case 'get_wp_tasks_summary':
                return self::wpTasks($data);

            case 'get_overdue_tasks_summary':
                return self::overdueTasks($data);

            case 'get_clients_by_task_load':
                return self::clientsByTaskLoad($data);

            case 'get_critical_overdue_clients':
                return self::criticalOverdueClients($data);

            case 'get_tasks_completed_this_week':
                return self::tasksCompletedThisWeek($data);

            case 'get_average_task_tat':
                return self::averageTaskTAT($data);

            case 'get_assignee_task_load':
                return self::assigneeTaskLoad($data);

            case 'get_unassigned_tasks_count':
                return self::unassignedTasks($data);

            case 'get_unassigned_open_tasks_list':
                return self::unassignedOpenTasksList($data);

            case 'get_inactive_clients':
                return self::inactiveClients($data);

            case 'get_active_clients_count':
                return self::activeClientsCount($data);

            case 'get_tasks_by_client':
                return self::tasksByClient($data);

            case 'get_overdue_tasks':
                return self::overdueTaskList($data);

            case 'get_tasks_by_assignee':
                return self::tasksByAssignee($data);

            case 'get_workload_health_summary':
                return self::workloadHealthSummary($data);

            case 'get_report_status':
                return self::reportStatus($data);

            /* ============================
            * CS – CLIENT SUCCESS
            * ============================
            */

            case 'get_clients_at_risk':
                return self::clientsAtRisk($data);

            case 'get_clients_with_zero_hours':
                return self::clientsWithZeroHours($data);

            case 'get_client_amc_usage':
                return self::clientAmcUsage($data);

            case 'get_clients_needing_renewal':
                return self::clientsNeedingRenewal($data);

            case 'get_high_consumption_clients':
                return self::highConsumptionClients($data);

            case 'get_clients_with_recommendations':
                return self::clientsWithRecommendations($data);

            case 'get_open_issues_by_client':
                return self::openIssuesByClient($data);

            case 'get_low_risk_clients':
                return self::lowRiskClients($data);

            case 'get_clients_above_amc_usage_threshold':
                return self::clientsAboveAmcThreshold($data);



            default:
                return [
                    'summary' => 'Request processed successfully.',
                    'severity' => 'info',
                    'data' => $data
                ];
        }
    }


    /* ============================
    * CS FORMATTERS
    * ============================
    */

    private static function clientsAboveAmcThreshold(array $data): array
    {
        $count = count($data);

        if ($count === 0) {
            return [
                'summary'  => 'No clients have crossed the AMC usage threshold.',
                'severity' => 'low',
                'data'     => []
            ];
        }

        return [
            'summary'  => "$count client(s) have consumed more than 50% of their AMC hours.",
            'severity' => $count >= 10 ? 'high' : 'medium',
            'action'   => 'Prioritize renewals and monitor scope creep.',
            'highlights' => array_map(
                fn($r) => "{$r['client_name']} ({$r['usage_percentage']}%)",
                array_slice($data, 0, 5)
            ),
            'data' => $data
        ];
    }


    private static function clientsAtRisk(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients are currently at risk.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $highlights = array_map(fn($r) =>
            "{$r['client_name']} — expires in {$r['days_to_expiry']} days, {$r['remaining_hours']} AMC hours left",
            $data
        );

        return [
            'summary' => count($data) . ' client(s) are at risk due to upcoming AMC expiry or low remaining hours.',
            'severity' => 'high',
            'action' => 'Initiate renewal or scope discussions immediately.',
            'highlights' => $highlights,
            'data' => $data
        ];
    }

    private static function clientsWithZeroHours(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients have exhausted their AMC hours.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => count($data) . ' client(s) have exhausted their AMC hours.',
            'severity' => 'critical',
            'action' => 'Block further work or raise immediate renewal approvals.',
            'highlights' => array_column($data, 'client_name'),
            'data' => $data
        ];
    }

    private static function clientAmcUsage(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No AMC usage data found for the specified client.',
                'severity' => 'info',
                'data' => []
            ];
        }

        $row = $data[0];

        return [
            'summary' =>
                "{$row['client_name']} has used {$row['consumed_hours']} hours. "
                . "{$row['remaining_hours']} AMC hours remain.",
            'severity' => $row['remaining_hours'] <= 10 ? 'high' : 'low',
            'action' => 'Monitor usage and plan renewals proactively.',
            'data' => $data
        ];
    }

    private static function clientsNeedingRenewal(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients require renewal discussions at this time.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => count($data) . ' client(s) need AMC renewal discussions this month.',
            'severity' => 'medium',
            'action' => 'Schedule renewal calls and prepare proposals.',
            'highlights' => array_map(
                fn($r) => "{$r['client_name']} — expires on {$r['contract_expiry']}",
                $data
            ),
            'data' => $data
        ];
    }

    private static function highConsumptionClients(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients show unusually high AMC consumption.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => 'High AMC consumption detected for select clients.',
            'severity' => 'medium',
            'action' => 'Review scope creep and adjust engagement models.',
            'highlights' => array_column($data, 'client_name'),
            'data' => $data
        ];
    }

    private static function clientsWithRecommendations(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No active recommendations for any clients.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => count($data) . ' client(s) have pending or active recommendations.',
            'severity' => 'info',
            'action' => 'Follow up to convert recommendations into upsell opportunities.',
            'highlights' => array_column($data, 'client_name'),
            'data' => $data
        ];
    }

    private static function openIssuesByClient(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No open issues reported across clients.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => 'Open support issues detected across multiple clients.',
            'severity' => 'medium',
            'action' => 'Ensure SLA compliance and proactive communication.',
            'data' => $data
        ];
    }

    private static function lowRiskClients(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients currently qualify as low-risk.',
                'severity' => 'info',
                'data' => []
            ];
        }

        return [
            'summary' => count($data) . ' client(s) are stable and low-risk.',
            'severity' => 'low',
            'action' => 'Maintain engagement and explore value-add opportunities.',
            'highlights' => array_column($data, 'client_name'),
            'data' => $data
        ];
    }


    /* ============================
     * OPS FORMATTERS
     * ============================
     */

    private static function ongoingTasks(array $data): array
    {
        $count = (int)($data[0]['ongoing_tasks'] ?? 0);

        return [
            'summary' => "$count task(s) are currently in progress.",
            'severity' => $count > 50 ? 'high' : ($count > 20 ? 'medium' : 'low'),
            'action' => 'Monitor workload distribution and ensure timely closures.',
            'data' => $data
        ];
    }

    private static function wpTasks(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No active WordPress-related tasks found.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $row = $data[0];

        return [
            'summary' =>
                "{$row['ongoing_tasks']} WordPress-related task(s) are active "
                . "across {$row['affected_clients']} client(s).",
            'severity' => $row['ongoing_tasks'] > 15 ? 'medium' : 'low',
            'action' => 'Review WP task prioritisation and delivery timelines.',
            'data' => $data
        ];
    }

    private static function overdueTasks(array $data): array
    {
        $count = (int)($data[0]['overdue_tasks'] ?? 0);

        return [
            'summary' => "$count task(s) are currently overdue.",
            'severity' => $count > 10 ? 'high' : ($count > 0 ? 'medium' : 'low'),
            'action' => $count > 0
                ? 'Identify blockers and expedite overdue tasks.'
                : 'No immediate action required.',
            'data' => $data
        ];
    }

    private static function clientsByTaskLoad(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No open tasks found for any client.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $top = $data[0]['client_name'] ?? 'N/A';

        return [
            'summary' => "Clients are currently unevenly loaded. $top has the highest task volume.",
            'severity' => 'medium',
            'action' => 'Consider redistributing workload or reviewing scope with high-load clients.',
            'data' => $data
        ];
    }

    private static function criticalOverdueClients(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No clients have tasks overdue beyond 7 days.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $highlights = [];

        foreach ($data as $row) {
            $highlights[] =
                "{$row['client_name']} — {$row['overdue_tasks']} overdue task(s), "
                . "max {$row['max_days_overdue']} days overdue";
        }

        return [
            'summary' =>
                count($data) . ' client(s) have critically overdue tasks.',
            'severity' => 'critical',
            'action' => 'Immediate escalation and corrective action required.',
            'highlights' => $highlights,
            'data' => $data
        ];
    }

    private static function tasksCompletedThisWeek(array $data): array
    {
        $count = (int)($data[0]['completed_tasks'] ?? 0);

        return [
            'summary' => "$count task(s) were completed in the last 7 days.",
            'severity' => 'info',
            'action' => 'Review delivery velocity and compare with previous weeks.',
            'data' => $data
        ];
    }

    private static function averageTaskTAT(array $data): array
    {
        $hours = $data[0]['avg_turnaround_hours'] ?? null;

        return [
            'summary' =>
                $hours !== null
                    ? "Average task turnaround time is {$hours} hours."
                    : 'No completed task data available to calculate turnaround time.',
            'severity' => $hours > 72 ? 'medium' : 'low',
            'action' => 'Identify bottlenecks if turnaround exceeds expectations.',
            'data' => $data
        ];
    }

    private static function assigneeTaskLoad(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'No assignees are currently overloaded.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $highlights = [];

        foreach ($data as $row) {
            $highlights[] =
                "{$row['assignee_name']} — {$row['open_tasks']} open task(s)";
        }

        return [
            'summary' =>
                count($data) . ' assignee(s) have high task load.',
            'severity' => 'medium',
            'action' => 'Consider rebalancing assignments to avoid burnout.',
            'highlights' => $highlights,
            'data' => $data
        ];
    }

    private static function unassignedTasks(array $data): array
    {
        $count = (int)($data[0]['unassigned_tasks'] ?? 0);

        return [
            'summary' => "$count open task(s) are currently unassigned.",
            'severity' => $count > 5 ? 'medium' : 'low',
            'action' =>
                $count > 0
                    ? 'Assign ownership to avoid delays.'
                    : 'All tasks are properly assigned.',
            'data' => $data
        ];
    }



    private static function activeClientsCount(array $data): array
    {
        $count = (int)($data[0]['active_clients'] ?? 0);

        return [
            'summary' => "{$count} client(s) currently have active open tasks.",
            'severity' => $count > 30 ? 'medium' : 'low',
            'action' => 'Use this as active workload scope for CS and PM planning.',
            'data' => $data
        ];
    }



    private static function tasksByClient(array $data): array
    {
        $count = count($data);
        return [
            'summary' => $count === 0
                ? 'No client tasks were found for the current scope.'
                : "$count task record(s) were found across clients.",
            'severity' => $count === 0 ? 'low' : 'info',
            'data' => $data
        ];
    }

    private static function overdueTaskList(array $data): array
    {
        $count = count($data);
        return [
            'summary' => $count === 0
                ? 'No overdue open tasks were found.'
                : "$count overdue task(s) require attention.",
            'severity' => $count >= 20 ? 'high' : ($count > 0 ? 'medium' : 'low'),
            'action' => $count > 0 ? 'Prioritize overdue tasks by owner and client impact.' : null,
            'data' => $data
        ];
    }

    private static function tasksByAssignee(array $data): array
    {
        $count = count($data);
        return [
            'summary' => $count === 0
                ? 'No open tasks by assignee were found.'
                : "$count open task record(s) were found by assignee.",
            'severity' => $count === 0 ? 'low' : 'info',
            'data' => $data
        ];
    }

    private static function workloadHealthSummary(array $data): array
    {
        $row = $data[0] ?? [];
        if (empty($row)) {
            return [
                'summary' => 'Workload health data is currently unavailable.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $open = (int)($row['open_tasks'] ?? 0);
        $overdue = (int)($row['overdue_tasks'] ?? 0);
        $unassigned = (int)($row['unassigned_tasks'] ?? 0);

        return [
            'summary' => "Weekly AMC health: $open open tasks, $overdue overdue, $unassigned unassigned.",
            'severity' => $overdue > 0 || $unassigned > 0 ? 'high' : 'info',
            'action' => 'Review overdue and unassigned queues in weekly planning.',
            'data' => $data
        ];
    }

    private static function reportStatus(array $data): array
    {
        $count = count($data);
        return [
            'summary' => $count === 0
                ? 'No client reporting status records were found.'
                : "$count reporting status record(s) were found.",
            'severity' => $count === 0 ? 'low' : 'info',
            'data' => $data
        ];
    }

    private static function unassignedOpenTasksList(array $data): array
    {
        $count = count($data);

        if ($count === 0) {
            return [
                'summary' => 'No unassigned open tasks were found.',
                'severity' => 'low',
                'data' => []
            ];
        }

        return [
            'summary' => "$count unassigned open task(s) are currently pending ownership.",
            'severity' => $count >= 20 ? 'high' : 'medium',
            'action' => 'Assign owners to avoid SLA slippage.',
            'highlights' => array_map(
                fn($r) => "{$r['client_name']} — {$r['task_title']}",
                array_slice($data, 0, 5)
            ),
            'data' => $data
        ];
    }

    private static function inactiveClients(array $data): array
    {
        if (empty($data)) {
            return [
                'summary' => 'All clients have recent task activity.',
                'severity' => 'low',
                'data' => []
            ];
        }

        $highlights = [];

        foreach ($data as $row) {
            $highlights[] =
                "{$row['client_name']} — last activity on {$row['last_task_date']}";
        }

        return [
            'summary' =>
                count($data) . ' client(s) show no activity in the last 30 days.',
            'severity' => 'medium',
            'action' => 'Review engagement level and follow up with inactive clients.',
            'highlights' => $highlights,
            'data' => $data
        ];
    }
}
