# Decisions Register Project Module

## Purpose
Tracks project decisions, decision metadata, and decision history through RAID-style records.

## Scope
- Scope type: `project`
- Module slug: `decisions_register_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/DecisionsRegisterController.php`: module entry points
- `Models/DecisionsRaidEntryModel.php`: module-local RAID entry persistence
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module provides standalone RAID controller behavior inside its own namespace.
