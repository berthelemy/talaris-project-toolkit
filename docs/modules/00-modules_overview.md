# Modules overview

## Rules

- All modules will use the standard module structure
- All modules will expose an API that can be accessed by other modules
- All modules will include documentation on how to use the module's API
- Modules can be designed for either Projects or Programmes

## Interface

- All modules will provide an interface through which their data can be accessed and changed
- The main interface will be accessed through the routes /projects/[project_id]/modules/[module_name] or /programmes/[programme_id]/modules/[module_name]

## Widgets

- All modules will provide one or more widgets
- The widgets will be available to view on the Overview page of each project or programme
- A project or programme manager can choose to enable or disable any of the available widgets for the project or programme
- All widgets will include a button to add data to the module in a modal popup
- All widgets will include a button to view the main page for the module

## Specifications

- Specifications for each module are in separate files in this directory.