---
name: security-audit-logging
description: "Use when: adding security controls, hardening data handling, and implementing audit logs for all changes with actor and timestamp."
---

# Security and Audit Logging

## Goal
Protect data and provide complete change traceability.

## Do
- Hash passwords with modern algorithms and never log secrets.
- Validate and sanitize input, and escape output by context.
- Record audit entries for create/update/delete actions with who, what, and when.
- Include before/after or diff-friendly change details when feasible.

## Checks
- Sensitive operations generate audit events.
- Security-critical configuration is not hardcoded.
- Log entries are queryable and linked to affected entities.
