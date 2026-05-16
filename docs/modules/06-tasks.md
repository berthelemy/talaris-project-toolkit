---
title: Tasks module
type: requirements
updated: 2026-05-16
---
# Tasks module

The tasks module is designed to help managers and teams plan, assign, monitor and complete project or programme tasks required to deliver outcomes.

## Core functions

The main interface will include capabilities to:

1. Add, edit or delete task entries
2. Assign tasks to owners and track progress through completion
3. Manage task priorities, due dates and dependencies
4. View a summary of task status, workload and overdue actions

## Details

Each entry in the tasks register will have its own entry in a datatable, showing key task data and current status.

Each task should support a clear lifecycle from creation through active work to completion or cancellation, with progress updates and completion notes recorded over time.

## Data

Each task entry will include:

- Date entered
- Person entering the task entry
- Task title
- Task description
- Task category
- Related project or programme objective
- Related module record (optional link to risk, issue, decision or dependency)
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

## Widgets

### Tasks overview

Designed to show a summary grid of tasks by status and priority.

### My open tasks

Designed to show a list of currently assigned open tasks for the current user.

### Overdue tasks

Designed to show tasks where the due date has passed and the task is not completed or closed.

Each task should be rendered as a link to open the task details.
