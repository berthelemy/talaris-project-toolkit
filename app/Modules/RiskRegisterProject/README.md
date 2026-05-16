# Risk Register Project Module

## Purpose
Tracks project risks, priorities, ownership, and mitigation actions with RAID lifecycle support.

## Scope
- Scope type: `project`
- Module slug: `risk_register_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/RiskRegisterController.php`: module entry points
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module uses shared RAID behavior provided by `app/Modules/RaidShared`.
