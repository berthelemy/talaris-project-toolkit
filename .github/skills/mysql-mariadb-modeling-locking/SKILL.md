---
name: mysql-mariadb-modeling-locking
description: "Use when: designing MySQL/MariaDB schema, migrations, and concurrency controls such as checkout/locking for records or modules."
---

# MySQL/MariaDB Modeling and Locking

## Goal
Design safe persistence with reliable concurrency behavior.

## Do
- Define normalized tables, keys, and indexes for common reads and writes.
- Use migrations for schema evolution and consistent environments.
- Implement module checkout/locking with ownership, context, and timestamps.
- Use transactions for multi-step writes that must stay consistent.

## Checks
- Lock acquisition and release rules are explicit (open, lock, unlock on logout/timeout).
- Conflict scenarios are handled with user-friendly responses.
- Auditable fields (created/updated/deleted by/at) are preserved where required.
