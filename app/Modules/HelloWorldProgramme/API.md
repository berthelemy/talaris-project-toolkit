# HelloWorld Programme Module API

## Module Metadata
- Slug: `hello_world_programme`
- Scope: `programme`
- Version: `1.0.0`
- Widget permission: `module.hello_world_programme.widget.read`

## HTTP Routes
Defined in `Config/routes.php`.

- `GET /programmes/{programmeId}/modules/hello-world`
  - Controller: `HelloWorldProgrammeController::index`

- `POST /programmes/{programmeId}/modules/hello-world`
  - Controller: `HelloWorldProgrammeController::create`

- `POST /programmes/{programmeId}/modules/hello-world/entries/{entryId}/autosave`
  - Controller: `HelloWorldProgrammeController::autosave`

## Autosave API Contract
### Request
- `message` (required, max 500 chars)
- `last_updated_at` (optional optimistic lock value)

### Responses
- `200` success with updated entry and CSRF hash
- `401` unauthorized
- `403` forbidden
- `404` entry not found
- `409` optimistic lock conflict
- `422` validation error
- `423` locked by another actor

## Widget Public Interface
Implemented by `Widgets/ModuleWidget.php`:
- `getWidgetView(int $scopeId)`
- `getWidgetData(int $scopeId, array $config = [])`
- `getDefaultConfig()` with `max_entries` default `5`
