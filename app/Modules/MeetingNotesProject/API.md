# Meeting Notes Project Module API

## Module Metadata
- Slug: `meeting_notes_project`
- Scope: `project`
- Version: `0.1.0`
- Widget permission: `module.meeting_notes_project.widget.read`

## Internal Module Integration
- Cross-module API access remains internal-only via module services.
- No direct HTTP module API endpoints are exposed for cross-module integrations.
- When Tasks is enabled, action rows can create linked task records in `module_raid_entries` under `tasks_register_project`.

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/meeting-notes`
- `POST /projects/{projectId}/modules/meeting-notes`
- `POST /projects/{projectId}/modules/meeting-notes/{noteId}/update`
- `POST /projects/{projectId}/modules/meeting-notes/{noteId}/close`
- `POST /projects/{projectId}/modules/meeting-notes/{noteId}/delete`

## Request Field Contract (create/update)
- Note fields:
  - `title`
  - `purpose`
  - `meeting_date` (`Y-m-d`)
  - `meeting_type` (`stand-up|planning|steering|review|retrospective|other`)
  - `status` (`draft|finalized|archived|closed`)
  - `follow_up_date` (`Y-m-d`)
  - `related_objective`
  - `chair_user_id`
  - `minute_taker_user_id`
  - `attendees_text`
  - `absentees_text`
  - `agenda_text`
  - `discussion_text`
  - `decisions_text`
  - `raised_links_text`
  - `lessons_learned`
- Action arrays (same index per row):
  - `action_description[]`
  - `action_owner_user_id[]`
  - `action_due_date[]`
  - `action_status[]` (`open|in_progress|blocked|completed|cancelled`)
  - `action_create_task[index]` (optional checkbox)

## Exposed Data Fields
- Note table `module_meeting_notes`:
  - `module_slug`, `scope_type`, `scope_id`
  - `title`, `purpose`, `meeting_date`, `meeting_type`, `status`
  - `chair_user_id`, `minute_taker_user_id`
  - `attendees_text`, `absentees_text`, `agenda_text`, `discussion_text`, `decisions_text`, `raised_links_text`
  - `follow_up_date`, `lessons_learned`, `closed_at`
  - `created_by_user_id`, `updated_by_user_id`, timestamps
- Action table `module_meeting_actions`:
  - `meeting_note_id`, `description`, `owner_user_id`, `due_date`, `status`
  - `linked_task_entry_id` (optional)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetDefinitions(int $scopeId, array $config = [])`
  - `overview`: meeting totals grouped by status and type
  - `open_actions`: active action items
  - `upcoming_followups`: follow-up dates within the next 14 days
- Compatibility methods:
  - `getWidgetView()` returns `null`
  - `getWidgetData()` returns merged data payload
- `getDefaultConfig()`:
  - `max_entries` default `5`