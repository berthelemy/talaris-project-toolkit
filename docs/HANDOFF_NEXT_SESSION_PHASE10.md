# Next Session Handoff: Phase 10 Kickoff (Desktop-Oriented UI Overhaul and Navigation)

Date: 2026-05-12

## Session Outcome

Phase sequencing has been updated in the implementation plan so the next delivery phase is now the UI overhaul. The detailed scope is aligned to the UI direction document in `docs/UI_CHANGES_2026_05_12.md`.

## What Changed This Session

1. Phase planning reshuffle completed in `docs/PHASED_IMPLEMENTATION_PLAN.md`
- Phase 10 is now: Desktop-Oriented UI Overhaul and Navigation.
- Former Phase 10 (Dashboards/Traceability) moved to Phase 11.
- Former Phase 11 (Reports/Email Scheduling) moved to Phase 12.
- Former Phase 12 (Hardening/Release Readiness) moved to Phase 13.

2. Phase 10 scope now explicitly includes
- Header shell redesign (logo, site title, navbar).
- Navbar IA: Programmes, Projects, Admin (Users/Modules/Theme), Profile, language selector, sign in/sign out.
- Card-based list/detail UX for Programmes and Projects.
- Project detail split layout with hideable side navigation panel.
- Overview widgets with modal quick-create actions and return-to-origin behavior.
- Datatable module views for Risks, Assumptions, Issues, Decisions, Dependencies.
- Widget visibility governance: admin defaults + project-manager per-project controls.
- Footer update: centered "Powered by Talaris" link.

## Inputs for Phase 10 Execution

1. Primary design-direction source
- `docs/UI_CHANGES_2026_05_12.md`

2. Updated roadmap source
- `docs/PHASED_IMPLEMENTATION_PLAN.md`

3. Existing technical context to preserve
- Localization behavior and EN/FR strings from Phase 5.
- Module framework/widget system from Phases 6-9.
- DataTables integration already introduced for module/admin/table views.
- RBAC boundaries and audit logging expectations for all mutation paths.

## Implementation Priorities (Recommended Order)

1. App shell and navigation foundation
- Update shared layout(s) to implement the new header and footer.
- Rework navbar structure and active-state handling for desktop and mobile.
- Ensure sign in/sign out visibility logic still matches authentication state.

2. Programmes and Projects index/detail layout refresh
- Convert `/programmes` and `/projects` listings to clickable cards.
- Add/verify project filtering by programme, including unlinked projects.
- Implement computed programme status on `/programmes/:id`.

3. Project page architecture (`/projects/:id`)
- Add hideable 2/12 side panel for overview + module navigation.
- Keep panel behavior keyboard-accessible and responsive.
- Ensure selected module route state is clear and persistent in navigation.

4. Overview widgets and modal flows
- Align widget cards to UI direction and existing module widgets.
- Ensure modal quick-create closes back to the launching page context.
- Implement admin default widget selection and project-manager show/hide controls.

5. Datatable consistency pass
- Ensure each module section uses consistent DataTable setup for usability/accessibility.
- Validate sort/search/filter consistency and responsive behavior.

## Acceptance and Quality Gates

1. Definition of Done (must be met)
- CodeIgniter 4 and PSR-12 conventions.
- Security checks + audit logging on new mutation points.
- Updated automated tests for changed behavior.
- EN/FR localization coverage for all new/changed UI strings.
- Mobile-first and WCAG 2.2 AA checks completed.
- Docs updated for user-visible behavior and configuration.

2. Minimum test pass before handoff
- `cd /var/www/html && XDEBUG_MODE=off composer ci`

3. Required manual validation focus
- Keyboard-only navigation for header/navbar/panel toggle/widgets/modals.
- Screen-reader labels for navigation groups, cards-as-links, and modal controls.
- Color contrast/focus visibility on new desktop shell and card states.
- Mobile breakpoints for navbar collapse and project side-panel behavior.

## Risks and Watchpoints

1. Scope expansion risk
- The UI document includes broad layout and interaction changes across multiple routes; avoid mixing dashboard/reporting features from later phases.

2. Accessibility regression risk
- Card-as-link patterns and hideable side-panel controls can easily regress keyboard and focus behavior.

3. State-management risk
- Modal quick-create return behavior and widget visibility preferences require clear state ownership (server vs client).

4. Localization drift risk
- High volume of new labels/navigation text can create missing EN/FR keys if string extraction is not systematic.

## Suggested First Commands Next Session

```bash
cd /var/www/html
XDEBUG_MODE=off composer ci
```

Then begin with shell/navigation implementation on shared layout views and route-specific templates for `/programmes`, `/programmes/:id`, `/projects`, and `/projects/:id`.
