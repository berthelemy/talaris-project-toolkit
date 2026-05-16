# Issue Tracker Project Module API

## Module Metadata
- Slug: `issue_tracker_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.issue_tracker_project.widget.read`

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/issue-tracker`
- `POST /projects/{projectId}/modules/issue-tracker`
- `POST /projects/{projectId}/modules/issue-tracker/{entryId}/update`
- `POST /projects/{projectId}/modules/issue-tracker/{entryId}/close`

All routes are served by `IssueTrackerController` via shared RAID behavior.

## Request Field Contract (create/update)
- Common RAID fields:
  - `title`
  - `description`
  - `owner_user_id`
  - `status` (`open|in_review|closed`)
  - `priority` (`low|medium|high|critical`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
- Issue-specific fields:
  - `date_reported` (`Y-m-d`)
  - `reporter_user_id`

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetView(int $scopeId)` -> issue widget view path
- `getWidgetData(int $scopeId, array $config = [])`
  - returns active/open issue entries for project scope
- `getDefaultConfig()`
  - `max_entries` default `5`
