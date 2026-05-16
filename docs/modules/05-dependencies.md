# Dependencies module

The dependencies module is designed to help managers to record, monitor and manage internal and external dependencies that affect project or programme delivery.

## Core functions

The main interface will include capabilities to:

1. Add, edit or delete dependency entries
2. Track each dependency through to fulfillment or closure
3. Record ownership, due dates and mitigation actions for at-risk dependencies
4. View a summary of dependency status and delivery impact

## Details

Each entry in the dependencies register will have its own entry in a datatable, showing key dependency data and current status.

Each dependency should support a clear lifecycle from identification through monitoring to fulfillment, cancellation or closure, with updates and escalation notes captured over time.

## Data

Each dependency entry will include:

- Date entered
- Person entering the dependency entry
- Dependency title
- Dependency description
- Dependency type (internal, external, supplier, customer, technical, regulatory, other)
- Related work package or milestone
- Depends on (team, supplier, project, programme or external party)
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

## Widgets

### Dependencies overview

Designed to show a summary grid of dependencies by status and impact level.

### At-risk dependencies

Designed to show a list of dependencies marked as high impact, blocked or at risk.

### Overdue dependencies

Designed to show dependencies where the target date has passed and the dependency is not fulfilled or closed.

Each dependency should be rendered as a link to open the dependency details.
