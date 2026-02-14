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

    'get_wp_active_clients_list' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS open_tasks
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%WP%'
        GROUP BY c.id
        ORDER BY open_tasks DESC
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

    'get_drupal_active_clients_list' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS open_tasks
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE
            t.completed = 0
            AND c.name LIKE '%Drupal%'
        GROUP BY c.id
        ORDER BY open_tasks DESC
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

    'get_active_clients_count' => "
        SELECT
            COUNT(DISTINCT t.client_id) AS active_clients
        FROM tasks_main t
        WHERE t.completed = 0
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

,

    'get_clients_with_zero_hours' => "
        SELECT
            client_id,
            client_name,
            consumed_hours,
            remaining_hours,
            usage_percentage
        FROM ai_client_amc_usage_view
        WHERE remaining_hours <= 0
        ORDER BY usage_percentage DESC
    ",

    'get_clients_needing_renewal' => "
        SELECT
            client_id,
            client_name,
            days_to_expiry,
            remaining_hours,
            DATE_ADD(CURDATE(), INTERVAL days_to_expiry DAY) AS contract_expiry
        FROM ai_clients_at_risk_view
        WHERE days_to_expiry <= 30
        ORDER BY days_to_expiry ASC
    ",

    'get_clients_with_recommendations' => "
        SELECT
            client_name,
            usage_percentage,
            remaining_hours,
            CASE
                WHEN remaining_hours <= 0 THEN 'Immediate renewal required'
                WHEN usage_percentage >= 80 THEN 'Proactive renewal discussion'
                ELSE 'Monitor monthly'
            END AS recommendation
        FROM ai_client_amc_usage_view
        WHERE usage_percentage >= 70
        ORDER BY usage_percentage DESC
    ",

    'get_open_issues_by_client' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS open_issues
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0
        GROUP BY c.id
        ORDER BY open_issues DESC
    ",

    'get_low_risk_clients' => "
        SELECT
            client_id,
            client_name,
            days_to_expiry,
            remaining_hours
        FROM ai_clients_at_risk_view
        WHERE days_to_expiry > 30 AND remaining_hours > 20
        ORDER BY remaining_hours DESC
    ",

    'get_tasks_by_tech_stack' => "
        SELECT
            CASE
                WHEN c.name LIKE '%WP%' THEN 'WordPress'
                WHEN c.name LIKE '%Drupal%' THEN 'Drupal'
                WHEN c.name LIKE '%Laravel%' THEN 'Laravel'
                WHEN c.name LIKE '%Umbraco%' THEN 'Umbraco'
                ELSE 'Other'
            END AS tech_stack,
            COUNT(t.id) AS open_tasks
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0
        GROUP BY tech_stack
        ORDER BY open_tasks DESC
    ",

    'get_overdue_services' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS overdue_services
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0 AND t.due_on < CURDATE()
        GROUP BY c.id
        ORDER BY overdue_services DESC
    ",

    'get_upcoming_maintenance' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS upcoming_tasks,
            MIN(t.due_on) AS next_due_date
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0 AND t.due_on BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
        GROUP BY c.id
        ORDER BY next_due_date ASC
    ",

    'get_pending_updates' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS pending_updates
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0
        GROUP BY c.id
        ORDER BY pending_updates DESC
    ",

    'get_repeat_issue_clients' => "
        SELECT
            c.name AS client_name,
            COUNT(t.id) AS repeated_open_tasks
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0
        GROUP BY c.id
        HAVING repeated_open_tasks >= 5
        ORDER BY repeated_open_tasks DESC
    ",

    'get_deployment_backlog' => "
        SELECT
            assignee_name,
            COUNT(*) AS backlog_tasks
        FROM tasks_main
        WHERE completed = 0 AND assignee_name IS NOT NULL
        GROUP BY assignee_name
        ORDER BY backlog_tasks DESC
    ",

    'get_blocked_tasks' => "
        SELECT
            c.name AS client_name,
            t.assignee_name,
            t.due_on,
            DATEDIFF(CURDATE(), t.created_at) AS task_age_days
        FROM tasks_main t
        JOIN clients c ON c.id = t.client_id
        WHERE t.completed = 0
        ORDER BY task_age_days DESC
        LIMIT 50
    ",

    'get_workload_health_summary' => "
        SELECT
            COUNT(*) AS open_tasks,
            SUM(CASE WHEN due_on < CURDATE() THEN 1 ELSE 0 END) AS overdue_tasks,
            SUM(CASE WHEN assignee_name IS NULL OR assignee_name = '' THEN 1 ELSE 0 END) AS unassigned_tasks
        FROM tasks_main
        WHERE completed = 0
    ",

    'get_amc_revenue_risk' => "
        SELECT
            COUNT(*) AS at_risk_clients,
            SUM(CASE WHEN remaining_hours <= 0 THEN 1 ELSE 0 END) AS exhausted_clients,
            AVG(days_to_expiry) AS avg_days_to_expiry
        FROM ai_clients_at_risk_view
    ",

    'get_clients_needing_escalation' => "
        SELECT
            client_id,
            client_name,
            days_to_expiry,
            remaining_hours
        FROM ai_clients_at_risk_view
        WHERE days_to_expiry <= 14 OR remaining_hours <= 5
        ORDER BY days_to_expiry ASC, remaining_hours ASC
    ",

    'get_task_growth_trend' => "
        SELECT
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            COUNT(*) AS task_count
        FROM tasks_main
        GROUP BY month
        ORDER BY month DESC
        LIMIT 6
    ",

    'get_capacity_demand_summary' => "
        SELECT
            COUNT(*) AS open_tasks,
            COUNT(DISTINCT assignee_name) AS active_assignees,
            ROUND(COUNT(*) / NULLIF(COUNT(DISTINCT assignee_name), 0), 2) AS avg_tasks_per_assignee
        FROM tasks_main
        WHERE completed = 0 AND assignee_name IS NOT NULL
    "


];
