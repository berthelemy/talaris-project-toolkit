---
name: php-codeigniter4-backend
description: "Use when: implementing PHP backend features in CodeIgniter 4, including controllers, models, validation, routing, and framework conventions."
---

# PHP + CodeIgniter 4 Backend

## Goal
Implement robust backend features using CodeIgniter 4 and modern PHP practices.

## Do
- Use CI4 MVC structure: controllers for HTTP flow, models for persistence, libraries/services for business logic.
- Keep controllers thin and move reusable logic into service classes.
- Use CI4 validation and request helpers for input handling.
- Use framework config files and environment settings instead of hardcoded values.

## Checks
- New routes are explicit and follow existing route grouping conventions.
- Validation errors are handled consistently in UI/API responses.
- Code is compatible with current project PHP and CI4 versions.
