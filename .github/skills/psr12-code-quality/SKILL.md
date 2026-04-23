---
name: psr12-code-quality
description: "Use when: writing or reviewing PHP to enforce PSR-12 style, readability, and maintainable code quality."
---

# PSR-12 and Code Quality

## Goal
Produce readable, consistent, and standards-compliant PHP.

## Do
- Follow PSR-12 formatting for namespaces, imports, braces, spacing, and line length.
- Use clear names, small methods, and low nesting.
- Add brief comments only where intent is not obvious.
- Keep error handling explicit and avoid silent failures.

## Checks
- No style drift from PSR-12 in touched files.
- Public methods have predictable behavior and minimal side effects.
- Changed code paths are covered by tests where practical.
