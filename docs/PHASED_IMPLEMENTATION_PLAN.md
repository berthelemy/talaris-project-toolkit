# Talaris Project Toolkit: Phased Implementation Plan

The project name "Talaris" is derived from the Latin *talaria* (the winged sandals of Hermes), implying speed and communication.

This plan is organized as two-week phases (sprints). Each phase includes:
- A delivery checklist.
- Manual acceptance testing instructions for phase sign-off.

## Planning Assumptions

- Sprint length is 2 weeks.
- Team has access to dev, test, and staging environments.
- CI pipeline runs automated tests on each merge request.
- Product owner signs off each phase via manual acceptance tests.

## Global Definition of Done (applies to every phase)

- [ ] Code follows CodeIgniter 4 conventions and PSR-12 style.
- [ ] Security checks and audit logging are implemented for new mutations.
- [ ] Unit/integration/system tests are added or updated for changed behavior.
- [ ] English and French strings are localized where UI text is introduced.
- [ ] Mobile-first responsive behavior is verified.
- [ ] WCAG 2.2 Level AA checks are completed for new/changed UI.
- [ ] Docs are updated in `docs/` for user-visible or API-visible changes.

## Phase 1 (Weeks 1-2): Foundation and Environments

### Objectives
- Establish baseline project structure and engineering guardrails.
- Enable local development and initial CI checks.

### Delivery Checklist
- [x] Confirm baseline CodeIgniter 4 app bootstraps cleanly.
- [x] Configure environment templates for local/shared hosting/VPS.
- [x] Add coding standards checks and test runner baseline.
- [x] Define initial DB migration strategy and naming conventions.
- [x] Create starter docs pages: installation, configuration, server requirements.

### Manual Acceptance Testing
1. Clone repository and install dependencies on a clean machine.
2. Configure environment values and start the app.
3. Open homepage in browser and confirm HTTP 200 response.
4. Run test command and confirm baseline suite executes.
5. Review docs pages and verify installation steps are reproducible.

### Manual Acceptance Result (2026-04-24)
- Environment: Local dev container.
- Timestamp (UTC): 2026-04-24 19:27:48.
- Dependency install step: `composer install --no-interaction --no-progress` completed successfully.
- Environment setup step: `.env` created from `env` template.
- App startup check: `curl http://localhost` returned HTTP 200.
- Baseline checks: `XDEBUG_MODE=off composer ci` passed (5 tests, 6 assertions).
- Documentation reproducibility check: `docs/INSTALLATION.md`, `docs/CONFIGURATION.md`, `docs/SERVER_REQUIREMENTS.md`, and `docs/DATABASE_MIGRATIONS.md` are present and non-empty.
- Blockers found: None.

### Exit Criteria
- [x] New developer can run the app in under 30 minutes using docs only.

## Phase 2 (Weeks 3-4): Authentication Core and Session Security

### Objectives
- Implement secure login foundations and session inactivity timeout.

### Delivery Checklist
- [x] Username/password authentication implemented.
- [x] Password policy is configurable by administrator settings.
- [x] Session inactivity timeout is configurable and enforced.
- [x] Password reset flow via email token is implemented.
- [x] Audit logs created for login, logout, failed login, password reset events.

### Manual Acceptance Testing
1. Create a test user with compliant password.
2. Log in with valid credentials and verify dashboard access.
3. Attempt logins with invalid password and verify denial + logged event.
4. Stay idle until timeout threshold and confirm forced logout.
5. Trigger password reset email, complete reset, and log in with new password.
6. Review audit log records for all above actions.

### Manual Acceptance Result (2026-05-10)
- Environment: Local dev container.
- Timestamp (UTC): 2026-05-10 11:37:02.
- Test user creation: Covered by `AuthSystemTest` fixture using compliant password `StrongPass!123`.
- Valid login and dashboard access: `tests/system/AuthSystemTest.php` confirms redirect to `/dashboard` and successful `login` audit event.
- Invalid login denial: `tests/system/AuthSystemTest.php` confirms failed login attempt is denied and logged as `login` with `failed` status.
- Session inactivity timeout: `tests/system/AuthSystemTest.php` updates timeout policy to 60 seconds, confirms redirect to `/login`, and records `session_timeout_logout`.
- Password reset flow: `tests/system/AuthSystemTest.php` confirms reset token creation, `password_reset_requested` audit logging, password update, and successful reset completion audit event.
- Email delivery path: development SMTP defaults are configured for Mailpit in `app/Config/Email.php`; local inbox inspection requires a devcontainer rebuild so the `mailpit` service is started.
- Baseline checks: `XDEBUG_MODE=off composer ci` passed successfully (15 tests, 40 assertions).
- Blockers found: None.

### Exit Criteria
- [x] Authentication and timeout behavior match configured policies.

## Phase 3 (Weeks 5-6): RBAC and User Management

### Objectives
- Deliver role-based authorization and user profile management.

### Delivery Checklist
- [x] Implement predefined roles: Administrator, Programme manager, Project manager, Team member, Stakeholder.
- [x] Support role assignment at system/programme/project scope.
- [x] Support multiple roles per user in a context.
- [x] Implement user profile updates including avatar, description, language preference.
- [x] Enforce current-password requirement for password change in profile.
- [x] Add impersonation capability for administrators with strict audit logging.

### Manual Acceptance Testing
1. Create users representing each predefined role.
2. Assign scoped roles and verify access boundaries per role.
3. Confirm a user with two roles receives union of allowed actions.
4. Edit profile fields and confirm persistence.
5. Attempt password change without current password and confirm rejection.
6. Perform admin impersonation and verify audit trail includes actor and target user.

### Manual Acceptance Result (2026-05-10)
- Environment: Local dev container.
- Timestamp (UTC): 2026-05-10 12:23:54.
- Predefined roles and scoped assignments: `tests/database/RbacServiceDatabaseTest.php` confirms system/project scope assignment behavior and role/permission resolution.
- Multiple roles in a context: `tests/database/RbacServiceDatabaseTest.php` confirms union permissions for a user holding Team member and Stakeholder roles in the same project.
- Profile updates: `tests/system/ProfileSystemTest.php` confirms persistence of language preference, description, and avatar path with `profile_updated` audit logging.
- Current-password enforcement: `tests/system/ProfileSystemTest.php` confirms password change is rejected when current password is invalid and accepted when valid, with audit events.
- Administrator impersonation: `tests/system/ImpersonationSystemTest.php` confirms authorized start/stop impersonation, non-admin denial, and audit events for started/stopped/denied paths.
- Baseline checks: `XDEBUG_MODE=off composer ci` passed successfully (25 tests, 95 assertions).
- Blockers found: None.

### Exit Criteria
- [x] Authorization checks consistently block unauthorized actions.

### Phase 3 Enhancement Backlog: Admin User and Role Management Interface

#### Objectives
- Provide administrators a complete interface to create, read, update, and delete users.
- Enable administrators to assign and revoke roles across system/programme/project scopes.

#### Delivery Checklist
- [x] Admin users list page with search/filter by username, email, status, and role.
- [x] Create user flow (username, email, initial password policy validation, active flag).
- [x] Edit user flow (profile fields, active/inactive status, optional password reset trigger).
- [x] Delete/deactivate user flow with safeguards for last active administrator.
- [x] Role assignment UI for system/programme/project scopes with multi-role support.
- [x] Role revoke flow with permission boundary checks.
- [x] Audit logging for all user and role mutations (actor, target, before/after metadata).
- [x] System tests covering admin CRUD boundaries and scoped role management.

#### Manual Acceptance Testing
1. As an administrator, open user management and create a new active user.
2. Edit that user and update profile/status fields; verify persisted values.
3. Assign multiple roles to the user across system and project scopes.
4. Confirm effective permissions match assigned roles and scopes.
5. Revoke one role and confirm permission reduction is immediate.
6. Attempt restricted actions as non-admin and confirm access is denied.
7. Delete or deactivate a user and confirm safety rules and audit entries.

#### Manual Acceptance Result (2026-05-10)
- Environment: Local dev container.
- Timestamp (UTC): 2026-05-10 15:12:10.
- Admin user management UI: Added `/users` list with username/email/status/role filters, create flow, edit flow, and deactivate flow.
- Last-active-admin safeguard: Enforced for deactivate and administrator role revoke actions.
- Scoped roles: Added assign/revoke flows for system/programme/project scopes with scope existence validation.
- Audit logging: Added events for user CRUD governance (`user_admin_created`, `user_admin_updated`, `user_admin_deactivated`, `user_admin_denied`) and role revoke (`role_revoked`) with actor/target metadata.
- Automated checks: `XDEBUG_MODE=off vendor/bin/phpunit --do-not-fail-on-warning tests/system/UserManagementSystemTest.php` passed in-session (coverage warning only).
- Blockers found: None.

#### Exit Criteria
- [x] Administrators can fully manage users and roles through UI with complete auditability.

## Phase 4 (Weeks 7-8): Programmes, Projects, and Core Domain Model

### Objectives
- Implement core entities and ownership relations.

### Delivery Checklist
- [x] CRUD for Programmes and Projects with ownership semantics.
- [x] Programme-to-project linking implemented.
- [x] Project and Programme manager assignments implemented.
- [x] Validation rules and business constraints are enforced.
- [x] Audit logging enabled for all domain mutations.

### Manual Acceptance Testing
1. Create programme and multiple projects.
2. Link/unlink projects to programme and verify reflected state.
3. Validate role-restricted CRUD behavior for programme/project managers.
4. Attempt invalid payloads and confirm validation errors are shown.
5. Confirm all create/update/delete actions are present in audit logs.

### Manual Acceptance Result (2026-05-10)
- Environment: Local dev container.
- Timestamp (UTC): 2026-05-10 13:58:00.
- Programme/project CRUD: Verified via list pages, modal edit pages, and detail pages (`/programmes/:id`, `/projects/:id`) with owner-based authorization checks.
- Linking behavior: Verified link/unlink from project edit page and reflected state in linked-programmes and linked-projects sections.
- Role boundaries: Confirmed by automated domain/system tests and RBAC permission checks in controller flows.
- Validation handling: Confirmed via create/update validation rules and error surfacing on forms.
- Audit coverage: Confirmed events for create/update/delete/link/unlink and manager assignment flows.
- Baseline checks: `XDEBUG_MODE=off composer ci` and focused system tests passed in-session (coverage warning only).
- Blockers found: None.

### Exit Criteria
- [x] Domain model supports required programme/project workflows.

## Phase 5 (Weeks 9-10): Localization and Theming

### Objectives
- Enable multilingual experience and admin branding customization.

### Delivery Checklist
- [x] English and French language packs wired for key UI flows.
- [x] Browser language detection implemented with English fallback.
- [x] Language selector implemented with essential cookie persistence.
- [x] Admin theme settings added: logo, heading font, body font, color scheme.
- [x] Contrast and readability validations included in theme handling.

### Implementation Progress (2026-05-10)
- Added global locale resolution with this precedence: language cookie override, user profile preference, browser `Accept-Language`, then English fallback.
- Added authenticated header language selector (`en`/`fr`) with essential cookie persistence.
- Added system coverage in `tests/system/LocalizationSystemTest.php` for French browser locale detection, unsupported locale fallback to English, and cookie persistence behavior.
- Added admin theme settings at `/theme` with RBAC permission `system.theme.manage`, logo upload/removal, heading/body font selection, and color scheme persistence.
- Added contrast/readability validation rules (text/background, primary/background, secondary/background) before theme updates are saved.
- Added system coverage in `tests/system/ThemeSettingsSystemTest.php` for authorized updates, unauthorized denial, audit logging, and contrast validation failure paths.
- Completed a key-flow EN/FR UI string sweep for authenticated pages by replacing remaining hardcoded select placeholders in profile and project-linking flows with language keys.

### Manual Acceptance Testing
1. Open app in browser configured to French and verify French UI.
2. Open app in unsupported locale and verify English fallback.
3. Change language in selector and confirm persistence after logout/login.
4. Apply custom logo/fonts/colors as administrator.
5. Verify updated theme appears across major pages.
6. Run quick contrast spot-check on primary text, links, and buttons.

### Manual Acceptance Result (2026-05-10)
- Environment: Local dev container.
- Timestamp (UTC): 2026-05-10 14:21:44.
- French locale behavior: Verified by `tests/system/LocalizationSystemTest.php` (`testFrenchBrowserLocaleRendersFrenchLoginStrings`) and manual UI checks on authenticated pages.
- Unsupported locale fallback: Verified by `tests/system/LocalizationSystemTest.php` (`testUnsupportedBrowserLocaleFallsBackToEnglish`).
- Language persistence: Verified cookie-based persistence via `tests/system/LocalizationSystemTest.php` (`testLanguageSelectorCookiePersistsAcrossSignedOutAndSignedInScreens`) and header selector flow.
- Theme configuration: Verified admin `/theme` page supports logo, heading/body fonts, and color scheme updates with role restriction (`system.theme.manage`).
- Contrast/readability checks: Verified validation prevents low-contrast combinations in `tests/system/ThemeSettingsSystemTest.php` (`testContrastValidationRejectsInaccessibleColors`).
- Baseline checks: `XDEBUG_MODE=off vendor/bin/phpunit --do-not-fail-on-warning tests/system/ProfileSystemTest.php tests/system/LocalizationSystemTest.php tests/system/ThemeSettingsSystemTest.php` passed in-session (coverage warning only).
- Blockers found: None.

### Exit Criteria
- [x] Language and branding preferences persist correctly across sessions.

## Phase 6 (Weeks 11-12): Module Framework and Hello World Modules

### Objectives
- Establish pluggable module architecture and baseline sample modules.

### Delivery Checklist
- [x] Standard module scaffold defined and documented.
- [x] One sample Programme-level Hello World module implemented.
- [x] One sample Project-level Hello World module implemented.
- [x] Module registration and enable/disable mechanisms implemented.
- [x] Module unit test template included for all new modules.
- [x] Backlog: Create full API and module documentation using phpDocumentor (https://phpdoc.org/).

### Implementation Progress (2026-05-10)
- Added module registry persistence migration with lifecycle flag (`module_registry`) and sample entry persistence table (`module_hello_world_entries`).
- Added module registry service and admin module management UI at `/modules` with enable/disable actions.
- Added audit logging for module lifecycle changes (`module_enabled`, `module_disabled`) and Hello World record creation events.
- Implemented programme-scoped and project-scoped Hello World modules at:
	- `/programmes/:id/modules/hello-world`
	- `/projects/:id/modules/hello-world`
- Added scope-aware launch cards on programme/project details pages and disabled-module access guards.
- Added EN/FR localization pack for module UI (`app/Language/en/Module.php`, `app/Language/fr/Module.php`).
- Added module framework scaffold documentation in `docs/MODULE_FRAMEWORK.md`.
- Added initial system coverage in `tests/system/ModuleFrameworkSystemTest.php` for lifecycle toggles, scope persistence, and disabled-module blocking.
- Added reusable unit test template scaffolding for modules in `tests/_support/Modules/` with an example in `tests/unit/modules/`.

### Manual Acceptance Testing
1. Install and enable both Hello World modules.
2. Verify each appears only in its intended scope.
3. Create records through module UI and confirm persistence.
4. Disable a module and confirm UI/API access is removed.
5. Run module-specific tests and verify pass status.

### Exit Criteria
- [x] Team can build new modules from scaffold with consistent structure.

### Phase 6 Outstanding Backlog (from handoff follow-up)

- [x] Enforce module permission boundaries for widgets (RBAC-aware per module and scope).
- [x] Add widget data/render caching strategy for heavier modules.
- [x] Add administrator-controlled widget ordering on programme/project pages.
- [x] Add additional reference modules (for example Risk Register, Issue Tracker) to validate scalability.
- [x] Add module version metadata support in registry and discovery.
- [x] Add module dependency declarations and dependency validation.
- [x] Add widget usage metrics for observability and adoption tracking.
- [x] Add module-exposed widget configuration options for end users/admins.
- [x] Improve widget failure visibility in development mode and provide admin-facing failure signals.
- [x] Add tests for module directory-to-slug discovery with varied naming conventions.

## Phase 7 (Weeks 13-14): Internal Module APIs and Autosave

### Objectives
- Enable module-to-module integration and live persistence.

### Delivery Checklist
- [x] Internal API contract format defined and documented.
- [x] Authentication/authorization checks added to internal APIs.
- [x] Autosave endpoints and frontend behavior implemented for editable module fields.
- [x] Error handling for autosave failures and concurrency conflicts implemented.
- [x] Audit logs include autosave and internal API mutation events.

### Implementation Progress (2026-05-10)
- Added module metadata discovery via `module.json` files and synchronized version/dependency/config fields into `module_registry`.
- Added dependency validation before module enablement and lifecycle-safe widget cache invalidation.
- Added widget RBAC permission checks, data/HTML caching, usage metrics, and failure logging signals.
- Added admin controls on `/modules` for widget display order and module widget config (`max_entries`).
- Added two additional project reference modules (`risk_register_project`, `issue_tracker_project`) with routes, controllers, views, and widgets.
- Added internal module API endpoints under `/api/modules/...` with authorization service and audit events.
- Added autosave endpoints for Hello World programme/project entries, including optimistic conflict handling (`409`).
- Added frontend autosave client (`public/js/autosave.js`) with debounced save, status messaging, and conflict refresh behavior.
- Added tests: `tests/system/ModuleApiSystemTest.php`, `tests/system/ModuleAutosaveSystemTest.php`, and `tests/unit/modules/DirectoryToSlugTest.php`.
- Added API/autosave contract documentation in `docs/MODULE_INTERNAL_API.md`.

### Manual Acceptance Testing
1. Trigger an internal API read from Module A into Module B context.
2. Trigger an authorized internal API update and verify audit entry.
3. Edit a form field with autosave and confirm save indicator appears.
4. Simulate network interruption and confirm autosave error state is shown.
5. Retry after reconnect and confirm successful persistence.

### Exit Criteria
- [x] Cross-module reads/updates are reliable and secure.

## Phase 8 (Weeks 15-16): Concurrency Locking and Checkout Flow

### Objectives
- Prevent edit conflicts with module-level checkout locking.

### Delivery Checklist
- [x] Locking model implemented for module data by context and user.
- [x] Lock acquisition on module open for authorized editors.
- [x] Lock denial message for second editor with clear guidance.
- [x] Lock release on logout and timeout.
- [x] Administrative lock visibility and recovery tooling added.

### Implementation Progress (2026-05-10)
- Added `module_edit_locks` persistence with unique context lock key (`module_slug`, `scope_type`, `scope_id`) and lock ownership/expiry fields.
- Added `ModuleLockService` for acquire/deny, expiry cleanup, user-scope release on logout/timeout, and admin force release flows.
- Enforced lock checks on module write paths:
	- Hello World autosave endpoints now return `423` when lock is held by another editor.
	- Internal module API write endpoints now return `423` on lock denial.
- Added module page checkout behavior on open for authorized editors and read-only fallback with lock-owner guidance for second editor.
- Added admin lock visibility/recovery section on `/modules` with active lock table and explicit release action.
- Added EN/FR localization strings for lock and read-only UX states.
- Added system test coverage for lock denial and release behavior in:
	- `tests/system/ModuleAutosaveSystemTest.php`
	- `tests/system/ModuleApiSystemTest.php`
	- `tests/system/AuthSystemTest.php`
	- `tests/system/ModuleFrameworkSystemTest.php`

### Manual Acceptance Testing
1. User A opens editable module and acquires lock.
2. User B opens same module/context and is blocked from editing.
3. Confirm User B can still view read-only data where permitted.
4. User A logs out; User B retries and can now edit.
5. Repeat with timeout-based logout and verify lock release.
6. Confirm lock events are present in audit logs.

### Exit Criteria
- [ ] No concurrent write conflicts are possible in locked module contexts.

## Phase 9 (Weeks 17-18): RAID Modules (Risk, Assumptions, Issues, Dependencies)

### Objectives
- Deliver first production modules based on project management RAID practices.

### Delivery Checklist
- [ ] Risk register module implemented.
- [ ] Assumptions register module implemented.
- [ ] Issues register module implemented.
- [ ] Dependencies register module implemented.
- [ ] Shared lifecycle/status/owner/date patterns standardized across RAID modules.
- [ ] Role-aware visibility and edit controls applied.

### Implementation Progress (2026-05-11)
- Added shared RAID persistence table `module_raid_entries` with common governance fields: title, description, owner, status, priority, target/review dates, closed timestamp, and audit actor metadata.
- Added production registry migration updates for all four project RAID modules:
	- `risk_register_project`
	- `issue_tracker_project`
	- `assumptions_register_project`
	- `dependencies_register_project`
- Refactored Risk and Issue controllers onto shared RAID project controller behaviors (create, update, close, filter/search/sort).
- Added new production modules for Assumptions and Dependencies with routes, controllers, widgets, metadata, and project detail-page entry points.
- Standardized role-aware behavior:
	- Read access uses project scope authorization checks.
	- Create/update/close mutation paths require project write permission.
	- Read-only mode messaging is shown to non-writers.
- Added standardized lifecycle/status/owner/date UX and query controls on RAID module pages.
- Added audit logging for RAID mutations: `raid_entry_created`, `raid_entry_updated`, and `raid_entry_closed`.
- Added system test coverage in `tests/system/RaidModulesSystemTest.php` for:
	- CRUD close workflow on Risk module.
	- Role-based mutation restriction for read-only users.
	- Assumptions and Dependencies module record creation.
	- Filter/search/sort behavior on Issue module.
	- Decisions module record creation.
- Added risk-specific fields and computed governance priority:
	- `mitigation_actions`
	- `impact` (high/medium/low)
	- `likelihood` (high/medium/low)
	- `priority` auto-calculated from impact x likelihood.
- Added assumptions-specific field `impact_if_not_valid`.
- Added a new project module `decisions_register_project` with fields for description, decision date, and decision owner (`made_by_user_id`).
- Added direct return navigation from module pages back to owning project/programme context.
- Added DataTables.net integration for sortable/searchable table rendering on module/admin/project/programme table views.
- Added inline quick-create forms inside module widgets on project/programme pages, with direct links from each widget to the full module page.
- Fixed widget visibility fallback so project/programme readers are not blocked when explicit widget permission assignment is absent.
- Validation results:
	- `XDEBUG_MODE=off php spark migrate` passed.
	- `XDEBUG_MODE=off composer ci` passed (60 tests, 298 assertions).

### Manual Acceptance Testing
1. Create, edit, and close records in each RAID module.
2. Verify each record supports owner, status, and due/review metadata.
3. Validate role-based restrictions for create/update/delete actions.
4. Verify search/filter/sort behavior for operational use.
5. Confirm changes are captured in audit logs with actor and timestamp.

### Exit Criteria
- [ ] RAID modules are usable for day-to-day project governance.

## Phase 10 (Weeks 19-20): Desktop-Oriented UI Overhaul and Navigation

### Objectives
- Implement the major UI redesign to make the app feel desktop-oriented while preserving mobile-first and accessibility commitments.

### Delivery Checklist
- [ ] Header updated to include logo, site title, and navbar.
- [ ] Navbar structure implemented: Programmes, Projects, Admin (Users/Modules/Theme), Profile, language selector, and sign in/sign out.
- [ ] Programmes list (`/programmes`) redesigned to card-based navigation with clickable cards.
- [ ] Programme detail (`/programmes/:id`) redesigned with computed programme status and clickable related-project cards.
- [ ] Projects list (`/projects`) redesigned to card-based navigation with programme filter including unlinked projects.
- [ ] Project detail (`/projects/:id`) redesigned into hideable navigation panel (2/12) + main content panel.
- [ ] Project overview section implemented with module widgets and quick actions as defined in `docs/UI_CHANGES_2026_05_12.md`.
- [ ] Project module sections (Risks, Assumptions, Issues, Decisions, Dependencies) rendered as datatable-driven views.
- [ ] Widget visibility controls implemented for administrators (default widgets) and project managers (per-project show/hide).
- [ ] Modal quick-create flows return users to the launching page context after close.
- [ ] Footer updated with centered "Powered by Talaris" link.

### Manual Acceptance Testing
1. Verify header shows logo, site title, and complete navbar structure on desktop and mobile widths.
2. Open `/programmes` and confirm card-based list with fully clickable cards.
3. Open a programme detail page and confirm computed status and clickable related project cards.
4. Open `/projects`, apply programme filters (including no-programme), and confirm results and navigation behavior.
5. Open `/projects/:id`, collapse/expand side panel, and confirm module navigation and overview behavior.
6. Validate overview widgets display expected data and quick-create modal flows return to the same page context.
7. Validate admin default-widget controls and project-manager widget show/hide controls.
8. Open each module section (Risks/Assumptions/Issues/Decisions/Dependencies) and confirm datatable rendering/interaction.
9. Verify footer displays centered "Powered by Talaris" link.
10. Run responsive and WCAG 2.2 AA spot checks for navigation, cards, panel toggle, and modal flows.

### Exit Criteria
- [ ] New desktop-oriented UI shell and page layouts are production-ready with localization, accessibility, and responsive behavior validated.

## Phase 11 (Weeks 21-22): Dashboards, Drill-Downs, and Traceability

### Objectives
- Provide programme/project dashboards with traceable source navigation.

### Delivery Checklist
- [ ] Programme dashboard with module summary widgets.
- [ ] Project dashboard with module summary widgets.
- [ ] Drill-down pages from each widget to detail views.
- [ ] Source links from details to originating module records.
- [ ] Performance tuning for dashboard queries and pagination.

### Manual Acceptance Testing
1. Open programme and project dashboards populated with sample data.
2. Validate each widget count/metric against source records.
3. Click widget to open detail page and verify filtered result set.
4. Follow source link from detail record to module record.
5. Confirm dashboard loads within acceptable response targets.

### Exit Criteria
- [ ] Dashboard metrics are accurate, navigable, and explainable.

## Phase 12 (Weeks 23-24): Cross-Module Reports and Email Scheduling

### Objectives
- Implement reporting and scheduled email distribution.

### Delivery Checklist
- [ ] Cross-module report builder for selected use cases.
- [ ] Report visibility rules for users and stakeholders.
- [ ] Email scheduling by frequency and recipients.
- [ ] Delivery logging and retry handling for failed sends.
- [ ] Localized report/email templates.

### Manual Acceptance Testing
1. Build a report that combines data from multiple modules.
2. Verify role-based visibility for report access.
3. Schedule report email to test recipients.
4. Trigger schedule execution and confirm delivery.
5. Simulate email failure and verify retry/log behavior.

### Exit Criteria
- [ ] Scheduled report delivery is reliable and auditable.

## Phase 13 (Weeks 25-26): Hardening, Accessibility, Docs, and Release Readiness

### Objectives
- Finalize quality, compliance, and deployment readiness.

### Delivery Checklist
- [ ] Full WCAG 2.2 Level AA review completed for core user journeys.
- [ ] Security hardening review completed (sessions, auth events, data handling).
- [ ] Full regression suite executed and defects triaged.
- [ ] Deployment runbooks finalized for local/shared hosting/VPS.
- [ ] Jekyll docs completed for Features and Documentation sections.
- [ ] README finalized with installation, license, screenshots, and product summary.
- [ ] GPL/legal header compliance check completed.
- [ ] Add a standardized legal notice at the top of all text files stating that the name "Talaris Project Toolkit" is a trademark of Mark Berthelemy Consulting, and referencing the GPL license.

### Manual Acceptance Testing
1. Execute end-to-end smoke tests for admin, programme manager, project manager, team member, and stakeholder personas.
2. Perform keyboard-only walkthrough on top user journeys.
3. Perform screen reader spot checks on forms, tables, and navigation.
4. Verify color contrast on key UI states (normal, hover, focus, error).
5. Deploy to staging profile matching shared hosting constraints and verify app startup.
6. Deploy to staging profile matching VPS constraints and verify app startup.
7. Validate documentation links and core installation instructions.

### Exit Criteria
- [ ] Product owner signs off release candidate for production.

## Deferred / Future Phases (Post v1)

### Security Enhancements
- [ ] Add SSO integration.
- [ ] Add 2-factor authentication.
- [ ] Expand security monitoring and anomaly alerting.

### Advanced Product Enhancements
- [ ] Expand report catalog based on stakeholder feedback.
- [ ] Add module marketplace governance and trust workflow.
- [ ] Add performance optimization for large datasets and high concurrency.

## Suggested Sprint Ceremony Cadence

- Sprint planning: 2 hours at start of each phase.
- Mid-sprint checkpoint: 30 minutes.
- Sprint review and manual acceptance testing: 90 minutes.
- Sprint retrospective: 60 minutes.
