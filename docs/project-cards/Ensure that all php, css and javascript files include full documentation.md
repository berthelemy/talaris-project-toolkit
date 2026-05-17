---
title: Card - Ensure Full Documentation for PHP, CSS, and JavaScript Files
type: card
status: Done
updated: 2026-05-17
---
# Card - Ensure Full Documentation for PHP, CSS, and JavaScript Files

## Requirement Source
Code quality, maintainability, onboarding, and auditability standards for application source files.

## Embedded Requirement Content

### Purpose
Ensure all in-scope PHP, CSS, and JavaScript source files contain complete, consistent, and maintainable documentation so contributors can safely understand behavior, dependencies, and change impact.

### Scope
#### In Scope
- `app/**/*.php`
- `public/js/**/*.js`
- `public/css/**/*.css`
- Module-owned frontend assets under `app/Modules/**` (for example `Assets/js` and `Assets/css`)

#### Excluded
- Third-party code and generated artifacts (`vendor/**`, build output bundles, minified third-party files)
- Runtime/writable directories (`writable/**`)
- Binary/non-source assets

### Documentation Requirements by File Type

#### PHP Requirements
- Every PHP file must include an accurate file-level PHPDoc block describing intent and primary responsibilities.
- Every class, interface, trait, and enum must include PHPDoc with a concise description of role and context.
- Every public and protected method must include PHPDoc covering:
- Purpose
- Parameters with types and intent
- Return type and meaning
- Exceptions/errors thrown when applicable
- Side effects (database writes, external calls, cache/session mutations) when applicable
- Properties that influence behavior should be documented where not self-explanatory.
- For arrays with structured payloads, use typed documentation (including array-shape style where practical) to clarify expected keys.

#### JavaScript Requirements
- Every JS file must include a file header comment describing module purpose and major dependencies.
- Public functions, exported functions, classes, and methods must include JSDoc with parameters, return values, and behavior notes.
- Event handlers and async workflows should document triggers, expected payloads, and error/fallback behavior.
- DOM-coupled scripts should document required selectors/data attributes and assumptions about markup.

#### CSS Requirements
- Every CSS file must include a top-level purpose comment that defines the stylesheet scope.
- Stylesheets must be organized with section comments for major areas/components.
- Complex selectors, non-obvious overrides, and state-dependent rules require explanatory comments.
- Shared utility classes, design tokens, and accessibility-related styles (focus, contrast, reduced motion handling) must be documented with intent.

### Quality and Consistency Requirements
- Documentation must stay synchronized with implemented behavior; stale comments are non-compliant.
- Documentation should explain why and how behavior works, not restate obvious syntax.
- Terminology must align with project/module language used in requirements and UI labels.
- All new or modified in-scope files must meet this standard before merge.

### Enforcement and Workflow Requirements
- Introduce and maintain a lightweight documentation checklist in pull request review criteria.
- Add CI/automation checks where practical to detect missing PHPDoc/JSDoc/file-header comments in in-scope paths.
- Reviewers must reject incomplete or misleading documentation in changed files.
- Incremental adoption is allowed for legacy files, but any touched legacy file must be brought up to standard.

### Acceptance Criteria
- A written documentation standard for PHP, CSS, and JavaScript exists and is discoverable in project documentation.
- All changed in-scope files in the implementation PR(s) include compliant documentation.
- Spot-check audit across representative modules confirms coverage and quality, not only presence of comments.
- CI/review workflow enforces documentation expectations for future changes.
- No third-party or generated files are incorrectly modified just to satisfy this requirement.

### Non-Functional Requirements
- Documentation updates must not change runtime behavior.
- Added comments must preserve readability and avoid excessive noise.
- Standards must be practical for day-to-day delivery and compatible with current coding conventions.

### Implementation Notes
- Prioritize high-change and high-risk areas first (authentication, RBAC, module APIs, widget providers).
- Document exceptions explicitly if a file cannot reasonably meet a rule, with rationale recorded in the PR.

## Definition of Done
- Requirement is approved and linked from the relevant documentation index.
- In-scope standards for PHP/CSS/JS are published with clear examples.
- Initial remediation pass is completed for targeted high-priority areas.
- PR review checklist and/or automated checks are in place for ongoing enforcement.
- Validation evidence is attached (file audit sample, review checklist updates, and CI result summary).

## Implementation Evidence
- Standards published: `docs/requirements/CODE_DOCUMENTATION_STANDARDS.md` and indexed via `docs/requirements/README.md`.
- Enforcement added: `scripts/check-documentation.sh`, CI integration in `.github/workflows/ci.yml`, and review checklist in `.github/pull_request_template.md`.
- Initial remediation completed in high-priority areas:
- `app/Controllers/AuthController.php`
- `app/Controllers/ModuleManagementController.php`
- `public/js/autosave.js`
- `public/js/widget-layout-ordering.js`
- `public/css/app-theme.css`
- Validation run: `XDEBUG_MODE=off composer docs:check` and `XDEBUG_MODE=off composer lint`.
