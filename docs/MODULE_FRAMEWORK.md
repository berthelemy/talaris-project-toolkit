# Module Framework Guide (Phase 6)

This document defines the baseline scaffold for pluggable modules in Talaris.

## Registry and Lifecycle

- Module metadata is persisted in `module_registry`.
- Each module has:
  - `slug` (unique identifier)
  - `name`
  - `scope_type` (`programme` or `project`)
  - `description`
  - `is_enabled` lifecycle flag
- Enable/disable changes are audited through `module_enabled` and `module_disabled` events.

## Standard Module Scaffold

Each module should include:

1. Registry entry and migration updates.
2. Scope controller:
   - Route handlers for module page and writes.
   - Scope authorization checks inherited from programme/project access policy.
   - Registry enabled-state guard before serving UI/API actions.
3. Persistence model(s) for module records.
4. Views:
   - Module page with list/create flows.
   - Scope-aware navigation from parent programme/project details page.
5. Localization keys in both English and French language files.
6. System tests for:
   - route access
   - persistence
   - disabled-module guard behavior

## Naming Conventions

- Controller names: `<Scope><ModuleName>Controller`.
  - Examples: `ProgrammeHelloWorldController`, `ProjectHelloWorldController`.
- Views: `app/Views/modules/<scope>_<module>.php`.
- Registry slugs: `<module_name>_<scope>`.
  - Examples: `hello_world_programme`, `hello_world_project`.
- Audit event names:
  - module lifecycle: `module_enabled`, `module_disabled`
  - module writes: `<module>_entry_created` or domain-specific action names

## Reference Modules

Phase 6 baseline reference modules:

- `hello_world_programme`: stores programme-scoped sample records.
- `hello_world_project`: stores project-scoped sample records.

Both modules persist records in `module_hello_world_entries` with explicit `scope_type` and `scope_id`.

## Language Catalog Decision

- Canonical framework/module UI strings remain in `app/Language/en/Module.php` and `app/Language/fr/Module.php`.
- Module-local `Language/en|fr/Module.php` files are retained as compatibility wrappers that delegate to the canonical shared catalog.
- This removes duplicate translation maintenance while preserving module folder conventions.

## Reusable Unit Test Template

- Base template: `tests/_support/Modules/ModuleUnitTestCase.php`
- Example implementation: `tests/unit/modules/ModuleUnitTemplateExampleTest.php`

Usage workflow:

1. Copy `ModuleUnitTemplateExampleTest.php` and rename the class for your module.
2. Extend `ModuleUnitTestCase`.
3. Replace sample metadata and add module-specific assertions.
4. Keep baseline checks for metadata shape and supported scope values.
