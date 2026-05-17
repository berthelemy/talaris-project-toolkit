---
title: Code Documentation Standards
type: doc
updated: 2026-05-17
status: In Progress
---
# Code Documentation Standards

## Purpose
Define baseline documentation requirements for PHP, JavaScript, and CSS files so contributors can understand intent, behavior, dependencies, and change impact without reverse-engineering implementation details.

## Scope
Applies to first-party source files under:
- `app/**/*.php`
- `public/js/**/*.js`
- `public/css/**/*.css`
- module-owned assets under `app/Modules/**`

Does not apply to:
- third-party or generated files (`vendor/**`, minified bundles, external libraries)
- runtime writable files (`writable/**`)

## Core Rules
- Documentation must describe intent and behavior, not restate obvious syntax.
- Documentation must remain synchronized with behavior when code changes.
- New and modified in-scope files must comply before merge.
- If a documented rule cannot reasonably be applied, the pull request must include a short rationale.

## PHP Standards
### File Header
Each PHP file must include a file-level PHPDoc header describing purpose and domain responsibility.

### Class and Type Docs
Each class, interface, trait, or enum must include PHPDoc that explains role and usage context.

### Method Docs
Every public/protected method must include PHPDoc with:
- purpose summary
- `@param` entries for inputs and expected meaning
- `@return` behavior/shape
- `@throws` when exceptions are intentionally surfaced
- side-effect notes where relevant (database writes, cache/session mutations, outbound calls)

### Data Shape Clarity
Where arrays carry structured data, include explicit key expectations in docs.
Use array-shape style when practical.

### PHP Example
```php
<?php

/**
 * Module widget layout coordination service.
 *
 * Persists and resolves scope-specific widget layout preferences.
 */
final class ModuleWidgetLayoutService
{
    /**
     * Save or update a widget visibility and display order preference.
     *
     * @param string $scopeType programme|project
     * @param int $scopeId Scope identifier.
     * @param string $moduleSlug Target module slug.
     * @param bool $isVisible Whether widget is visible by default.
     * @param int $displayOrder Display order position.
     * @param int $actorId User applying the change for audit traceability.
     * @return bool True when write succeeds.
     */
    public function upsert(
        string $scopeType,
        int $scopeId,
        string $moduleSlug,
        bool $isVisible,
        int $displayOrder,
        int $actorId
    ): bool {
        // ...
    }
}
```

## JavaScript Standards
### File Header
Each file should begin with a block comment describing script purpose, required DOM attributes/selectors, and external dependencies.

### Function and Class Docs
Exported/public functions and non-trivial internal functions should include JSDoc with:
- `@param` and `@returns`
- event/data expectations
- fallback/error behavior for async logic

### DOM Contract Notes
Scripts that depend on markup should document required attributes/classes.

### JavaScript Example
```javascript
/**
 * Autosave input fields using debounced AJAX requests.
 *
 * Required attributes:
 * - data-autosave="true"
 * - data-autosave-url
 * - data-csrf-name
 * - data-csrf-value
 * - data-csrf-cookie-name
 */
(function () {
    'use strict';

    /**
     * Debounce function execution.
     *
     * @param {Function} fn Callback to invoke.
     * @param {number} delay Delay in milliseconds.
     * @returns {Function} Debounced callback.
     */
    function debounce(fn, delay) {
        let timer = null;

        return function debounced() {
            const context = this;
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, delay);
        };
    }
})();
```

## CSS Standards
### File Header
Each stylesheet must start with a comment that states stylesheet scope and intended usage.

### Sectioning
Use section headers for major component groups (layout, utilities, components, accessibility states).

### Complex Rule Commentary
Add comments for non-obvious selector specificity, overrides, and state coupling.

### Accessibility Notes
Document styles that directly support accessibility behavior, such as focus visibility, reduced motion, and contrast-sensitive variants.

### CSS Example
```css
/*
 * Application theme overrides.
 * Scope: global app chrome, buttons, and shared utility classes.
 */

/* Component: Primary action buttons mapped to theme token colors. */
.btn-primary {
    --bs-btn-bg: var(--talaris-primary);
    --bs-btn-border-color: var(--talaris-primary);
}

/* Utility: constrain modal body scrolling to preserve header/footer visibility. */
.module-modal-body-scroll {
    max-height: 70vh;
    overflow-y: auto;
}
```

## Review and CI Enforcement
- Pull requests must confirm in-scope file documentation completeness.
- Automated checks should validate baseline file-header and docblock/JSDoc presence for changed files.
- Reviewers should reject stale, misleading, or placeholder documentation.

## Incremental Adoption Plan
1. Enforce for all newly changed in-scope files immediately.
2. Prioritize remediation for high-risk modules (authentication, RBAC, module APIs, widget providers).
3. Expand to full repository pass as part of quality hardening milestones.
