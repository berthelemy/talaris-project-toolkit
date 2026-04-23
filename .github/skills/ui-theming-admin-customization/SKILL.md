---
name: ui-theming-admin-customization
description: "Use when: implementing admin-configurable branding such as logo, heading/body fonts, and color scheme across the application."
---

# UI Theming and Admin Customization

## Goal
Allow administrators to safely customize corporate branding.

## Do
- Store theme settings in configuration data with validation.
- Apply theme through centralized CSS variables or theme tokens.
- Support fallback assets and default branding.
- Ensure custom styles do not break contrast or readability.

## Checks
- Theme changes apply globally and consistently.
- Invalid uploads or values fail safely with clear messaging.
- Default theme can be restored quickly.
