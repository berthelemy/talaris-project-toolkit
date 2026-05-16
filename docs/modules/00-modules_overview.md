# Modules overview

## Rules

- All modules will use the standard module structure
- Cross-module API access is internal-only via service-layer integration
- No direct HTTP module API endpoints are exposed for cross-module access
- All modules will include API documentation describing internal contracts and module routes
- All modules will include a README file in the module directory
- Modules can be designed for either Projects or Programmes

## Interface

- All modules will provide an interface through which their data can be accessed and changed
- The main interface will be accessed through the routes /projects/[project_id]/modules/[module_name] or /programmes/[programme_id]/modules/[module_name]
- All module interfaces should support full lifecycle management of records, including create, update and closure, and delete where applicable
- All module interfaces should provide clear status tracking from entry creation through resolution or completion

## Widgets

- All modules will provide one or more widgets
- The widgets will be available to view on the Overview page of each project or programme
- A project or programme manager can choose to enable or disable any of the available widgets for the project or programme
- All widgets will include a button to add data to the module in a modal popup
- All widgets will include a button to view the main page for the module
- Each module should include at least one summary widget and one action-focused widget (for example high priority, overdue or pending implementation)

## Specifications

- Specifications for each module are in separate files in this directory.

### Module requirement files

- 01-risks.md: Risk identification, prioritisation, mitigation and closure tracking.
- 02-assumptions.md: Assumption capture, validation planning and closure tracking.
- 03-issues.md: Issue capture, resolution management and overdue/high-priority monitoring.
- 04-decisions.md: Decision-point recording, implementation tracking and supersession traceability.
- 05-dependencies.md: Dependency monitoring, impact/escalation tracking and fulfillment management.
- 06-tasks.md: Task assignment, progress tracking and completion management.