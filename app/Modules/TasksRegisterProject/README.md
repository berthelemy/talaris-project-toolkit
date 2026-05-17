# Tasks Register Project Module

## Purpose
Tracks project tasks, ownership, progress, and delivery commitments with lifecycle governance.

## Scope
- Scope type: `project`
- Module slug: `tasks_register_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/TasksRegisterController.php`: module entry points
- `Models/TasksRaidEntryModel.php`: module-local RAID entry persistence
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module provides standalone RAID controller behavior inside its own namespace.
