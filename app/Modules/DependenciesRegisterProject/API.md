# Dependencies Register Project Module API

## Module Metadata
- Slug: `dependencies_register_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.dependencies_register_project.widget.read`

## Internal Module Integration
- Cross-module API access is internal-only via `App\Libraries\Modules\ModuleInternalApiService`.
- No direct HTTP module API endpoints (for example `/api/modules/...`) are exposed.

## HTTP Routes
Defined in `Config/routes.php`.

These are application page/action routes for authenticated users, not cross-module API endpoints.

- `GET /projects/{projectId}/modules/dependencies-register`
- `POST /projects/{projectId}/modules/dependencies-register`
- `POST /projects/{projectId}/modules/dependencies-register/{entryId}/update`
- `POST /projects/{projectId}/modules/dependencies-register/{entryId}/close`
- `POST /projects/{projectId}/modules/dependencies-register/{entryId}/delete`

All routes are served by `DependenciesRegisterController` via shared RAID behavior.

## Request Field Contract (create/update)
- Core dependency fields:
  - `title`
  - `description`
  - `dependency_type` (`internal|external|supplier|customer|technical|regulatory|other`)
  - `related_work_package`
  - `depends_on`
  - `owner_user_id`
  - `impact_level` (`low|medium|high`)
  - `priority` (`low|medium|high|critical`)
  - `mitigation_actions`
  - `escalation_required` (`0|1`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
  - `lessons_learned`
- Lifecycle fields:
  - `status` (`open|in_progress|at_risk|blocked|fulfilled|cancelled|closed`)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - `overview`: dependencies grouped by status and impact level
  - `at_risk`: dependencies with high impact, blocked, or at-risk status
  - `overdue`: dependencies with passed target date and unresolved status
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data set
- `getDefaultConfig()`
  - `max_entries` default `5`
