---
title: Card - Remove Raid Shared Directory and Ensure Modules Are Self-Contained
status: Done
type: card
updated: 2026-05-16
blocked_by:
  - phase-09-raid-modules
---
# Card - Remove Raid Shared Directory and Ensure Modules Are Self-Contained

## Requirement Source
Architecture hardening requirement from module framework principles and standalone module rules.

## Embedded Requirement Content

### Purpose
Remove the shared RAID implementation directory and enforce a module-first architecture where each module contains all code required to run independently within the application.

This change is intended to:
- Eliminate hidden coupling through `App\Modules\RaidShared`.
- Improve maintainability and ownership boundaries.
- Align all project modules with the repository rule that modules are standalone and installable without external module internals.

### Problem Statement
Multiple modules currently depend on `App\Modules\RaidShared\Models\ModuleRaidEntryModel`, which creates a cross-module runtime dependency and weakens module isolation.

Known current consumers include:
- Risk Register Project
- Assumptions Register Project
- Decisions Register Project
- Dependencies Register Project
- Issue Tracker Project
- Tasks Register Project
- Meeting Notes Project

### Scope
This card covers project-scope RAID-style modules that currently import classes from `app/Modules/RaidShared`.

In-scope work:
1. Remove `app/Modules/RaidShared` from active runtime usage.
2. Move or recreate required model/controller logic inside each consuming module.
3. Update each module namespace/imports to local module paths only.
4. Preserve existing behavior, routes, lifecycle transitions, and widgets.
5. Update tests and docs to reflect module-local ownership.

Out-of-scope work:
1. Major UX redesign of module screens.
2. Semantic changes to RAID data model fields.
3. New cross-module API features unrelated to dependency removal.

### Functional Requirements

#### FR-1 Module Self-Containment
Each module must include all classes required for its operation within its own directory tree.

Minimum expected local ownership per module:
- Controller layer for module entry CRUD and lifecycle actions.
- Persistence model(s) used by that module.
- Widget data providers used by that module.
- Any module-specific validation/helper logic needed at runtime.

No module may import application classes from another module's internal namespace.

#### FR-2 Remove Shared RAID Directory
The `app/Modules/RaidShared` directory must be removed once all references are migrated.

Interim compatibility shims are allowed only if:
- They are temporary and documented in this card.
- They are removed before card closure.

#### FR-3 Namespace and Dependency Boundary Enforcement
All prior imports to `App\Modules\RaidShared\*` must be replaced with module-local classes, for example:
- `App\Modules\RiskRegisterProject\Models\...`
- `App\Modules\DecisionsRegisterProject\Models\...`
- `App\Modules\MeetingNotesProject\Models\...`

No remaining code in `app/`, `tests/`, or module docs may reference `App\Modules\RaidShared` after completion.

#### FR-4 Behavioral Parity
For each affected module, parity must be preserved for:
- Listing/filtering/sorting of entries.
- Create/update/close/delete operations.
- Module-specific field handling and validation logic.
- Audit logging events for data mutations.
- Widget aggregates and lists.

#### FR-5 Data Compatibility
Existing data in `module_raid_entries` must remain readable/writable without manual migration unless schema evolution is explicitly introduced.

If schema changes are required:
1. Add migration scripts.
2. Run migrations in active environment (`XDEBUG_MODE=off php spark migrate`).
3. Document result and rollback strategy in handoff notes.

#### FR-6 Documentation and Module Packaging
Each affected module must include/update `README.md` to describe:
- Its owned model/controller classes.
- Its public integration points.
- Any assumptions previously provided by RaidShared and now internalized.

Project card and module docs must clearly state that shared RAID internals are retired.

### Non-Functional Requirements

#### NFR-1 Code Quality and Standards
- PHP changes must follow PSR-12.
- Public classes and methods require PHPDoc annotations.
- Keep changes minimal and avoid unrelated refactors.

#### NFR-2 Security and Auditability
- Existing authorization checks must remain enforced.
- Audit logging must remain present for create/update/close/delete operations.
- No reduction in input validation or escaping protections.

#### NFR-3 Performance
- Query performance for module list pages and widgets must not regress materially.
- Existing index usage assumptions on `module_raid_entries` must remain valid.

### Implementation Requirements
1. Introduce module-local model classes in each affected module, either:
   - One dedicated model per module, or
   - A local abstract model inside each module namespace where justified.
2. Update controllers and widget providers to use local model classes.
3. Remove all `use App\Modules\RaidShared\...` imports.
4. Remove the `app/Modules/RaidShared` directory when no references remain.
5. Update automated tests for each affected module.
6. Update handoff/project docs referencing RaidShared paths.

### Testing Requirements

#### Automated Tests
At minimum, add/update tests that validate:
1. Module page load and authorization checks.
2. Create/update/close/delete behavior per module.
3. Widget data generation still functions with local models.
4. No fatal errors from missing RaidShared namespace/classes.

#### Verification Commands
Run relevant suites and checks for changed modules, then report outcomes in the delivery note.

Required validation:
1. Targeted unit/integration tests for affected modules.
2. Any relevant system/e2e coverage touching RAID module flows.
3. Search verification showing no remaining RaidShared references in runtime code.

### Acceptance Criteria
1. No runtime code references `App\Modules\RaidShared`.
2. `app/Modules/RaidShared` is removed from repository.
3. Each affected module runs with its own model/controller internals.
4. CRUD + lifecycle + widget behavior remains functionally equivalent.
5. Audit logs still emit for module entry mutations.
6. Updated module READMEs are present for all touched modules.
7. Tests covering changed behavior pass.
8. Documentation/handoff references to RaidShared are updated or archived.

### Risks and Mitigations
- Risk: duplicated code across modules increases maintenance overhead.
  Mitigation: define a shared pattern/template in docs, but keep runtime classes module-local.

- Risk: subtle field-handling regressions during model split.
  Mitigation: add parity tests for create/update payload mapping and lifecycle transitions.

- Risk: stale doc references mislead future work.
  Mitigation: include a doc reference sweep as part of completion checklist.

## Definition of Done
- Requirements in this card are fully implemented and verified.
- `app/Modules/RaidShared` no longer exists.
- All affected modules are self-contained and pass relevant tests.
- Documentation and READMEs are updated to reflect self-contained architecture.
- Card status can move to `Done` once implementation evidence is attached.
