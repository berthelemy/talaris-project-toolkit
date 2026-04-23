# Server Requirements

## Runtime

- PHP 8.2 or newer
- Composer 2.x
- MySQL or MariaDB

## Required PHP Extensions

- `intl`
- `mbstring`
- `json`
- `mysqli` or `pdo_mysql`
- `curl`
- `openssl`
- `ctype`
- `filter`
- `hash`

## Recommended Services

- Web server configured with document root set to `public/`
- TLS certificate for non-local environments
- SMTP service for password reset and future notification features

## File System

- Writable directories for runtime data under `writable/`
- Backups for database and uploaded files

## Operational Baseline

- Daily database backups
- Centralized logs for app and web server
- Environment-specific `.env` management
