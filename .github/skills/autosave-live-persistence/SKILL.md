---
name: autosave-live-persistence
description: "Use when: implementing immediate backend persistence from frontend edits without full form submission, including validation and failure handling."
---

# Autosave and Live Persistence

## Goal
Persist user edits quickly and safely without requiring explicit form submits.

## Do
- Use granular endpoints for field-level or section-level updates.
- Validate server-side for every autosave request.
- Return clear success/error state so UI can reflect save status.
- Debounce high-frequency edits to reduce unnecessary writes.

## Checks
- Partial failures are recoverable and visible to users.
- Concurrency conflicts surface actionable messages.
- Audit logs capture autosave-origin changes like normal writes.
