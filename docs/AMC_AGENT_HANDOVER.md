# AMC Agent Handover & Operating Playbook

This document is for CS, Developers, PMs, and Management to run AMC operations consistently.

## 1) What the bot now does

- Preserves conversation context via `conversation_id` across turns.
- Uses intent classification + confidence-based clarification before SQL execution.
- Returns structured response metadata (intent, confidence, row count, filters used).
- Supports AMC process guidance for onboarding, Asana workflow, reporting, escalation, and daily ops.

## 2) Team usage model

### CS
- Ask: risk clients, renewals, zero-balance clients, and weekly summaries.
- Use playbook prompts for communication cadence and escalation rules.

### Developers
- Ask: overdue workload, assignee load, and blocker patterns.
- Use structured tables for actionable triage.

### PMs
- Ask: critical overdue clients, task load distribution, and delivery velocity.
- Use `row_count` + confidence to decide whether to request clarification.

### Management
- Ask: at-risk client counts, AMC over-consumption trends, and capacity pressure.
- Use playbook escalation checklist for governance.

## 3) New client onboarding SOP (quick reference)

1. Kickoff + SLA + scope alignment.
2. Asana project initialization with workflow stages.
3. Access and stakeholder map collection.
4. Reporting cadence and campaign templates setup.
5. Baseline metrics: TAT, closure %, AMC burn, risk flags.

## 4) Suggested prompts for new AMC team members

- "Show me AMC onboarding checklist for a new client."
- "Give me Asana task management SOP for AMC."
- "What are AMC escalation metrics we should track weekly?"
- "How should we send weekly and monthly AMC reports?"
- "Give me daily AMC operations rhythm checklist."

## 5) Remaining improvements after this patch

- Complete intent ↔ SQL coverage parity (some intents still lack templates).
- Add automated tests for clarification and fallback paths.
- Add role-specific quick action buttons in UI.
- Add analytics: fallback-rate, clarification-rate, retry-rate.
