# Installation

This guide describes a clean local setup for the Talaris Project Toolkit.

The project name "Talaris" is derived from the Latin *talaria* (the winged sandals of Hermes), implying speed and communication.

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

## Verify Writable Permissions

Use these checks to confirm the runtime folders are writable by the web server user.

1. Inspect owner/group and mode:

```bash
ls -ld writable writable/cache writable/logs writable/session writable/uploads
```

2. Confirm the expected web server user inside the container (usually `www-data` for Apache):

```bash
ps -eo user,comm | grep -E "apache2|httpd|php-fpm"
```

3. Test write access as the web server user:

```bash
sudo -u www-data sh -c 'touch writable/cache/.perm-test && rm writable/cache/.perm-test'
sudo -u www-data sh -c 'touch writable/logs/.perm-test && rm writable/logs/.perm-test'
sudo -u www-data sh -c 'touch writable/session/.perm-test && rm writable/session/.perm-test'
sudo -u www-data sh -c 'touch writable/uploads/.perm-test && rm writable/uploads/.perm-test'
```

4. If any command fails, fix ownership and minimum safe permissions:

```bash
sudo chown -R www-data:www-data writable
sudo find writable -type d -exec chmod 775 {} \;
sudo find writable -type f -exec chmod 664 {} \;
```

If your web server runs as a different user/group, replace `www-data` with that account.

## Verify the Baseline

Run baseline checks:

```bash
composer ci
```

Expected result:
- PHP lint passes for `app/` and `tests/`.
- PHPUnit baseline suite executes successfully.
