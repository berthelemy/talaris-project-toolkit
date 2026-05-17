title: Card - Project Navigation Panel Display
type: card
status: Done
updated: 2026-05-17
---
# Card - Project Navigation Panel Display

## Requirement Source
Project UX consistency and navigation continuity requirement for project-level module pages.

## Embedded Requirement Content

### Purpose
Ensure users can access a consistent project navigation panel across all project module pages so that movement between project context, modules, and key actions is predictable and efficient.

### Core Functions
1. Display the same project navigation panel currently used on the project main page on all project module pages.
2. Highlight the active module/page in the navigation panel.
3. Preserve project context links (project dashboard, module routes, and key project actions) without requiring users to return to the main project page.
4. Support responsive behavior so navigation remains usable on mobile, tablet, and desktop.

### Scope
- Applies to all project-level module pages rendered under the project context.
- Does not change programme-level navigation behavior unless explicitly routed through a project page.
- Reuses existing navigation information architecture and labels where available.

### Detailed Requirements
- The panel must appear in a consistent location on every project module page.
- The panel must include all project modules available to the current user, filtered by RBAC permissions.
- The current module/page must be visually indicated as active.
- Navigation links must preserve the current project identifier and route parameters.
- If a module is unavailable (disabled or unauthorized), it must not be shown as a navigable item.
- If no project modules are available to the user, show a clear empty-state message in the panel area.
- Panel behavior must match existing collapse/expand interaction patterns used on the project main page.

### UI and UX Requirements
- Keep visual styling aligned with existing Bootstrap 5 theme and application design tokens.
- Maintain keyboard navigability for all panel links and controls.
- Ensure visible focus indicators and sufficient color contrast for active/inactive states.
- On small screens, panel presentation may collapse into an off-canvas/drawer pattern, but must remain reachable within one obvious action.
- Avoid layout shift when navigating between module pages with and without long content.

### Accessibility Requirements (WCAG 2.2 AA)
- Provide semantic navigation landmarks and labels for assistive technologies.
- Ensure active page state is conveyed programmatically (not only by color).
- Verify tab order is logical and consistent across module pages.
- Confirm target sizes and spacing support touch interaction on mobile.

### Security and Authorization Requirements
- Enforce server-side RBAC checks for every navigation target regardless of UI visibility.
- Do not expose unauthorized module names or links in rendered markup.
- Log authorization denials according to application audit/security practices.

### Data and Configuration Requirements
- Reuse existing project/module metadata sources for panel item generation.
- Respect module enablement/disablement configuration.
- Support localization of panel labels via existing i18n language files.

### Non-Functional Requirements
- Panel rendering must not introduce noticeable delay in initial page paint compared to current module pages.
- Reused component/partial should minimize duplication and remain maintainable.
- Implementation should be compatible with current theming overrides.

### Test Requirements
- Unit/integration tests for navigation item generation by permission and module availability.
- Feature/system tests for active-state highlighting on representative module routes.
- Responsive verification for mobile and desktop layouts.
- Accessibility checks for keyboard navigation, focus visibility, and semantic landmarks.

## Definition of Done
- Shared project navigation panel is present on all in-scope project module pages.
- Active module highlighting works consistently across routes.
- RBAC-filtered visibility and access enforcement are validated.
- Mobile and desktop behavior are validated with no major layout regressions.
- Accessibility checks pass for navigation semantics and keyboard interaction.
- Localization support for panel labels is implemented or confirmed.
- Automated tests are added/updated for permission filtering and active-state behavior.

