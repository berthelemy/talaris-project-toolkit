---
name: module-plugin-architecture
description: "Use when: designing or implementing pluggable modules with shared structure, conventions, and lifecycle within the toolkit application."
---

# Module and Plugin Architecture

## Goal
Enable consistent, extensible modules across project and programme scopes.

## Do
- Define a standard module skeleton (config, routes, controller, model, views, tests).
- Keep module boundaries explicit and avoid tight coupling.
- Version module contracts and document expected capabilities.
- Provide installer and validation checks for module onboarding.

## Checks
- New modules can be created from template with minimal manual steps.
- Modules honor project coding conventions and test requirements.
- Scope support (programme/project) is explicit in module metadata.
