# Decisions module

The decisions module is designed to help managers to record key decision points during a project or programme, and to track their implementation and outcomes.

## Core functions

The main interface will include capabilities to:

1. Add, edit or delete decision entries
2. Record decision rationale, approvers and expected outcomes
3. Track decision status from draft to implemented or superseded
4. View a summary of decision progress and pending actions

## Details

Each entry in the decisions register will have its own entry in a datatable, showing key decision data and current status.

Each decision should support a clear lifecycle from initial proposal through approval and implementation, with any superseding decision linked for traceability.

## Data

Each decision entry will include:

- Date entered
- Person entering the decision entry
- Decision title
- Decision description
- Decision date
- Decision category
- Decision rationale
- Alternatives considered
- Chosen option
- Made by
- Approver
- Status (draft, proposed, approved, implemented, rejected, superseded, closed)
- Implementation actions
- Target implementation date
- Review date
- Superseded by (optional link to another decision)
- Closed (boolean)
- Closing date
- Lessons learned

## Widgets

### Decisions overview

Designed to show a summary grid of decisions by status.

### Pending implementation decisions

Designed to show a list of approved decisions not yet implemented.

### Recent key decisions

Designed to show a list of recently approved or implemented high-impact decisions.

Each decision should be rendered as a link to open the decision details.
