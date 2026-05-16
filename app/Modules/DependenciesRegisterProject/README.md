# Dependencies Register Project Module

## Purpose
Tracks project dependencies, impact level, ownership, and follow-up actions in RAID records.

## Scope
- Scope type: `project`
- Module slug: `dependencies_register_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/DependenciesRegisterController.php`: module entry points
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module uses shared RAID behavior provided by `app/Modules/RaidShared`.
