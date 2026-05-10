# Module Internal API and Autosave Contract

This document defines the internal module API contract introduced in Phase 7.

## Endpoints

- `GET /api/modules/{moduleSlug}/{resource}`
- `POST /api/modules/{moduleSlug}/{resource}`
- `PUT /api/modules/{moduleSlug}/{resource}/{id}`

## Supported Resource

Current implementation supports:

- `resource = entries`

## Required Scope Parameters

- `scope_type`: `programme` or `project`
- `scope_id`: numeric identifier for the scope record

## Authorization Rules

- Read access checks are enforced by `ModuleApiAuthorizationService::canRead()`.
- Write access checks are enforced by `ModuleApiAuthorizationService::canWrite()`.
- Ownership and role-based permissions are validated using RBAC.

## Audit Events

- `module_api_read`
- `module_api_write`
- `autosave_update`

Each event captures actor identity and scope metadata.

## Autosave Behavior

Hello World programme and project modules expose autosave endpoints:

- `POST /programmes/{id}/modules/hello-world/entries/{entryId}/autosave`
- `POST /projects/{id}/modules/hello-world/entries/{entryId}/autosave`

Payload:

- `message`: updated field value
- `last_updated_at`: optimistic concurrency token

Response behavior:

- `200` on successful save
- `409` for concurrency conflict
- `422` for validation errors
- `401/403/404` for authentication/authorization/not found paths

## Frontend Integration

`public/js/autosave.js` provides:

- debounced updates
- save/error/conflict status messaging
- conflict recovery by loading server state
