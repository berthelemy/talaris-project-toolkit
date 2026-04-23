---
name: module-internal-api-integration
description: "Use when: defining or consuming internal APIs that allow modules to read and update each other safely."
---

# Module Internal API Integration

## Goal
Provide reliable cross-module data exchange with stable internal APIs.

## Do
- Define clear request/response contracts and error semantics.
- Apply authorization checks before cross-module reads/writes.
- Protect APIs with validation, idempotency where needed, and logging.
- Document each module API in docs with examples.

## Checks
- API changes include backward compatibility notes or migration paths.
- Integration points are covered by integration tests.
- Failure modes are explicit and observable.
