# Issues module

The issues module is designed to help managers to record, prioritise, manage and resolve project or programme issues that arise during delivery.

## Core functions

The main interface will include capabilities to:

1. Add, edit or delete issue entries
2. Track each issue through to resolution
3. View a summary of the issues' status
4. Highlight high priority or overdue issues

## Details

Each entry in the issue register will have its own entry in a datatable, showing key issue data and current status.

Each issue should support a clear lifecycle from creation to closure, with updates to ownership, action plans and progress notes recorded as the issue evolves.

## Data

Each issue entry will include:

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

## Widgets

### Issues overview

Designed to show a summary grid of the number of open issues by status and priority.

### High priority issues

Designed to show a list of high and critical issues that require immediate attention.

### Overdue issues

Designed to show issues where the target resolution date has passed and the issue is not closed.

Each issue should be rendered as a link to open the issue details.
