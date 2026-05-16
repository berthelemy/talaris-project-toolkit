---
title: Module Structure Reference
type: doc
updated: 2026-05-16
status: complete
---
# Module Structure Reference

This document defines the canonical folder hierarchy for modules in Talaris and explains why the modular layout is preferred over scattering module code across global application folders.

## Canonical Hierarchy

Each module lives under app/Modules/<ModuleName>/ and keeps its own MVC, language, routing, and optional test/migration assets.

```text
app/Modules/
  <ModuleName>/
    Controllers/
      <ModuleName>Controller.php
    Models/
      <ModuleModel>.php
    Views/
      index.php
      widget.php                  # Optional when module exposes dashboard widgets
    Widgets/
      ModuleWidget.php            # Optional; implements ModuleWidgetInterface
    Language/
      en/Module.php
      fr/Module.php
    Config/
      routes.php
    Tests/                        # Optional local test assets
      <ModuleName>SystemTest.php
      <ModuleName>UnitTest.php
    Database/                     # Optional module-specific migrations/seeds
      Migrations/
      Seeds/
```

## Required vs Optional

- Required: Controllers, Models, Views, Config/routes.php
- Required: Localization coverage in English and French
- Optional: Widgets when module should appear in programme/project detail dashboards
- Optional: Tests and Database folders for module-specific development and packaging

## Why This Is Better

1. Strong ownership boundaries
Module code, language keys, and routes stay together, making reviews and maintenance faster.

2. Safer change impact
A module can evolve without broad edits in unrelated core folders.

3. Better portability
A module directory can be copied, versioned, and reviewed as a coherent package.

4. Clear onboarding path
Developers can inspect one directory and understand behavior, routes, data model, and UI.

5. Cleaner scalability
As module count grows, per-module isolation avoids naming collisions and reduces cognitive load.

## Language Catalog Strategy

Current Phase 6 strategy uses app/Language/en/Module.php and app/Language/fr/Module.php as the canonical shared catalog for framework and Hello World labels.

Module-local Language/en|fr/Module.php files delegate to the shared catalog to avoid duplicate translations while preserving expected module folder structure.

## Widget Discovery Mapping

Widget discovery converts module directory names into slugs and matches enabled rows in module_registry.

Examples:

- HelloWorldProgramme -> hello_world_programme
- HelloWorldProject -> hello_world_project

A module widget is loaded from one of these class locations, in order:

1. App\Modules\<ModuleName>\Widgets\ModuleWidget
2. App\Modules\<ModuleName>\Services\WidgetService
3. App\Modules\<ModuleName>\Controllers\<ModuleName>WidgetController

## Practical Checklist For New Modules

- Create folder under app/Modules/<ModuleName>/
- Add routes in Config/routes.php
- Register module metadata in module_registry migration/seed
- Implement controller actions and model persistence
- Add en/fr language keys
- Add widget class if dashboard integration is needed
- Add system tests for enabled/disabled access and persistence
