# Issue Tracker Project Module

## Purpose
Tracks project issues, reporting details, ownership, status, and mitigation progress.

## Scope
- Scope type: `project`
- Module slug: `issue_tracker_project`

## Main Components
- `Config/routes.php`: module route registrations
- `Controllers/IssueTrackerController.php`: module entry points
- `Widgets/ModuleWidget.php`: dashboard widgets
- `Views/`: register page and widget templates

## Documentation
- API contract and routes: `API.md`

## Notes
This module provides standalone RAID controller behavior inside its own namespace.
