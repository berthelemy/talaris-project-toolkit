# Configuration

This page lists the minimum configuration expected across environments.

## Environment Strategy

- Keep secrets and environment-specific values in `.env`.
- Never commit secret values.
- Use different DB credentials per environment.

## Required Local Settings

Update `.env` values for:
- `app.baseURL`
- database connection (`database.default.*`)
- mail transport values for password-reset workflows

## Session and Security Baseline

- Configure session timeout to align with security policy.
- Enable secure cookie settings for non-local environments.
- Keep debug mode off outside local development.

## Authentication Configuration (Phase 2)

- Run `php spark migrate` to create authentication tables.
- Configure outgoing email settings in `.env` so password reset links can be delivered.
- Administrator-managed authentication settings are stored in `auth_settings` and include:
	- password minimum length and complexity toggles
	- session inactivity timeout (seconds)
	- password reset token TTL (minutes)
- Authentication audit events are stored in `auth_audit_logs`.

## File and Directory Permissions

- Web server must have write access only to `writable/`.
- Do not grant write access to `app/`, `public/`, or `vendor/`.

## Deployment Profiles

### Local Development
- Use `php spark serve` or a local virtual host.
- Keep detailed error output enabled.

### Shared Hosting
- Point web root to `public/`.
- Confirm required PHP extensions are available.
- Verify background scheduling constraints for future report emails.

### VPS
- Use managed service stack (Nginx/Apache + PHP-FPM + MySQL/MariaDB).
- Configure process supervisor and log rotation.
- Enforce TLS and restricted firewall rules.
