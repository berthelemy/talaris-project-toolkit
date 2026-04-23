---
name: email-scheduling-notifications
description: "Use when: implementing scheduled report emails and notification delivery with configurable frequency and recipient targeting."
---

# Email Scheduling and Notifications

## Goal
Send reliable report communications to users and stakeholders.

## Do
- Model schedule rules (frequency, timing, timezone, recipients, scope).
- Queue outbound email jobs and track delivery outcomes.
- Provide unsubscribe/opt-out behavior where policy requires.
- Keep templates localized and consistent with branding.

## Checks
- Failed sends are retried and surfaced for admin review.
- Recipient permissions are checked before report delivery.
- Schedule edits take effect predictably.
