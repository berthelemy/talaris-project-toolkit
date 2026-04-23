---
name: deployment-local-shared-vps
description: "Use when: preparing deployment for local development, shared hosting, and VPS targets with environment-safe configuration."
---

# Deployment: Local, Shared Hosting, VPS

## Goal
Ship the application reliably across hosting models.

## Do
- Use environment-based configuration for URLs, DB, email, and secrets.
- Keep writable directories and permissions explicit per environment.
- Document deployment steps with rollback notes.
- Validate required PHP extensions and runtime prerequisites.

## Checks
- App boots and serves correctly in each target environment.
- No secrets are committed to repository files.
- Health checks/logging paths are configured and testable.
