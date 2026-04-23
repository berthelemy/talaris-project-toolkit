# Installation

This guide describes a clean local setup for the Pejen Project Toolkit.

## Prerequisites

- PHP 8.2+
- Composer 2+
- MySQL or MariaDB
- Web server pointing document root to `public/`

## Steps

1. Clone the repository.
2. Install dependencies:

```bash
composer install
```

3. Copy environment template:

```bash
cp env .env
```

4. Configure `.env` for your local database and base URL.
5. Ensure writable paths are writable by the web server user:
- `writable/cache/`
- `writable/logs/`
- `writable/session/`
- `writable/uploads/`
6. Run database migrations when project migrations are added:

```bash
php spark migrate
```

7. Start local development server:

```bash
php spark serve
```

8. Verify application is reachable in your browser.

## Verify the Baseline

Run baseline checks:

```bash
composer ci
```

Expected result:
- PHP lint passes for `app/` and `tests/`.
- PHPUnit baseline suite executes successfully.
