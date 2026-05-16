---
name: kanban-methodology-best-practices
description: "Use when: planning, executing, or reviewing delivery work using Kanban boards, WIP limits, pull-based flow, and continuous improvement practices."
---

# Kanban Methodology Best Practices

## Goal
Keep delivery flow predictable, transparent, and continuously improving using practical Kanban controls.

## Do
- Define clear workflow states and explicit entry/exit criteria for each column.
- Keep board items small, testable, and outcome-focused (vertical slices where possible).
- Enforce pull-based flow: start new work only when capacity is available.
- Set and respect WIP limits for active states (`Ready to do`, `in-progress`, review/validation states).
- Require every blocked item to include blocker reason, owner, and unblock dependency.
- Prioritize by service class and risk (expedite only when justified and time-bounded).
- Use aging and lead-time signals to spot stalled flow early.
- Close work with definition-of-done evidence (tests, docs, migration notes, and validation output).
- Review flow metrics regularly (throughput, cycle time, blocked time, rework rate).
- Run lightweight retrospectives and apply one measurable process improvement at a time.

## Checks
- Board states are unambiguous and consistently used across cards.
- No hidden work exists outside the board for active delivery.
- WIP breaches are called out and corrected, not normalized.
- Blocked cards contain clear unblock actions and dependencies.
- Items marked `Done` include acceptance evidence and links to verification artifacts.
- Prioritization reflects current constraints and business value.
