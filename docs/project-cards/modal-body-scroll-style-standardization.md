---
title: Card - Modal Body Scroll Style Standardization
type: card
status: Ready to do
updated: 2026-05-16
---
# Card - Modal Body Scroll Style Standardization

## Suggested Change Source
Follow-up from module widget modal consistency updates.

## Problem
Modal overflow scrolling is now correctly applied at `div.modal-body`, but the style is duplicated inline across module views.

## Proposal
Introduce a shared CSS utility class for modal-body overflow behavior (for example `module-modal-body-scroll`) and replace repeated inline styles.

## Scope Candidates
- Assumptions module modal
- Decisions module modal
- Dependencies module modal
- Issues module modal
- Risks module modal
- Tasks module modal
- HelloWorld project/programme widget modals

## Definition of Done
- Shared CSS class created in the appropriate common stylesheet.
- Inline `max-height`/`overflow-y` styles removed from affected modal bodies.
- Behavior remains: scroll only on modal body.
- Mobile and desktop behavior validated.
- No regressions in modal layout or accessibility.
