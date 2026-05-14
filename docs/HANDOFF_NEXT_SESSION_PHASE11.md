# Handoff: Phase 11 - Risk UX Stabilization and Dashboard Scope Cleanup
## Session: May 14, 2026
## Status: Ready for Next Session

## Summary
This session completed a large set of Risk register UX improvements on the project module page and removed the now-deprecated project/programme drill-down dashboard feature.

## Completed Work
- Risk register datatable on project module page was reworked for inline row editing.
- Risk rows now default to read-only text and switch to editable controls only when that row enters edit mode.
- Save/Cancel controls are shown only for the row currently being edited.
- Risk table columns were split so title, description, and mitigation actions are separate columns.
- Risk Close action buttons were removed from risk datatable rows.
- Standalone risk filter/search form was removed so users rely on DataTables search.
- DataTables responsive behavior was stabilized for risk rows, including collapsed row edit behavior.
- Global datatable vertical top alignment was added via shared assets.
- Risk widget output was aligned to specification with two widgets:
  - Open risks by priority
  - High-priority risk list
- Risk widget modal fields were expanded to match the main risk modal field set.
- Cache invalidation behavior for risk mutations was implemented earlier in this phase and retained.

## Dashboard Scope Changes (requested)
- Removed project/programme dashboard drill-down routes:
  - `GET /projects/{id}/dashboard/details`
  - `GET /programmes/{id}/dashboard/details`
- Removed drill-down buttons from project/programme overview pages.
- Removed obsolete drill-down view file:
  - `app/Views/dashboard/details.php`
- Simplified `DashboardController` to dashboard home behavior only.

## Project Overview UX Change (requested)
- Added explicit Edit Project button on project overview card.
- Button links to existing project edit page where title, description, and status are editable.
- Button is shown only for actors with project management permissions.

## Files Changed In Final Uncommitted Set
- `app/Config/Routes.php`
- `app/Controllers/DashboardController.php`
- `app/Controllers/ProjectController.php`
- `app/Views/programmes/show.php`
- `app/Views/projects/show.php`
- `tests/system/ProgrammeProjectDomainSystemTest.php`
- `docs/HANDOFF_NEXT_SESSION_PHASE11.md`

## Tests and Validation
- Updated system tests to remove obsolete dashboard-detail expectations and add project edit button coverage.
- Ran:
  - `XDEBUG_MODE=off vendor/bin/phpunit --do-not-fail-on-warning tests/system/ProgrammeProjectDomainSystemTest.php`
- Result: tests passed in that file (with existing non-blocking PHPUnit Xdebug coverage warning).

## Commits This Session
- `5b19c6e` - Complete Phase 11 risk register UX and responsive datatable fixes
- (Next commit pending for dashboard removal + project overview edit button + handoff update)

## Known Notes
- PHPUnit exits with warning in this environment due to coverage mode configuration (`XDEBUG_MODE=coverage` not enabled). Functional assertions for targeted tests passed.
- Language keys for removed dashboard drill-down labels remain in language files and can be cleaned up in a future housekeeping pass.

## Recommended Next Steps
1. Smoke test these routes/pages in browser:
   - `/projects/{id}` (verify Edit button visibility and behavior)
   - `/programmes/{id}` (confirm no dashboard details button)
   - `/dashboard` (home still renders as expected)
2. Optionally clean up unused language keys related to removed drill-down dashboard labels.
3. Continue Phase 11 backlog items after stakeholder confirmation of final risk-table UX behavior on collapsed rows.
