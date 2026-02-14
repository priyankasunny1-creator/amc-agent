<?php

return [
    'keywords' => [
        'onboarding' => ['onboard', 'onboarding', 'new client', 'kickoff', 'handover'],
        'asana' => ['asana', 'task board', 'task assign', 'daily assignment'],
        'reporting' => ['report', 'reports', 'reporting', 'campaign', 'email report', 'monthly report', 'weekly report', 'weekly and monthly', 'amc health summary'],
        'escalation' => ['escalation', 'sla breach', 'risk', 'critical'],
        'daily_ops' => ['daily standup', 'daily ops', 'today tasks', 'work allocation', 'operations rhythm', 'daily operations rhythm', 'daily checklist']
    ],
    'sections' => [
        'onboarding' => [
            'title' => 'New AMC Client Onboarding',
            'summary' => 'Run kickoff, baseline audit, scope mapping, and communication setup within first 5 working days.',
            'checklist' => [
                'Confirm AMC scope, exclusions, and SLA matrix in kickoff MOM.',
                'Create Asana project template: incidents, maintenance, enhancements, reporting.',
                'Collect credentials + infra access + stakeholder escalation ladder.',
                'Publish first-30-days service calendar and reporting cadence.',
                'Define success KPIs: response TAT, closure rate, consumed vs remaining AMC hours.'
            ]
        ],
        'asana' => [
            'title' => 'Asana Task Management SOP',
            'summary' => 'Use a strict workflow so CS, Dev, and PM can track ownership and SLA risk in one place.',
            'checklist' => [
                'Every ticket must include client, category, effort estimate, due date, and assignee.',
                'Use status stages: New -> Triaged -> In Progress -> Client Review -> Done.',
                'Tag blockers with root cause and expected unblock date.',
                'Review overloaded assignees daily and rebalance before SLA breach.',
                'Escalate stale tasks (>2 days no update) in PM standup.'
            ]
        ],
        'reporting' => [
            'title' => 'Client Reporting via Campaigns',
            'summary' => 'Send concise performance narratives backed by AMC usage and delivery metrics.',
            'checklist' => [
                'Weekly: top delivered items, pending blockers, and next-week plan.',
                'Monthly: AMC hours consumed, balance, SLA compliance, and recommendations.',
                'For campaign emails, keep one executive summary + one detailed appendix.',
                'Highlight risks with mitigation owner and target date.',
                'Archive sent reports and approvals for audit traceability.'
            ]
        ],
        'escalation' => [
            'title' => 'Escalation Metrics and Rules',
            'summary' => 'Escalate early with objective thresholds so management sees risk before client impact.',
            'checklist' => [
                'Critical if AMC remaining hours <= 0 or unresolved P1 > 24 hours.',
                'High risk if contract expiry <= 30 days with unresolved renewal plan.',
                'Escalate workload imbalance when an assignee exceeds agreed open-task cap.',
                'Track and publish escalation MTTR and reopen rate weekly.',
                'Maintain RACI for every escalation thread (CS owner, PM owner, tech owner).'
            ]
        ],
        'daily_ops' => [
            'title' => 'Daily AMC Operations Rhythm',
            'summary' => 'Run a fixed daily rhythm across CS, Dev, and PM for predictable execution.',
            'checklist' => [
                'Morning triage: prioritize incidents, overdue work, and today due tasks.',
                'Midday sync: review blockers, dependencies, and client communication needs.',
                'Evening closure: update task status, timesheets, and next-day assignments.',
                'Capture learning notes for recurring incidents and preventive fixes.',
                'Update management snapshot with workload, risk, and renewal signals.'
            ]
        ]
    ]
];
