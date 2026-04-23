---
name: authentication-authorization-rbac
description: "Use when: implementing login, session controls, role-based access control, and permissions across system, programme, project, and module contexts."
---

# Authentication, Authorization, and RBAC

## Goal
Enforce secure access with context-aware permissions.

## Do
- Implement username/email login flows with secure password verification.
- Enforce configurable password policy and session inactivity timeout.
- Model roles and permissions by scope: system, programme, project, module.
- Support users with multiple roles in the same context.

## Checks
- Every write action checks permission in the correct scope.
- Impersonation features are limited to authorized administrators and audited.
- Access denial responses do not leak sensitive information.
