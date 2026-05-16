# HelloWorld Project Module API

## Module Metadata
- Slug: `hello_world_project`
- Scope: `project`
- Version: `1.0.0`
- Widget permission: `module.hello_world_project.widget.read`

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /projects/{projectId}/modules/hello-world`
  - Controller: `HelloWorldController::index`
  - Purpose: render module entries and lock/read-only state.

- `POST /projects/{projectId}/modules/hello-world`
  - Controller: `HelloWorldController::create`
  - Purpose: create a message entry.

- `POST /projects/{projectId}/modules/hello-world/entries/{entryId}/autosave`
  - Controller: `HelloWorldController::autosave`
  - Purpose: autosave entry message edits.

## Autosave API Contract
### Request
- `message` (required, max 500 chars)
- `last_updated_at` (optional optimistic lock value)

### Responses
- `200` success: `{ ok: true, entry, csrf_hash }`
- `401` unauthorized
- `403` forbidden
- `404` entry not found
- `409` conflict with current entry payload
- `422` invalid message
- `423` locked by another actor

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetView(int $scopeId)`
- `getWidgetData(int $scopeId, array $config = [])`
- `getDefaultConfig()` with `max_entries` default `5`
