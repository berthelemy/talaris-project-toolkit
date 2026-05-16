# Issue Tracker Project Module API

## Module Metadata
- Slug: `issue_tracker_project`
- Scope: `project`
- Version: `1.1.0`
- Widget permission: `module.issue_tracker_project.widget.read`

## Internal Module Integration
- Cross-module API access is internal-only via `App\Libraries\Modules\ModuleInternalApiService`.
- No direct HTTP module API endpoints (for example `/api/modules/...`) are exposed.

## HTTP Routes
Defined in `Config/routes.php`.

These are application page/action routes for authenticated users, not cross-module API endpoints.

- `GET /projects/{projectId}/modules/issue-tracker`
- `POST /projects/{projectId}/modules/issue-tracker`
- `POST /projects/{projectId}/modules/issue-tracker/{entryId}/update`
- `POST /projects/{projectId}/modules/issue-tracker/{entryId}/close`
- `POST /projects/{projectId}/modules/issue-tracker/{entryId}/delete`

All routes are served by `IssueTrackerController` with module-local standalone RAID behavior.

## Request Field Contract (create/update)
- Common RAID fields:
  - `title`
  - `description`
  - `owner_user_id`
  - `status` (`open|in_review|blocked|resolved|closed`)
  - `priority` (`low|medium|high|critical`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
- Issue-specific fields:
  - `date_reported` (`Y-m-d`)
  - `reporter_user_id`
  - `impact_level` (`low|medium|high`)
  - `mitigation_actions` (resolution actions)
  - `lessons_learned`

## Exposed Data Fields (module table/detail)
- System-managed fields:
  - `created_at` (Date entered)
  - `created_by_user_id` / `created_by_username` (Person entering the issue entry)
  - `closed_at` (Closing date)
  - `closed` boolean derived from `closed_at` (Closed)
- Issue fields:
  - `title` (Issue title)
  - `description` (Issue description)
  - `date_reported`
  - `reporter_user_id`
  - `impact_level`
  - `priority`
  - `status`
  - `mitigation_actions` (Resolution actions)
  - `owner_user_id`
  - `target_date` (Target resolution date)
  - `review_date`
  - `lessons_learned`

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - `overview`: issue totals grouped by status and priority
  - `high_priority`: active high/critical issues
  - `overdue`: active issues past target date
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged aggregate data
- `getDefaultConfig()`
  - `max_entries` default `5`
