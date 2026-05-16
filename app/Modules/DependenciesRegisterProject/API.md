# Dependencies Register Project Module API

## Module Metadata
- Slug: `dependencies_register_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.dependencies_register_project.widget.read`

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/dependencies-register`
- `POST /projects/{projectId}/modules/dependencies-register`
- `POST /projects/{projectId}/modules/dependencies-register/{entryId}/update`
- `POST /projects/{projectId}/modules/dependencies-register/{entryId}/close`

All routes are served by `DependenciesRegisterController` via shared RAID behavior.

## Request Field Contract (create/update)
- Common RAID fields:
  - `title`
  - `description`
  - `owner_user_id`
  - `status` (`open|in_review|closed`)
  - `priority` (`low|medium|high|critical`)
  - `target_date` (`Y-m-d`)
  - `review_date` (`Y-m-d`)
- Dependency-specific fields:
  - `impact_level` (`low|medium|high`)

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetView(int $scopeId)` -> dependency widget view path
- `getWidgetData(int $scopeId, array $config = [])`
  - returns medium/high impact dependencies for project scope
- `getDefaultConfig()`
  - `max_entries` default `5`
