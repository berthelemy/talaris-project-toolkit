---
title: Major UI changes
type: requirements
updated: 2026-05-16
---
# Major UI changes

Objective: To make the application look and feel more like a desktop application

## Header

The header should contain the following elements:

- Logo
- Site title
- Navbar

### Navbar

The navbar should be structured as follows:

- Programmes
- Projects
- Admin
  - Users
  - Modules
  - Theme
- Profile
- Language selector
- Sign out / Sign in

## Main content

The main contents will change depending on the page template:

### /programmes

The main content will contain:

- Heading: Programmes
- a series of cards, each containing the name of the programme, its description and status. The whole card will be clickable to take the user to the progamme page.

### /programmes/1

Each programme page main content section will contain:

- Programme title
- Programme description
- Programme status - calculated based on the status of each of the related projects
- A series of cards, each containing the name of a related project, its description and status. The whole card will be clickable to take the user to the project page.

### /projects

The main content section will contain:

- Heading: Projects
- A series of cards, filterable by programme (including projects where there is no related programme). Each card will contain the name of the project, its description and status. The whole card will be clickable to take the user to the project page.

### /projects/1

This page will be split into two sections:

#### Navigation panel

A 2/12 width hideable panel

The panel will contain:

- Heading: Project name
- Link: Overview
- Links to each of the available modules

#### Main panel

The contents of this panel will change depending on the module selected:

##### Overview

This will contain:

- Project title
- Project description
- One or more widgets provided by the modules (each widget will display in a card)
  - Risks widget 1 containing:
    - Title: Risks overview
    - Number of risks at each priority level
    - Button to add a new risk in a modal popup
    - Button to view all risks
  - Risks widget 2:
    - Title: High priority risks
    - List of high priority risks
    - Button to add a new risk in a modal popup
    - Button to view all risks
  - Assumptions widget 1:
    - Title: Assumptions
    - Number of unvalidated assumptions
    - Button to add a new assumption in a modal popup
    - Button to view all assumptions
  - Issues widget 1 containing
    - Title: Issues overview
    - Issues matrix showing the number of issues at each status and priority
    - Button to add a new issue in a modal popup
    - Button to view all issues
  - Issues widget 2:
    - Title: High priority issues
    - List of high priority issues
    - Button to add a new risk in a modal popup
    - Button to view all risks
  - Decisions widget 1:
    - Title: Decisions overview
    - Number of decisions made
    - Button to add a new decision in a modal popup
    - Button to view all decisions
  - Dependencies widget 1:
    - Title: Dependencies overview
    - Number of projects this project depends on
    - Number of projects depending on this project
    - Button to add a new dependency in a modal popup
    - Button to view all dependency    

Where a modal popup is requested, when it closes, it will return the user to the page from which the popup was launched

The admin user will be able to select which widgets appear by default on each project's overview page.

Each project manager will be able to add or hide widgets on the overview page.

##### Risks

A datatable showing all the data about all the risks in this project.

##### Assumptions

A datatable showing all the data about all the assumptions in this project.

##### Issues

A datatable showing all the data about all the issues in this project.

##### Decisions

A datatable showing all the data about all the decisions in this project.

##### Dependencies

A datatable showing all the data about all the dependencies in this project.

## Footer

The footer should contain:

- Centred text: Powered by [Talaris](https://talaris.net)



