# Meeting Notes Project Module

## Purpose
Capture structured meeting notes, decisions, and action items at project scope with optional task linking.

## Scope
- Scope type: `project`
- Module slug: `meeting_notes_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/MeetingNotesController.php`: CRUD and action/task linking workflows
- `Models/MeetingNotesRaidEntryModel.php`: local RAID entry persistence used for related RAID/task linking
- `Widgets/ModuleWidget.php`: dashboard widgets for overview, open actions, and follow-ups
- `Views/`: module page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
The module persists note headers and action items in dedicated tables and can create linked task entries when the Tasks module is enabled.