---
name: testing-unit-integration-system
description: "Use when: creating or updating test coverage across unit, integration, and system/end-to-end levels for application features and modules."
---

# Unit, Integration, and System Testing

## Goal
Provide layered automated test coverage with clear confidence boundaries.

## Test Layers
- Unit tests: isolate classes/functions and edge cases with mocks/stubs.
- Integration tests: verify DB, services, internal APIs, and module interactions.
- System tests: validate user workflows end-to-end through HTTP/UI boundaries.

## Do
- Add tests for both success paths and failure/authorization cases.
- Cover locking behavior, autosave behavior, and audit logging side effects.
- Keep fixtures deterministic and teardown clean.
- Name tests by behavior, not implementation detail.

## Checks
- New features include at least one test at the most appropriate layer.
- Regressions include a failing test before the fix where feasible.
- Test suite can run in CI without environment-specific hacks.
