---
title: Card - Decisions Module Full Refresh
type: card
status: blocked
updated: 2026-05-16
blocked_by:
  - phase-10-desktop-ui-overhaul-and-navigation
---
# Card - Decisions Module Full Refresh

## Requirement Source
[[modules/04-decisions]]

## Embedded Requirement Content

### Purpose
The decisions module is designed to help managers record key decision points during a project or programme, and track implementation and outcomes.

### Core Functions
1. Add, edit, or delete decision entries.
2. Record decision rationale, approvers, and expected outcomes.
3. Track decision status from draft to implemented or superseded.
4. View a summary of decision progress and pending actions.

### Details
Each decision appears as an entry in a datatable with key decision data and current status.

Each decision follows a lifecycle from initial proposal through approval and implementation, with superseding decision links for traceability.

### Data Requirements
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

### Widget Requirements
- Decisions overview: summary grid of decisions by status.
- Pending implementation decisions: list of approved decisions not yet implemented.
- Recent key decisions: list of recently approved or implemented high-impact decisions.

Each decision should be rendered as a link to open decision details.

## Definition of Done
- CRUD including delete and closure tracking.
- Lifecycle workflow: draft through implemented/rejected/superseded/closed.
- Decision data fields and supersession traceability implemented.
- Widgets: overview, pending implementation, recent key decisions.
