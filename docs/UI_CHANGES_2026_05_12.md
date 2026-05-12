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

The main content section should be split into two sections:

### Navigation panel



A hideable panel that contains links to each of the modules available for the project

## Footer

The footer should contain:

- Centred text: Powered by [Talaris](https://talaris.net)



