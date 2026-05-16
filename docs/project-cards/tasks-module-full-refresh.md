---
title: Card - Tasks Module Full Refresh
type: card
status: blocked
updated: 2026-05-16
blocked_by:
  - phase-10-desktop-ui-overhaul-and-navigation
---
# Card - Tasks Module Full Refresh

## Requirement Source
[[modules/06-tasks]]

## Embedded Requirement Content

### Purpose
The tasks module is designed to help managers and teams plan, assign, monitor, and complete project or programme tasks required to deliver outcomes.

### Core Functions
1. Add, edit, or delete task entries.
2. Assign tasks to owners and track progress through completion.
3. Manage task priorities, due dates, and dependencies.
4. View a summary of task status, workload, and overdue actions.

### Details
Each task appears as an entry in a datatable with key task data and current status.

Each task follows a lifecycle from creation through active work to completion or cancellation, with progress updates and completion notes over time.

### Data Requirements
- Date entered
- Person entering the task entry
- Task title
- Task description
- Task category
- Related project or programme objective
- Related module record (optional link to risk, issue, decision, or dependency)
- Owner
- Collaborators (optional)
- Priority (critical, high, medium, low)
- Status (open, in progress, blocked, in review, completed, cancelled, closed)
- Percent complete
- Planned start date
- Due date
- Completed date
- Blocked reason
- Next action
- Closed (boolean)
- Closing date
- Lessons learned

### Widget Requirements
- Tasks overview: summary grid of tasks by status and priority.
- My open tasks: list of currently assigned open tasks for current user.
- Overdue tasks: list of tasks where due date passed and task is not completed or closed.

Each task should be rendered as a link to open task details.

## Definition of Done
- New or updated module implementation aligned to tasks specification.
- CRUD including delete and closure tracking.
- Lifecycle and progress model implemented (including blocked and completed states).
- Widgets: overview, my open tasks, overdue tasks.
