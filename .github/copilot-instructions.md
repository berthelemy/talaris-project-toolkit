# Copilot Instructions for Talaris Project Toolkit

Use these repository skills whenever tasks match their scope. Choose the most specific skill and combine multiple skills when needed.

## Core Development Skills
- `.github/skills/php-codeigniter4-backend/SKILL.md`
- `.github/skills/codeigniter-architecture-patterns/SKILL.md`
- `.github/skills/psr12-code-quality/SKILL.md`
- `.github/skills/mysql-mariadb-modeling-locking/SKILL.md`
- `.github/skills/authentication-authorization-rbac/SKILL.md`
- `.github/skills/security-audit-logging/SKILL.md`

## Frontend and UX Skills
- `.github/skills/frontend-bootstrap5/SKILL.md`
- `.github/skills/mobile-first-responsive-design/SKILL.md`
- `.github/skills/ui-theming-admin-customization/SKILL.md`
- `.github/skills/i18n-en-fr-localization/SKILL.md`
- `.github/skills/autosave-live-persistence/SKILL.md`
- `.github/skills/accessibility-wcag22-aa/SKILL.md`

## Modules and Reporting Skills
- `.github/skills/module-plugin-architecture/SKILL.md`
- `.github/skills/module-internal-api-integration/SKILL.md`
- `.github/skills/dashboards-reporting/SKILL.md`
- `.github/skills/email-scheduling-notifications/SKILL.md`

## Quality and Delivery Skills
- `.github/skills/testing-unit-integration-system/SKILL.md`
- `.github/skills/deployment-local-shared-vps/SKILL.md`
- `.github/skills/docs-markdown-jekyll/SKILL.md`
- `.github/skills/gpl-open-source-compliance/SKILL.md`
- `.github/skills/project-management-raid-domain/SKILL.md`

## Working Rules
- Follow CodeIgniter 4 and PHP best practices.
- Enforce PSR-12 formatting for all PHP changes.
- Prefer secure defaults and include audit logging for data mutations.
- Keep features mobile-first, Bootstrap 5 compliant, and localization-ready.
- Build and review UI changes to meet WCAG 2.2 Level AA.
- Add or update automated tests (unit, integration, system) for meaningful behavioral changes.
- Update Markdown documentation when behavior, configuration, or module APIs change.
- Require `README.md` in every module directory; all new modules must include `README.md` with purpose, scope, key components, and links to module API docs.
- Ensure all PHP files include full PHPDoc annotations for classes, methods, and variables.
- After any code change, run all relevant tests to validate behavior and prevent regressions.
- After any database schema change (new/updated migration), run migrations in the active environment (`XDEBUG_MODE=off php spark migrate`) before validation or handoff, and report the migration result.
- Ensure that all modules include appropriate API documentation for their public interfaces, and update this documentation when APIs change.
