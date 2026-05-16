# RAID Shared Internal API

## Purpose
`RaidShared` provides the common project RAID register CRUD stack used by:
- `risk_register_project`
- `assumptions_register_project`
- `issue_tracker_project`
- `dependencies_register_project`
- `decisions_register_project`

## Public Internal Controller Surface
Base class: `Controllers/BaseProjectRaidController.php`

Exposed action methods for wrapper controllers:
- `index(int $projectId)`
- `create(int $projectId)`
- `update(int $projectId, int $entryId)`
- `close(int $projectId, int $entryId)`

Wrapper controllers set module-specific static config:
- module slug
- display title key
- RAID entry type (`risk|assumption|issue|dependency|decision`)
- field map and table columns
- validation rules

## Data Model Contract
Persistence model: `app/Models/ModuleRaidEntryModel.php`

Relevant normalized fields include:
- core: `module_slug`, `scope_type`, `scope_id`, `entry_type`, `title`, `description`, `status`, `priority`
- ownership/dates: `owner_user_id`, `target_date`, `review_date`, `date_reported`, `reporter_user_id`, `decision_date`, `made_by_user_id`
- RAID extras: `impact_level`, `mitigation_actions`, `lessons_learned`, `closed_at`, `closed_by`

## Mutation Side Effects
- Audit events are written via `AuditLogger` for create/update/close.
- Widget cache invalidation occurs through `ModuleWidgetService` cache key invalidation for affected module/scope.

## Authorization
Controllers rely on project access and module capability checks inherited from the base application controller stack.
