# Tasks Register Project Module API

## Module Metadata
- Slug: `tasks_register_project`
- Scope: `project`
- Version: `1.1.0`
- Widget permission: `module.tasks_register_project.widget.read`

## Internal Module Integration
- Cross-module API access is internal-only via `App\Libraries\Modules\ModuleInternalApiService`.
- No direct HTTP module API endpoints are exposed.

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/tasks-register`
- `POST /projects/{projectId}/modules/tasks-register`
- `POST /projects/{projectId}/modules/tasks-register/{entryId}/update`
- `POST /projects/{projectId}/modules/tasks-register/{entryId}/close`
- `POST /projects/{projectId}/modules/tasks-register/{entryId}/delete`

## Request Field Contract (create/update)
- Core fields:
  - `title`
  - `description`
  - `owner_user_id`
  - `priority` (`low|medium|high|critical`)
  - `status` (`open|in_progress|blocked|in_review|completed|cancelled|closed`)
- Task-specific fields:
  - `task_category`
  - `related_objective`
  - `related_module_entry_id` (optional)
  - `collaborators` (optional)
  - `percent_complete` (`0..100`)
  - `planned_start_date` (`Y-m-d`)
  - `due_date` (`Y-m-d`)
  - `completed_date` (`Y-m-d`)
  - `blocked_reason`
  - `next_action`
  - `lessons_learned`

## Exposed Data Fields (module table/detail)
- System-managed fields:
  - `created_at` (Date entered)
  - `created_by_user_id` / `created_by_username` (Person entering the task entry)
  - `closed_at` (Closing date)
  - `closed` boolean derived from `closed_at` (Closed)
- Task fields:
  - `title` (Task title)
  - `description` (Task description)
  - `task_category`
  - `related_objective` (Related project or programme objective)
  - `related_module_entry_id` (Related module record)
  - `owner_user_id`
  - `collaborators`
  - `priority`
  - `status`
  - `percent_complete`
  - `planned_start_date`
  - `due_date`
  - `completed_date`
  - `blocked_reason`
  - `next_action`
  - `lessons_learned`

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - `overview`: tasks grouped by status and priority
  - `my_open`: current-user assigned open tasks
  - `overdue`: overdue and not completed/closed tasks
- Backward-compatible methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data set
- `getDefaultConfig()`
  - `max_entries` default `5`
