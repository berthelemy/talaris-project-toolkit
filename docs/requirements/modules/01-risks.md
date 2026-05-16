---
title: Risks module
type: requirements
updated: 2026-05-16
---
# Risks module

The risks module is designed to help managers to record, prioritise and mitigate project or programme risks

## Core functions

The main interface will include capabilities to:

1. Add, edit or delete risk entries
2. View a summary of the risks' status
3. Highlight the high priority risks

## Details

Each entry in the risk register will have its own page, showing all the data stored for this entry. The page will be formatted in two responsive columns - with textual data in the first column and the rest in the second column.

## Data

Each risk entry will include:

- Date entered
- Person entering the risk entry
- Risk description
- Risk impact (high, medium, low)
- Risk likelihood (high, medium, low)
- Calculated priority based on the impact and likelihood (high, medium, low)
- Mitigation actions
- Owner
- Closed (boolean)
- Closing date
- Lessons learned

## Widgets

### Risks overview

Designed to show a summary grid of the number of open risks at each priority level.

### High priority risks

Designed to show a list of the high priority risks.

Each risk should be rendered as a link to open the risk details