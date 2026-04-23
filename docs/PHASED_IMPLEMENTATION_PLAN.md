# Pejen Project Toolkit: Phased Implementation Plan

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
- [ ] Confirm baseline CodeIgniter 4 app bootstraps cleanly.
- [ ] Configure environment templates for local/shared hosting/VPS.
- [ ] Add coding standards checks and test runner baseline.
- [ ] Define initial DB migration strategy and naming conventions.
- [ ] Create starter docs pages: installation, configuration, server requirements.

### Manual Acceptance Testing
1. Clone repository and install dependencies on a clean machine.
2. Configure environment values and start the app.
3. Open homepage in browser and confirm HTTP 200 response.
4. Run test command and confirm baseline suite executes.
5. Review docs pages and verify installation steps are reproducible.

### Exit Criteria
- [ ] New developer can run the app in under 30 minutes using docs only.

## Phase 2 (Weeks 3-4): Authentication Core and Session Security

### Objectives
- Implement secure login foundations and session inactivity timeout.

### Delivery Checklist
- [ ] Username/password authentication implemented.
- [ ] Password policy is configurable by administrator settings.
- [ ] Session inactivity timeout is configurable and enforced.
- [ ] Password reset flow via email token is implemented.
- [ ] Audit logs created for login, logout, failed login, password reset events.

### Manual Acceptance Testing
1. Create a test user with compliant password.
2. Log in with valid credentials and verify dashboard access.
3. Attempt logins with invalid password and verify denial + logged event.
4. Stay idle until timeout threshold and confirm forced logout.
5. Trigger password reset email, complete reset, and log in with new password.
6. Review audit log records for all above actions.

### Exit Criteria
- [ ] Authentication and timeout behavior match configured policies.

## Phase 3 (Weeks 5-6): RBAC and User Management

### Objectives
- Deliver role-based authorization and user profile management.

### Delivery Checklist
- [ ] Implement predefined roles: Administrator, Programme manager, Project manager, Team member, Stakeholder.
- [ ] Support role assignment at system/programme/project scope.
- [ ] Support multiple roles per user in a context.
- [ ] Implement user profile updates including avatar, description, language preference.
- [ ] Enforce current-password requirement for password change in profile.
- [ ] Add impersonation capability for administrators with strict audit logging.

### Manual Acceptance Testing
1. Create users representing each predefined role.
2. Assign scoped roles and verify access boundaries per role.
3. Confirm a user with two roles receives union of allowed actions.
4. Edit profile fields and confirm persistence.
5. Attempt password change without current password and confirm rejection.
6. Perform admin impersonation and verify audit trail includes actor and target user.

### Exit Criteria
- [ ] Authorization checks consistently block unauthorized actions.

## Phase 4 (Weeks 7-8): Programmes, Projects, and Core Domain Model

### Objectives
- Implement core entities and ownership relations.

### Delivery Checklist
- [ ] CRUD for Programmes and Projects with ownership semantics.
- [ ] Programme-to-project linking implemented.
- [ ] Project and Programme manager assignments implemented.
- [ ] Validation rules and business constraints are enforced.
- [ ] Audit logging enabled for all domain mutations.

### Manual Acceptance Testing
1. Create programme and multiple projects.
2. Link/unlink projects to programme and verify reflected state.
3. Validate role-restricted CRUD behavior for programme/project managers.
4. Attempt invalid payloads and confirm validation errors are shown.
5. Confirm all create/update/delete actions are present in audit logs.

### Exit Criteria
- [ ] Domain model supports required programme/project workflows.

## Phase 5 (Weeks 9-10): Localization and Theming

### Objectives
- Enable multilingual experience and admin branding customization.

### Delivery Checklist
- [ ] English and French language packs wired for key UI flows.
- [ ] Browser language detection implemented with English fallback.
- [ ] Language selector implemented with essential cookie persistence.
- [ ] Admin theme settings added: logo, heading font, body font, color scheme.
- [ ] Contrast and readability validations included in theme handling.

### Manual Acceptance Testing
1. Open app in browser configured to French and verify French UI.
2. Open app in unsupported locale and verify English fallback.
3. Change language in selector and confirm persistence after logout/login.
4. Apply custom logo/fonts/colors as administrator.
5. Verify updated theme appears across major pages.
6. Run quick contrast spot-check on primary text, links, and buttons.

### Exit Criteria
- [ ] Language and branding preferences persist correctly across sessions.

## Phase 6 (Weeks 11-12): Module Framework and Hello World Modules

### Objectives
- Establish pluggable module architecture and baseline sample modules.

### Delivery Checklist
- [ ] Standard module scaffold defined and documented.
- [ ] One sample Programme-level Hello World module implemented.
- [ ] One sample Project-level Hello World module implemented.
- [ ] Module registration and enable/disable mechanisms implemented.
- [ ] Module unit test template included for all new modules.

### Manual Acceptance Testing
1. Install and enable both Hello World modules.
2. Verify each appears only in its intended scope.
3. Create records through module UI and confirm persistence.
4. Disable a module and confirm UI/API access is removed.
5. Run module-specific tests and verify pass status.

### Exit Criteria
- [ ] Team can build new modules from scaffold with consistent structure.

## Phase 7 (Weeks 13-14): Internal Module APIs and Autosave

### Objectives
- Enable module-to-module integration and live persistence.

### Delivery Checklist
- [ ] Internal API contract format defined and documented.
- [ ] Authentication/authorization checks added to internal APIs.
- [ ] Autosave endpoints and frontend behavior implemented for editable module fields.
- [ ] Error handling for autosave failures and concurrency conflicts implemented.
- [ ] Audit logs include autosave and internal API mutation events.

### Manual Acceptance Testing
1. Trigger an internal API read from Module A into Module B context.
2. Trigger an authorized internal API update and verify audit entry.
3. Edit a form field with autosave and confirm save indicator appears.
4. Simulate network interruption and confirm autosave error state is shown.
5. Retry after reconnect and confirm successful persistence.

### Exit Criteria
- [ ] Cross-module reads/updates are reliable and secure.

## Phase 8 (Weeks 15-16): Concurrency Locking and Checkout Flow

### Objectives
- Prevent edit conflicts with module-level checkout locking.

### Delivery Checklist
- [ ] Locking model implemented for module data by context and user.
- [ ] Lock acquisition on module open for authorized editors.
- [ ] Lock denial message for second editor with clear guidance.
- [ ] Lock release on logout and timeout.
- [ ] Administrative lock visibility and recovery tooling added.

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

### Manual Acceptance Testing
1. Create, edit, and close records in each RAID module.
2. Verify each record supports owner, status, and due/review metadata.
3. Validate role-based restrictions for create/update/delete actions.
4. Verify search/filter/sort behavior for operational use.
5. Confirm changes are captured in audit logs with actor and timestamp.

### Exit Criteria
- [ ] RAID modules are usable for day-to-day project governance.

## Phase 10 (Weeks 19-20): Dashboards, Drill-Downs, and Traceability

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

## Phase 11 (Weeks 21-22): Cross-Module Reports and Email Scheduling

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

## Phase 12 (Weeks 23-24): Hardening, Accessibility, Docs, and Release Readiness

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
