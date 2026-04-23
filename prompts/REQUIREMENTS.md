# Project Toolkit Initial Requirements

## Objective

The project toolkit application will be a multi-user system that gives project managers within an organisation access to a range of tools to support the administration and management of their projects.

## Frameworks and infrastructure

- The back-end will use CodeIgniter 4, and follow CodeIgniter and PHP best practices.
- The front-end will use Bootstrap 5 and Bootstrap Icons, and follow Bootstrap best practices
- The code will strictly follow the PSR-12 coding convention
- Where possible, changes to data in the front-end will be stored immediately by the back-end in the database, without requiring a form to be submitted.
- All changes to data will be logged, with a datetime stamp, a record of the change made, and who made it.

## Languages

- The application should work initially in English and French
- The application should appear in the language set by the user's browser, defaulting to English where the language is not supported
- Users should be able to override the set language via a language selector in the interface
- The language selector should set an essential cookie to remember the user's preference between sessions

## Hosting

- The application should be able to be deployed locally, within shared hosting environments and within virtual private servers.

## Database

- The application will use MySQL/MariaDB
- To avoid data conflicts, when a user with permissions to update data opens a module within a project or programme, then that module's data within that context will be checked-out and cannot be changed by another user.
- Once this user has logged out, then the data will be unlocked and available to be changed by another user.

## Security

- Users will be logged out after a period of inactivity (definable by the system administrator)
- Users will require a user name and password to login
- The password length and strength will be definable by the system administrator
- Users will be able to recover their password through a link sent to their email address
- Users will be able to set a username that is different from their email address
- Later phases will add single-sign on capability
- Later phases will add 2 factor authentication

## Front end design

- The initial design will use a corporate style, with a default logo, font and colour scheme
- Administrators should be able to change the logo, the heading and body fonts and color scheme
- The application should be designed on "mobile-first" principles

## Programmes and projects

- A programme is a collection of projects
- A programme has zero or more programme managers
- A programme has zero or more projects
- A project has zero or more project managers

## User profiles

- Each user will have the following data attached to them:
  - username
  - email address
  - password
  - language preference
  - description (written by the user)
  - image/avatar

- The image/avatar should default to a head and shoulders line drawing on a grey background
- Users should be able to change their profile data
- A user should only be able to change their password in the profile if they can also enter their current password.

## User roles

- Roles can be allocated at System, Programme and Project level
- The application should have the following pre-defined roles:
  - Administrator:
    - Can invite or upload users
    - Can define roles
    - Can configure the system's design
    - Can make modules available to end-users
    - Can impersonate any user and see the application as if they are this user
    - Can add modules
  - Programme manager:
    - Can create Programmes
    - Can read, update and delete their own Programmes
    - Can add existing Projects to Programmes
  - Project manager:
    - Can create Projects
    - Can read, update and delete their own Projects
    - Can update any information attached to a project they own
  - Team member:
    - Can read any information within a project where they are given this role
  - Stakeholder:
    - Can read reports that are open to Stakeholders
- A user may have more than one role in any context (system, programme, project, module)
- The administrator can create new roles with different permissions, based on the existing roles, or starting from afresh.

## Dashboards

- Each Project and Programme will have a dashboard, containing graphical summaries of the data in their attached modules
- Each dashboard component will link to a page containing more detail
- Each element of detail will be able to link to the source data

## Modules

- The application will be based around functionalities defined as modules
- All modules will use the same outline code structure
- All modules will follow the code conventions in this project
- All modules will contain appropriate unit tests
- Modules may be designed to work at either Programme or Project level
- The application will contain two sample "Hello World" modules - one for Programmes and one for Projects
- Modules should be able to be added through a zip file uploaded via the application interface by the administrator
- Modules will each expose an internal API, so that they can be read and updated by other modules

## Initial modules

The initial modules will include:

For projects:

- A Risk register
- An Assumptions register
- An Issues register
- A Dependencies register

The RAID logs will follow best project management practices.

Each module will need its own set of requirements, to be defined later.

## Reports

- The application will have the ability to create reports from across multiple modules.
- These reports may be emailed to users and stakeholders at a frequency defined by the project manager or programme manager
- The exact reports required will be defined later

## Documentation

- All documentation will be held as Markdown files within a docs directory
- Documentation will be presented within a section of a Jekyll website, using a Bootstrap based theme
- The Jekyll website will contain:
  - An attractive home page, summarising the benefits of the software
  - Features: Listing the key features
  - Documentation: Containing:
    - Server requirements
    - Security
    - Installation
    - Configuration
    - Programmes
    - Projects
    - Modules (for each module, include details of the module's internal API)

## Open source

- The application should use the GPL license
- The README.md file should contain installation instructions, links to the license, sample screenshots and a description of the product
- All text files within the project should contain the following text:

```
// This file is part of the Talaris Project Toolkit - http://Talaris.work/
//
// The Talaris Project Toolkit is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// The Talaris Project Toolkit is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with the Talaris Project Toolkit.  If not, see <http://www.gnu.org/licenses/>.
```