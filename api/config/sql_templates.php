<?php
/**
 * SQL TEMPLATE REGISTRY (FINAL & LOCKED)
 * -------------------------------------
 * Rules:
 * - One intent → one SQL
 * - NO placeholders
 * - NO dynamic SQL
 * - CS / Incidents / Maintenance / Reporting → ai_* views ONLY
 * - OPS / Workload → base tables allowed
 * - Client / assignee filtering happens in PHP
 */

return [

    /*
    |--------------------------------------------------------------------------
    | CLIENT INTELLIGENCE
    |--------------------------------------------------------------------------
    */

    'get_client_overview' => "
        SELECT
            client_name,
            contract_status,
            contract_expiry_date,
            total_amc_hours,
            consumed_hours,
            remaining_hours,
            is_expiring_soon,
            last_activity_at
        FROM ai_clients_view
    ",

    'get_client_expiry' => "
        SELECT
            client_name,
            contract_expiry_date,
            days_to_expiry,
            remaining_hours
        FROM ai_expiring_clients_view
        ORDER BY days_to_expiry ASC
    ",

    'get_client_amc_usage' => "
        SELECT
            client_id,
            client_name,
            consumed_hours,
            remaining_hours,
            total_efforts_hours,
            usage_percentage
        FROM ai_client_amc_usage_view
        ORDER BY usage_percentage DESC
    ",

    /*
    |--------------------------------------------------------------------------
    | WORK / TASK INTELLIGENCE
    |--------------------------------------------------------------------------
    */

    'get_tasks_by_client' => "
        SELECT
            client_name,
            task_title,
            due_date,
            status_label,
            assignee_name,
            is_completed
        FROM ai_work_items_view
        ORDER BY
            is_completed ASC,
            due_date ASC
    ",

    'get_overdue_tasks' => "
        SELECT
            client_name,
            task_title,
            due_date,
            assignee_name
        FROM ai_work_items_view
        WHERE
            is_completed = 0
            AND due_date IS NOT NULL
            AND due_date < CURDATE()
        ORDER BY due_date ASC
    ",

    'get_tasks_by_assignee' => "
        SELECT
            client_name,
            task_title,
            due_date,
            status_label
        FROM ai_work_items_view
        WHERE is_completed = 0
        ORDER BY due_date ASC
    ",

    /*
    |--------------------------------------------------------------------------
    | INCIDENTS
    |--------------------------------------------------------------------------
    */

    'get_open_incidents' => "
        SELECT
            client_name,
            incident_title,
            incident_status,
            due_date,
            reported_at
        FROM ai_incidents_view
        WHERE is_resolved = 0
        ORDER BY reported_at DESC
    ",

    'get_incidents_by_client' => "
        SELECT
            client_name,
            incident_title,
            incident_status,
            due_date,
            is_resolved,
            reported_at
        FROM ai_incidents_view
        ORDER BY reported_at DESC
    ",

    /*
    |--------------------------------------------------------------------------
    | SERVICES / MAINTENANCE
    |--------------------------------------------------------------------------
    */

    'get_service_due_date' => "
        SELECT
            client_name,
            service_title,
            next_due_date,
            service_status
        FROM ai_service_schedule_view
        ORDER BY next_due_date ASC
    ",

    /*
    |--------------------------------------------------------------------------
    | REPORTING
    |--------------------------------------------------------------------------
    */

    'get_report_status' => "
        SELECT
            client_name,
            report_frequency,
            last_sync_status,
            last_report_generated_at
        FROM ai_reports_view
    ",

    /*
    |--------------------------------------------------------------------------
    | WORKLOAD / MANAGEMENT OVERVIEW
    |--------------------------------------------------------------------------
    */

    'get_open_workload_summary' => "
        SELECT
            client_name,
            open_task_count,
            total_pending_effort_hours,
            avg_days_to_due_date
        FROM ai_open_tasks_summary_view
        ORDER BY open_task_count DESC
    ",

    'get_clients_at_risk' => "
        SELECT
            client_id,
            client_name,
            days_to_expiry,
            remaining_hours
        FROM ai_clients_at_risk_view
        ORDER BY days_to_expiry ASC
    ",

    'get_high_consumption_clients' => "
        SELECT
            client_id,
            client_name,
            usage_percentage,
            remaining_hours
        FROM ai_high_consumption_clients_view
        ORDER BY usage_percentage DESC
    ",

    /*
    |--------------------------------------------------------------------------
    | OPERATIONS (OPS) — BASE TABLES
    |--------------------------------------------------------------------------
    */

    'get_ongoing_tasks_summary' => "
        SELECT
            COUNT(*) AS ongoing_tasks
        FROM tasks_main
        WHERE completed = 0
    ",

    'get_wp_tasks_summary' => "
        SELECT
            COUNT(t.id) AS ongoing_tasks,
            COUNT(DISTINCT c.id) AS affected_clients
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%WP%'
    ",

    'get_drupal_tasks_summary' => "
        SELECT
            COUNT(t.id) AS ongoing_tasks,
            COUNT(DISTINCT c.id) AS affected_clients
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%Drupal%'
    ",

    'get_laravel_tasks_summary' => "
        SELECT
            COUNT(t.id) AS ongoing_tasks,
            COUNT(DISTINCT c.id) AS affected_clients
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%Laravel%'
    ",

    'get_umbraco_tasks_summary' => "
        SELECT
            COUNT(t.id) AS ongoing_tasks,
            COUNT(DISTINCT c.id) AS affected_clients
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%Umbraco%'
    ",

    'get_overdue_tasks_summary' => "
        SELECT
            COUNT(*) AS overdue_tasks
        FROM tasks_main
        WHERE
            completed = 0
            AND due_on IS NOT NULL
            AND due_on < CURDATE()
    ",

    'get_critical_overdue_clients' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS overdue_tasks,
            MAX(DATEDIFF(CURDATE(), t.due_on)) AS max_days_overdue
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND t.due_on < DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY c.id
        ORDER BY max_days_overdue DESC
    ",

    'get_tasks_completed_this_week' => "
        SELECT
            COUNT(*) AS completed_tasks
        FROM tasks_main
        WHERE
            completed = 1
            AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ",

    'get_average_task_tat' => "
        SELECT
            ROUND(
                AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)),
                2
            ) AS avg_turnaround_hours
        FROM tasks_main
        WHERE
            completed = 1
            AND completed_at IS NOT NULL
    ",

    'get_assignee_task_load' => "
        SELECT
            assignee_name,
            COUNT(*) AS open_tasks
        FROM tasks_main
        WHERE
            completed = 0
            AND assignee_name IS NOT NULL
        GROUP BY assignee_name
        HAVING open_tasks >= 10
        ORDER BY open_tasks DESC
    ",

    'get_unassigned_tasks_count' => "
        SELECT
            COUNT(*) AS unassigned_tasks
        FROM tasks_main
        WHERE
            completed = 0
            AND (assignee_name IS NULL OR assignee_name = '')
    ",

    'get_inactive_clients' => "
        SELECT
            c.name AS client_name,
            MAX(t.created_at) AS last_task_date
        FROM clients c
        LEFT JOIN tasks_main t ON t.client_id = c.id
        GROUP BY c.id
        HAVING
            last_task_date IS NULL
            OR last_task_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ORDER BY last_task_date ASC
    ",

    'get_clients_above_amc_usage_threshold' => "
    SELECT
        client_name,
        usage_percentage,
        remaining_hours
    FROM ai_client_amc_usage_view
    WHERE usage_percentage > 50
    ORDER BY usage_percentage DESC
    ",


    'get_clients_by_task_load' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS open_tasks
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
        GROUP BY c.id
        ORDER BY open_tasks DESC
        LIMIT 10
    "

];
