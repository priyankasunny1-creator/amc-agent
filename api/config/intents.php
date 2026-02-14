<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OPERATIONS (OPS) — TASK & WORKLOAD INTELLIGENCE
    | Tier-A: Direct & Derived (Single-SQL, Executable)
    |--------------------------------------------------------------------------
    */

    'get_ongoing_tasks_summary' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Summary count of all ongoing (open) tasks',
        'parameters' => []
    ],

    'get_wp_tasks_summary' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Summary of ongoing WordPress-related tasks',
        'parameters' => []
    ],

    'get_overdue_tasks_summary' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Count of tasks that are overdue as of today',
        'parameters' => []
    ],

    'get_clients_by_task_load' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Clients ranked by number of open tasks',
        'parameters' => []
    ],

    'get_critical_overdue_clients' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Clients with tasks overdue by more than 7 days',
        'parameters' => []
    ],

    'get_tasks_completed_this_week' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Number of tasks completed in the last 7 days',
        'parameters' => []
    ],

    'get_average_task_tat' => [
        'domain' => 'OPS',
        'tier' => 'B',
        'description' => 'Average turnaround time for completed tasks',
        'parameters' => []
    ],

    'get_assignee_task_load' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Assignees with high number of open tasks',
        'parameters' => []
    ],

    'get_unassigned_tasks_count' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Count of open tasks that are unassigned',
        'parameters' => []
    ],

    'get_inactive_clients' => [
        'domain' => 'OPS',
        'tier' => 'B',
        'description' => 'Clients with no task activity in the last 30 days',
        'parameters' => []
    ],

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER SUCCESS (CS) — AMC & CLIENT HEALTH
    | Tier-A: Direct & Risk-Based
    |--------------------------------------------------------------------------
    */

    'get_clients_above_amc_usage_threshold' => [
    'domain' => 'CS',
    'tier' => 'A',
    'description' => 'Clients whose AMC usage exceeds a defined threshold',
    'parameters' => []
        ],


    'get_clients_at_risk' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'Clients at risk due to AMC expiry or low remaining hours',
        'parameters' => []
    ],

    'get_clients_with_zero_hours' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'Clients who have exhausted all AMC hours',
        'parameters' => []
    ],

    'get_client_amc_usage' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'AMC usage and remaining hours for a specific client',
        'parameters' => ['client_name']
    ],

    'get_clients_needing_renewal' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'Clients whose AMC contracts are expiring soon',
        'parameters' => []
    ],

    'get_high_consumption_clients' => [
        'domain' => 'CS',
        'tier' => 'B',
        'description' => 'Clients consuming AMC hours at a high rate',
        'parameters' => []
    ],

    'get_clients_with_recommendations' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'Clients with active or pending recommendations',
        'parameters' => []
    ],

    'get_open_issues_by_client' => [
        'domain' => 'CS',
        'tier' => 'B',
        'description' => 'Count of open issue-type tasks per client',
        'parameters' => []
    ],

    'get_low_risk_clients' => [
        'domain' => 'CS',
        'tier' => 'B',
        'description' => 'Stable clients with low risk and sufficient AMC hours',
        'parameters' => []
    ],

    /*
    |--------------------------------------------------------------------------
    | DEVELOPMENT / DELIVERY — SERVICES & MAINTENANCE
    | Tier-A: Service & Platform Intelligence
    |--------------------------------------------------------------------------
    */

    'get_tasks_by_tech_stack' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Distribution of open tasks by technology stack (WP, .NET, etc.)',
        'parameters' => []
    ],

    'get_overdue_services' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Client services that are overdue',
        'parameters' => []
    ],

    'get_upcoming_maintenance' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Maintenance or scheduled services due soon',
        'parameters' => []
    ],

    'get_pending_updates' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Pending plugin, CMS, or platform updates',
        'parameters' => []
    ],

    'get_repeat_issue_clients' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Clients with recurring or repeated issues',
        'parameters' => []
    ],

    'get_deployment_backlog' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Open deployment-related tasks',
        'parameters' => []
    ],

    'get_blocked_tasks' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Tasks that are blocked or on hold',
        'parameters' => []
    ],

    /*
    |--------------------------------------------------------------------------
    | MANAGEMENT — CAPACITY, RISK & TREND INTELLIGENCE
    | Tier-B: Aggregated & Executive-Level
    |--------------------------------------------------------------------------
    */

    'get_workload_health_summary' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Overall snapshot of workload health',
        'parameters' => []
    ],

    'get_amc_revenue_risk' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Revenue risk due to AMC expiry or exhaustion',
        'parameters' => []
    ],

    'get_clients_needing_escalation' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Clients requiring immediate escalation',
        'parameters' => []
    ],

    'get_task_growth_trend' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Month-over-month growth in task volume',
        'parameters' => []
    ],

    'get_capacity_demand_summary' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Comparison of team capacity versus task demand',
        'parameters' => []
    ]

,

    'get_client_overview' => [
        'domain' => 'CS',
        'tier' => 'B',
        'description' => 'Overview of AMC contract and activity for all clients',
        'parameters' => []
    ],

    'get_client_expiry' => [
        'domain' => 'CS',
        'tier' => 'A',
        'description' => 'Clients and AMC contract expiry dates',
        'parameters' => []
    ],

    'get_tasks_by_client' => [
        'domain' => 'OPS',
        'tier' => 'B',
        'description' => 'Open and completed tasks grouped by client',
        'parameters' => []
    ],

    'get_overdue_tasks' => [
        'domain' => 'OPS',
        'tier' => 'A',
        'description' => 'Detailed list of overdue open tasks',
        'parameters' => []
    ],

    'get_tasks_by_assignee' => [
        'domain' => 'OPS',
        'tier' => 'B',
        'description' => 'Detailed list of open tasks grouped by assignee',
        'parameters' => []
    ],

    'get_open_incidents' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Open incidents requiring action',
        'parameters' => []
    ],

    'get_incidents_by_client' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Incident listing by client',
        'parameters' => []
    ],

    'get_service_due_date' => [
        'domain' => 'DEV',
        'tier' => 'A',
        'description' => 'Upcoming service schedule and due dates',
        'parameters' => []
    ],

    'get_report_status' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Status of client reporting syncs and generation',
        'parameters' => []
    ],

    'get_open_workload_summary' => [
        'domain' => 'MGMT',
        'tier' => 'B',
        'description' => 'Open workload summary across clients',
        'parameters' => []
    ],

    'get_drupal_tasks_summary' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Summary of ongoing Drupal-related tasks',
        'parameters' => []
    ],

    'get_laravel_tasks_summary' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Summary of ongoing Laravel-related tasks',
        'parameters' => []
    ],

    'get_umbraco_tasks_summary' => [
        'domain' => 'DEV',
        'tier' => 'B',
        'description' => 'Summary of ongoing Umbraco-related tasks',
        'parameters' => []
    ]


];
