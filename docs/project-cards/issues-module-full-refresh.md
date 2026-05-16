---
title: Card - Issues Module Full Refresh
type: card
status: Done
updated: 2026-05-16
blocked_by:
  - phase-10-desktop-ui-overhaul-and-navigation
---
# Card - Issues Module Full Refresh

## Requirement Source
[[modules/03-issues]]

## Embedded Requirement Content

### Purpose
The issues module is designed to help managers record, prioritise, manage, and resolve project or programme issues that arise during delivery.

### Core Functions
1. Add, edit, or delete issue entries.
2. Track each issue through to resolution.
3. View a summary of issue status.
4. Highlight high priority or overdue issues.

### Details
Each issue appears as an entry in a datatable with key issue data and current status.

Each issue follows a lifecycle from creation to closure, with updates to ownership, action plans, and progress notes as the issue evolves.

### Data Requirements
- Date entered
- Person entering the issue entry
- Issue title
- Issue description
- Date reported
- Reporter
- Impact level (high, medium, low)
- Priority (critical, high, medium, low)
- Status (open, in review, blocked, resolved, closed)
- Resolution actions
- Owner
- Target resolution date
- Review date
- Closed (boolean)
- Closing date
- Lessons learned

### Widget Requirements
- Issues overview: summary grid of open issues by status and priority.
- High priority issues: list of high and critical issues requiring immediate attention.
- Overdue issues: list of issues where target resolution date has passed and issue is not closed.

Each issue should be rendered as a link to open issue details.

## Definition of Done
- CRUD including delete and closure tracking.
- Status workflow: open, in review, blocked, resolved, closed.
- Required fields and lifecycle behavior aligned to spec.
- Widgets: overview, high priority, overdue.
