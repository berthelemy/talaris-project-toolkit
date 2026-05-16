# Decisions Register Project Module API

## Module Metadata
- Slug: `decisions_register_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.decisions_register_project.widget.read`

## Internal Module Integration
- Cross-module API access is internal-only via `App\Libraries\Modules\ModuleInternalApiService`.
- No direct HTTP module API endpoints (for example `/api/modules/...`) are exposed.

## HTTP Routes
Defined in `Config/routes.php`.

These are application page/action routes for authenticated users, not cross-module API endpoints.

- `GET /projects/{projectId}/modules/decisions-register`
- `POST /projects/{projectId}/modules/decisions-register`
- `POST /projects/{projectId}/modules/decisions-register/{entryId}/update`
- `POST /projects/{projectId}/modules/decisions-register/{entryId}/close`

All routes are served by `DecisionsRegisterController` via shared RAID behavior.

## Request Field Contract (create/update)
- Decision-specific required fields:
  - `description`
  - `decision_date` (`Y-m-d`)
  - `made_by_user_id`
- Additional supported RAID fields:
  - `status` (`open|in_review|closed`)
  - `priority` (`low|medium|high|critical`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetView(int $scopeId)` -> decisions widget view path
- `getWidgetData(int $scopeId, array $config = [])`
  - returns latest decisions by date
- `getDefaultConfig()`
  - `max_entries` default `5`
