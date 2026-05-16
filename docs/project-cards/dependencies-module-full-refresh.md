---
title: Card - Dependencies Module Full Refresh
type: card
status: in-progress
updated: 2026-05-16
blocked_by:
  - phase-10-desktop-ui-overhaul-and-navigation
---
# Card - Dependencies Module Full Refresh

## Requirement Source
[[modules/05-dependencies]]

## Embedded Requirement Content

### Purpose
The dependencies module is designed to help managers record, monitor, and manage internal and external dependencies that affect project or programme delivery.

### Core Functions
1. Add, edit, or delete dependency entries.
2. Track each dependency through to fulfillment or closure.
3. Record ownership, due dates, and mitigation actions for at-risk dependencies.
4. View a summary of dependency status and delivery impact.

### Details
Each dependency appears as an entry in a datatable with key dependency data and current status.

Each dependency follows a lifecycle from identification through monitoring to fulfillment, cancellation, or closure, with updates and escalation notes over time.

### Data Requirements
- Date entered
- Person entering the dependency entry
- Dependency title
- Dependency description
- Dependency type (internal, external, supplier, customer, technical, regulatory, other)
- Related work package or milestone
- Depends on (team, supplier, project, programme, or external party)
- Owner
- Impact level (high, medium, low)
- Priority (critical, high, medium, low)
- Status (open, in progress, at risk, blocked, fulfilled, cancelled, closed)
- Mitigation actions
- Target date
- Review date
- Escalation required (boolean)
- Closed (boolean)
- Closing date
- Lessons learned

### Widget Requirements
- Dependencies overview: summary grid of dependencies by status and impact level.
- At-risk dependencies: list of dependencies marked high impact, blocked, or at risk.
- Overdue dependencies: list of dependencies where target date passed and dependency is not fulfilled or closed.

Each dependency should be rendered as a link to open dependency details.

## Definition of Done
- CRUD including delete and closure tracking.
- Lifecycle workflow with fulfillment/cancellation/closure paths.
- Dependency, impact, and escalation fields aligned to spec.
- Widgets: overview, at-risk, overdue.
