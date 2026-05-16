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
- `POST /projects/{projectId}/modules/decisions-register/{entryId}/delete`

All routes are served by `DecisionsRegisterController` via shared RAID behavior.

## Request Field Contract (create/update)
- Core decision fields:
  - `title`
  - `description`
  - `decision_date` (`Y-m-d`)
  - `decision_category`
  - `decision_rationale`
  - `alternatives_considered`
  - `chosen_option`
  - `made_by_user_id`
  - `approver_user_id`
  - `implementation_actions`
  - `superseded_by_entry_id` (optional)
  - `lessons_learned`
- Lifecycle and planning fields:
  - `status` (`draft|proposed|approved|implemented|rejected|superseded|closed`)
  - `priority` (`low|medium|high|critical`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - `overview`: decisions grouped by lifecycle status
  - `pending_implementation`: approved decisions pending implementation
  - `recent_key`: recent approved/implemented high-priority decisions
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data set
- `getDefaultConfig()`
  - `max_entries` default `5`
