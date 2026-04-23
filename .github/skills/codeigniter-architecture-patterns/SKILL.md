---
name: codeigniter-architecture-patterns
description: "Use when: designing or refactoring feature architecture in CodeIgniter for maintainability, separation of concerns, and module consistency."
---

# CodeIgniter Architecture Patterns

## Goal
Keep the codebase maintainable with consistent project patterns.

## Do
- Follow a predictable feature layout across controllers, models, views, language files, and tests.
- Separate read models, write workflows, and cross-cutting concerns such as authorization and logging.
- Centralize shared behavior in base services/helpers instead of duplicating per module.
- Prefer explicit dependency boundaries over hidden global state.

## Checks
- Feature logic can be traced without jumping across unrelated layers.
- New code matches folder and naming conventions used by existing modules.
- Refactors preserve public routes and response formats unless explicitly changed.
