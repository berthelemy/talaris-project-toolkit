# Assumptions Register Project Module API

## Module Metadata
- Slug: `assumptions_register_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.assumptions_register_project.widget.read`

## Internal Module Integration
- Cross-module API access is internal-only via `App\Libraries\Modules\ModuleInternalApiService`.
- No direct HTTP module API endpoints (for example `/api/modules/...`) are exposed.

## HTTP Routes
Defined in `Config/routes.php`.

These are application page/action routes for authenticated users, not cross-module API endpoints.

- `GET /projects/{projectId}/modules/assumptions-register`
  - Controller: `AssumptionsRegisterController::index`
  - Purpose: Render assumptions register page for a project.

- `POST /projects/{projectId}/modules/assumptions-register`
  - Controller: `AssumptionsRegisterController::create`
  - Purpose: Create assumption entry.

- `POST /projects/{projectId}/modules/assumptions-register/{entryId}/update`
  - Controller: `AssumptionsRegisterController::update`
  - Purpose: Update assumption entry.

- `POST /projects/{projectId}/modules/assumptions-register/{entryId}/close`
  - Controller: `AssumptionsRegisterController::close`
  - Purpose: Mark assumption entry closed.

## Request Field Contract (create/update)
- Common RAID fields:
  - `title` (assumption statement/description)
  - `owner_user_id`
  - `status` (`open|in_review|closed`)
  - `priority` (stored; defaults to `medium` in UI)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
- Assumptions-specific fields:
  - `impact_if_not_valid`
  - `validation_actions` (normalized to `mitigation_actions` in persistence)
  - `impact_level` (`low|medium|high`)
  - `lessons_learned`

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - Returns independent widgets:
    - `overview` (open assumptions by impact level)
    - `high_priority` (high-impact assumption list)
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data set
- Default config:
  - `max_entries` (default `5`)

## Authorization and Auditing
- Read/write authorization handled via shared RAID base controller and module authorization service.
- Mutations are audit logged (`raid_entry_created`, `raid_entry_updated`, `raid_entry_closed`).
- Widget cache invalidation is triggered after mutations.
