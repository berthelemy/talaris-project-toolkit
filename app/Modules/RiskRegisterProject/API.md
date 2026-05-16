# Risk Register Project Module API

## Module Metadata
- Slug: `risk_register_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.risk_register_project.widget.read`

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/risk-register`
  - Controller: `RiskRegisterController::index`
  - Purpose: Render risk register page for a project.

- `POST /projects/{projectId}/modules/risk-register`
  - Controller: `RiskRegisterController::create`
  - Purpose: Create risk entry.

- `POST /projects/{projectId}/modules/risk-register/{entryId}/update`
  - Controller: `RiskRegisterController::update`
  - Purpose: Update risk entry.

- `POST /projects/{projectId}/modules/risk-register/{entryId}/close`
  - Controller: `RiskRegisterController::close`
  - Purpose: Mark risk entry closed.

## Request Field Contract (create/update)
- Common RAID fields:
  - `title`
  - `description`
  - `owner_user_id`
  - `status` (`open|in_review|closed`)
  - `priority` (computed for risk updates if impact/likelihood provided)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
- Risk-specific fields:
  - `mitigation_actions`
  - `impact` (`low|medium|high`)
  - `likelihood` (`low|medium|high`)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - Returns independent widgets:
    - `overview` (open risks by priority)
    - `high_priority` (high/critical risk list)
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data set
- Default config:
  - `max_entries` (default `5`)

## Authorization and Auditing
- Read/write authorization handled via shared RAID base controller and module authorization service.
- Mutations are audit logged (`raid_entry_created`, `raid_entry_updated`, `raid_entry_closed`).
- Widget cache invalidation is triggered after mutations.
