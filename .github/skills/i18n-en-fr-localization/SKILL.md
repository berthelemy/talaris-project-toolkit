---
name: i18n-en-fr-localization
description: "Use when: implementing localization, browser language detection, fallback to English, and user language override via cookie."
---

# English/French Localization

## Goal
Deliver reliable multilingual behavior for English and French.

## Do
- Store translatable strings in language files, not inline literals.
- Detect browser locale and map to supported locales.
- Default to English for unsupported locales.
- Provide language selector that persists preference in an essential cookie.

## Checks
- Language override takes precedence over browser default.
- Missing translations degrade gracefully.
- Date, number, and message formats respect locale where applicable.
