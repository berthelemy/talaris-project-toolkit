---
title: Card - Meeting Notes Module
status: done
type: card
updated: 2026-05-16
---
# Card - Meeting Notes Module

## Requirement Source
Draft requirement card (module requirement file to be created).

## Embedded Requirement Content

### Purpose
The meeting notes module is designed to help managers and teams capture structured meeting records, track decisions and actions, and keep follow-up work visible and accountable across project or programme delivery.

### Core Functions
1. Add, edit, or delete meeting note entries.
2. Capture attendees, agenda points, discussion notes, decisions, and action items.
3. Link action items to the tasks module (if present), including creating a task from a meeting note action.
4. View a summary of meetings, open actions, and upcoming follow-up checkpoints.

### Details
Each meeting appears as an entry in a datatable with key meeting metadata and action progress indicators.

Each meeting note follows a lifecycle from draft capture through finalization and closure, with versioned updates for corrections and post-meeting outcomes.

Action items created in meeting notes should support traceable links to related task records, without requiring duplicate data entry.

### Data Requirements
- Date entered
- Person entering the meeting note entry
- Meeting title
- Meeting description or purpose
- Meeting date
- Meeting type (stand-up, planning, steering, review, retrospective, other)
- Context level (project or programme)
- Related project or programme objective (optional)
- Chair/facilitator
- Minute taker
- Attendees (required list)
- Absentees/apologies (optional list)
- Agenda items
- Discussion notes (structured by agenda item when possible)
- Decisions made (linked to Decisions module)
- Risks/issues/dependencies raised (optional links)
- Action items
- Action owner per action item
- Action due date per action item
- Action status per action item (open, in progress, blocked, completed, cancelled)
- Linked task reference per action item (optional, required when task is created)
- Follow-up meeting date (optional)
- Status (draft, finalized, archived)


### Internal Integration Requirements
- If the tasks module is enabled, users can create a linked task from an action item directly in the meeting note workflow.
- Linked tasks must preserve reference integrity in both directions where supported (meeting note to task, task to originating meeting note).
- If the tasks module is not enabled, meeting action tracking remains available in-module with no broken links.
- Permission checks must align with project/programme RBAC before creating or viewing linked tasks.

### Widget Requirements
- Meetings overview: summary grid of recent meetings by status and type.
- Open meeting actions: list of open or blocked action items from recent meetings.
- Upcoming follow-ups: list of meetings with scheduled follow-up date in the next defined window.

Each meeting note should be rendered as a link to open meeting note details.

## Definition of Done
- CRUD including delete.
- Lifecycle workflow: draft, finalized, archived.
- Meeting metadata, attendees, discussion, decisions, and action tracking fields aligned to spec.
- Tasks-module linking implemented when module is present, with graceful fallback when absent.
- Widgets: meetings overview, open meeting actions, upcoming follow-ups.
- Traceability maintained from meeting actions to linked tasks and back where supported.
