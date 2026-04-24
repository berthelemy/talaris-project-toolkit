# Database Migrations Strategy

This document defines the baseline migration approach for the toolkit.

## Principles

- All schema changes must be delivered via migrations.
- Migrations must be idempotent at deployment level (run once, tracked by framework).
- No manual schema changes in shared environments.
- Migration and code changes ship together in the same release.

## Naming Conventions

- Use descriptive migration names generated with Spark, for example:
  - `php spark make:migration CreateUsersTable`
  - `php spark make:migration AddLanguagePreferenceToUsers`
- Table names: snake_case plural (for example `project_modules`).
- Column names: snake_case with explicit intent (for example `locked_by_user_id`).
- Foreign keys: `<related_entity>_id`.
- Timestamp columns: `created_at`, `updated_at`, `deleted_at` where soft delete is needed.

## File Structure

- Store migrations in `app/Database/Migrations/`.
- Keep one migration focused on one schema change concern.

## Current Authentication Tables (Phase 2)

- `users`: identity and password hash records.
- `password_reset_tokens`: hashed reset token storage with expiry and single-use marker.
- `auth_audit_logs`: traceable auth events (login/logout/failures/reset actions).
- `auth_settings`: administrator-configurable password policy and timeout settings.

## Authoring Rules

- Always implement both `up()` and `down()` methods.
- Add indexes for high-frequency lookup fields.
- Prefer explicit constraints and defaults.
- Use transactions for data backfills where supported and safe.

## Execution

- Apply latest migrations:

```bash
php spark migrate
```

- Roll back latest batch when needed:

```bash
php spark migrate:rollback
```

- Refresh schema in local development only:

```bash
php spark migrate:refresh
```

## Environment Notes

- Local: frequent migration cycles are expected.
- Shared hosting: ensure CLI migration command availability during deployment.
- VPS: run migrations as a deployment step before switching traffic.
