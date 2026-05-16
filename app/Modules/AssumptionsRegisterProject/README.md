# Assumptions Register Project Module

## Purpose
Tracks project assumptions as RAID entries and supports inline updates, closure, and widget summaries.

## Scope
- Scope type: `project`
- Module slug: `assumptions_register_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/AssumptionsRegisterController.php`: module entry points
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module uses shared RAID behavior provided by `app/Modules/RaidShared`.
