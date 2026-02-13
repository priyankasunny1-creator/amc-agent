# AMC Agent UX + Reliability Recommendations

## Current end-to-end flow (from code review)

1. UI sends `{ message }` to `/api/index.php`.
2. API classifies intent with LLM (`IntentClassifier`) and validates against configured intents.
3. API resolves parameters (`IntentResolver`) and validates (`IntentValidator`).
4. SQL executes from template map (`SqlExecutor` + `config/sql_templates.php`).
5. Result set is optionally narrowed from free-text constraints (`ResultNarrower`).
6. Intelligence metrics are generated (`ResponseIntelligence`) and converted into final natural-language summary (`ResponseFormatter`).
7. API returns `response.summary` (or a fallback message).

## Why the system feels “not user friendly” right now

### 1) UI loses context and has no conversational continuity
- The frontend currently POSTs only `message` and does not send `conversation_id`.
- Backend has conversation memory support (`ConversationMemory`) and follow-up handling (“what about...”, “show only...”), but this path only runs if `conversation_id` is provided.
- Net effect: users *think* they are in a conversation, but the app treats each message like a fresh query.

### 2) UI only shows a plain summary string
- Backend returns richer payloads (`response`, intent, intelligence-derived structure), but the frontend collapses everything into `data.response?.summary`.
- Users do not see confidence, filter interpretation, tables, or actionable details.

### 3) No loading, retry, or graceful error states in frontend
- Chat input has no disabled/loading state while awaiting response.
- Network/API failures are not surfaced with clear, user-safe messages.
- This feels unresponsive and uncertain in real usage.

### 4) API returns generic fallback too often
- In `index.php`, broad exceptions return “Unable to process this request with the current data.”
- When formatting fails, it always shows a static “No results available...” message + hardcoded queue URL.
- Users are not told *what to do next* in context.

### 5) Intent/parameter clarification loop is missing
- If intent candidates are ambiguous or parameter extraction is partial, API should ask follow-up questions (e.g., “Do you mean WordPress tasks or all CMS tasks?”).
- Current flow proceeds directly to execution or generic fallback.

### 6) Hardcoded heuristics for follow-up are narrow
- Follow-up detection checks only two phrase patterns in `index.php`.
- Natural user phrasing has much larger variety (“how about”, “and for WP?”, “same for Laravel”).

### 7) Operational transparency is weak
- `RequestLogger` exists but user-visible traceability is absent.
- No response metadata in UI (intent used, filters applied, row count), so users cannot build trust in outputs.

## High-impact suggestions (priority order)

## P0 — Fix core chat usability first

1. **Persist and send a `conversation_id` from UI**
   - Generate once in browser (UUID/localStorage) and include in every API call.
   - This instantly enables existing backend memory logic and makes follow-up queries feel natural.

2. **Add explicit loading + error states**
   - Disable input while request is pending.
   - Show typing indicator (“AMC is analyzing your request…”).
   - On failure, show retry CTA and safe error text.

3. **Render structured results, not summary-only text**
   - If backend returns lists/metrics, display cards/table blocks.
   - Keep summary at top, but include “Details” and “Filters used.”

## P1 — Improve API friendliness

4. **Return standardized response envelope**
   - Example: `{ ok, intent, confidence, summary, details, follow_up_question, trace_id }`.
   - Avoid shape variability that forces fragile frontend assumptions.

5. **Add clarification mode for low-confidence intent/params**
   - If confidence below threshold, return a clarifying question instead of running SQL.
   - This avoids incorrect answers that erode trust.

6. **Replace static fallback with actionable guidance**
   - Include interpreted intent, known filters, and specific next prompts user can try.
   - Example: “I couldn’t find overdue tasks for ‘WP’. Try ‘overdue tasks for wordpress clients in last 30 days’.”

## P2 — Improve trust + adoption

7. **Show “How I interpreted your question” in UI**
   - Display detected intent + extracted entities (client, date range, tech stack).
   - Let user click to edit/refine.

8. **Expose top suggestions and quick actions**
   - Add one-tap prompts (“Show overdue by client”, “Task load by assignee”, etc.) based on `intents.php`.

9. **Track quality metrics**
   - Measure fallback rate, clarification rate, retry rate, and “thumbs down” frequency.
   - Use these to continuously tune thresholds and prompts.

## Suggested implementation roadmap

### Sprint 1 (fast UX wins)
- Frontend: `conversation_id` persistence + loading/error states.
- API: consistent envelope + `trace_id`.
- Frontend: basic renderer for `summary` + optional list/table details.

### Sprint 2 (intent confidence + clarification)
- Add confidence scoring and clarification response path.
- Expand follow-up phrase normalization beyond two hardcoded strings.

### Sprint 3 (trust and productivity)
- Query interpretation panel.
- Quick action chips.
- Lightweight feedback loop and analytics dashboard.

## Minimal acceptance criteria for “user-friendly” baseline

- A user can ask 3 follow-up questions without retyping context.
- Every request has visible loading state and deterministic error message.
- Every answer clearly shows: intent used, filters applied, and row-count/result scope.
- Ambiguous queries trigger clarification instead of wrong answers.

