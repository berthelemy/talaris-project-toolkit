# Module Internal API and Autosave Contract

This document defines the internal module API contract introduced in Phase 7.

## Internal Service Contract

Internal module integration must use `App\Libraries\Modules\ModuleInternalApiService`.

Methods:

- `read(string $moduleSlug, string $resource, array $query, int $actorId): array`
- `create(string $moduleSlug, string $resource, array $data, int $actorId): array`
- `update(string $moduleSlug, string $resource, int $id, array $data, int $actorId): array`

There are no public HTTP routes for the internal module API.

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

- `module_internal_api_read`
- `module_internal_api_write`
- `autosave_update`
- `module_lock_acquired`
- `module_lock_denied`
- `module_lock_released`

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
- `423` when module context is locked by another editor
- `422` for validation errors
- `401/403/404` for authentication/authorization/not found paths

## Locking Behavior (Phase 8)

- Lock scope is module context (`module_slug`, `scope_type`, `scope_id`).
- Authorized editors acquire/refresh lock on module open.
- Second editor sees read-only mode and receives lock-owner guidance.
- Autosave and internal API write operations return `423` while lock is held by another user.
- Locks are released on logout, inactivity timeout, expiry cleanup, or administrator recovery action from `/modules`.

## Frontend Integration

`public/js/autosave.js` provides:

- debounced updates
- save/error/conflict status messaging
- conflict recovery by loading server state
