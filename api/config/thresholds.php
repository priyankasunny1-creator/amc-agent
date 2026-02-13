<?php

/**
 * RESPONSE INTELLIGENCE THRESHOLDS
 * --------------------------------
 * All severity, escalation, and risk cutoffs
 * are defined here.
 *
 * Changing these values MUST NOT require
 * any code changes elsewhere.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | AMC (Annual Maintenance Contract)
    |--------------------------------------------------------------------------
    */

    'amc' => [

        // Single-client AMC usage (%)
        'usage' => [
            'safe'     => 70,   // below this = low risk
            'warning'  => 80,   // proactive renewal
            'critical' => 100   // exhausted / overspent
        ],

        // Aggregate AMC overuse (number of clients)
        'aggregate' => [
            'medium'   => 5,    // watchlist
            'high'     => 8,    // escalation required
            'critical' => 15    // revenue risk
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | TASK / WORKLOAD (OPS)
    |--------------------------------------------------------------------------
    */

    'tasks' => [

        // Total open tasks
        'ongoing' => [
            'medium'   => 50,
            'high'     => 80,
            'critical' => 120
        ],

        // Unassigned tasks
        'unassigned' => [
            'medium' => 1,
            'high'   => 5
        ],

        // Assignee overload
        'assignee_load' => [
            'high' => 10   // tasks per person
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | CLIENT RISK (CS / MGMT)
    |--------------------------------------------------------------------------
    */

    'clients' => [

        // AMC expiry window (days)
        'expiry_days' => [
            'warning'  => 30,
            'critical' => 7
        ],

        // Inactivity window (days)
        'inactive_days' => [
            'warning' => 30,
            'high'    => 60
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | MANAGEMENT / EXECUTIVE SIGNALS
    |--------------------------------------------------------------------------
    */

    'management' => [

        // Percentage of client base affected
        'risk_exposure' => [
            'medium'   => 20,   // %
            'high'     => 40,   // %
            'critical' => 60    // %
        ]
    ]
];
