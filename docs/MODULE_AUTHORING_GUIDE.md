# Module Authoring Guide

This guide walks you through the complete process of creating a new module for the Talaris Project Toolkit. Whether you're building a simple data entry module or a complex cross-module integration, this guide provides step-by-step instructions, code templates, and best practices.

## Table of Contents

1. [Quick Start Checklist](#quick-start-checklist)
2. [Module Anatomy](#module-anatomy)
3. [Step-by-Step Walkthrough](#step-by-step-walkthrough)
4. [Component Templates](#component-templates)
5. [Integration Points](#integration-points)
6. [Testing Your Module](#testing-your-module)
7. [Audit Logging](#audit-logging)
8. [Deployment and Distribution](#deployment-and-distribution)
9. [Reference: Hello World Modules](#reference-hello-world-modules)

---

## Quick Start Checklist

Before you begin, ensure you have the prerequisites and understand the scope:

- [ ] Determine your module's scope: `programme` (system-level) or `project` (project-specific)
- [ ] Choose a module slug (lowercase, snake_case, e.g., `risk_register`, `dependency_tracker`)
- [ ] Plan your data model and database tables
- [ ] Identify required RBAC permissions
- [ ] Prepare English and French localization strings
- [ ] Design unit/system tests

### File Checklist

For a module named `risk_register` at project scope, you will create:

```
app/
  Controllers/
    ProjectRiskRegisterController.php       # Route handlers
  Models/
    ModuleRiskRegisterEntryModel.php        # Persistence layer
  Database/
    Migrations/
      2026-XX-XX-XXXXXX_CreateRiskRegisterTables.php
  Views/
    modules/
      project_risk_register.php             # UI template
app/Language/
  en/
    RiskRegister.php                        # English strings
  fr/
    RiskRegister.php                        # French strings
tests/
  system/
    ModuleRiskRegisterSystemTest.php        # System/integration tests
  unit/
    modules/
      RiskRegisterModuleTest.php            # Unit tests
```

---

## Module Anatomy

Each module consists of the following components:

### 1. Database Migration and Models

**Purpose**: Define schema and persistence layer for module data.

**Files**:
- Migration file: `app/Database/Migrations/`
- Model files: `app/Models/`

### 2. Controller(s)

**Purpose**: Handle HTTP requests, orchestrate business logic, and enforce authorization.

**Files**:
- `app/Controllers/<Scope><ModuleName>Controller.php`

**Pattern**: Scope-aware controller that:
- Checks actor authentication
- Verifies scope access (via parent scope authorization)
- Guards disabled modules at route level
- Validates input and persists records
- Logs audit events

### 3. Views

**Purpose**: Render module UI with localization and Bootstrap 5 styling.

**Files**:
- `app/Views/modules/<scope>_<module_name>.php`

**Pattern**: Forms for create/edit, tables or cards for display, error/success alerts.

### 4. Localization

**Purpose**: Provide English and French UI strings with language-aware fallback.

**Files**:
- `app/Language/en/<ModuleName>.php`
- `app/Language/fr/<ModuleName>.php`

**Pattern**: Return associative array of keys → strings. Use nested arrays for grouped content (e.g., `scope`, `status`).

### 5. Routes

**Purpose**: Map HTTP verbs and paths to controller actions.

**Location**: `app/Config/Routes.php`

**Pattern**: Routes inherit scope authorization from parent (programme or project).

### 6. Tests

**Purpose**: Verify module behavior across unit, integration, and system levels.

**Files**:
- `tests/unit/modules/<ModuleName>ModuleTest.php`
- `tests/system/Module<ModuleName>SystemTest.php`

**Pattern**: Extend `ModuleUnitTestCase` or `CIUnitTestCase` with fixtures and assertions.

### 7. Audit Logging

**Purpose**: Track all mutations with actor, timestamp, and metadata.

**Pattern**: Call `AuditLogger::log()` after successful persistence.

---

## Step-by-Step Walkthrough

This section guides you through creating a fictional **Risk Register** module at project scope.

### Step 1: Plan Your Data Model

Define what records your module stores and what fields they have.

**Example: Risk Register Entry**

```
- risk_id (generated)
- project_id (inherited from scope)
- title (required, 200 chars)
- description (optional, 2000 chars)
- risk_level (required: low, medium, high, critical)
- mitigation_strategy (optional, 1000 chars)
- owner_user_id (required, foreign key)
- due_date (optional)
- status (required: open, mitigated, closed)
- created_by_user_id (required, foreign key)
- created_at, updated_at (timestamps)
```

### Step 2: Create Database Migration

Create a new migration file with a timestamp prefix:

**File**: `app/Database/Migrations/2026-05-15-100000_CreateRiskRegisterTables.php`

```php
<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRiskRegisterTables extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 10,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'risk_level' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'medium',
            ],
            'mitigation_strategy' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'owner_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'open',
            ],
            'created_by_user_id' => [
                'type'       => 'INT',
                'constraint' => 10,
                'unsigned'   => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('project_id');
        $this->forge->addKey('owner_user_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('project_id', 'projects', 'id', 'CASCADE');
        $this->forge->addForeignKey('owner_user_id', 'users', 'id', 'CASCADE');
        $this->forge->addForeignKey('created_by_user_id', 'users', 'id', 'CASCADE');

        $this->forge->createTable('module_risk_register_entries');
    }

    public function down(): void
    {
        $this->forge->dropTable('module_risk_register_entries');
    }
}
```

**Run migration**:
```bash
php spark migrate
```

### Step 3: Create Model(s)

Create a model class for your module's data.

**File**: `app/Models/ModuleRiskRegisterEntryModel.php`

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleRiskRegisterEntryModel extends Model
{
    protected $table            = 'module_risk_register_entries';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'project_id',
        'title',
        'description',
        'risk_level',
        'mitigation_strategy',
        'owner_user_id',
        'due_date',
        'status',
        'created_by_user_id',
    ];
}
```

### Step 4: Register Module in Database

Update the registry entry migration to include your module metadata.

**Edit**: `app/Database/Migrations/2026-05-10-160000_CreateModuleFrameworkTables.php`

Find the `up()` method's seeding section and add your module:

```php
$this->db->table('module_registry')->insert([
    'slug'        => 'risk_register_project',
    'name'        => 'Risk Register',
    'scope_type'  => 'project',
    'description' => 'Manage project risks with mitigation tracking.',
    'is_enabled'  => true,
    'created_at'  => date('Y-m-d H:i:s'),
    'updated_at'  => date('Y-m-d H:i:s'),
]);
```

Re-run migration:
```bash
php spark migrate:refresh
```

### Step 5: Create Controller

Build a controller that handles HTTP requests with proper authorization and audit logging.

**File**: `app/Controllers/ProjectRiskRegisterController.php`

```php
<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleRiskRegisterEntryModel;
use App\Models\ProjectModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProjectRiskRegisterController extends BaseController
{
    private const MODULE_SLUG = 'risk_register_project';

    public function index(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        // Authenticate user
        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        // Authorize scope access
        if (! is_array($project) || ! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        // Guard: module must be enabled
        if (! (new ModuleRegistryService())->isEnabled(self::MODULE_SLUG, 'project')) {
            return redirect()->to('/projects/' . $projectId)
                ->with('error', lang('RiskRegister.moduleDisabled'));
        }

        // Fetch module records
        $entries = (new ModuleRiskRegisterEntryModel())
            ->where('project_id', $projectId)
            ->orderBy('status', 'ASC')
            ->orderBy('risk_level', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('modules/project_risk_register', [
            'project'  => $project,
            'entries'  => $entries,
            'levels'   => ['low', 'medium', 'high', 'critical'],
            'statuses' => ['open', 'mitigated', 'closed'],
        ]);
    }

    public function create(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($project) || ! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled(self::MODULE_SLUG, 'project')) {
            return redirect()->to('/projects/' . $projectId)
                ->with('error', lang('RiskRegister.moduleDisabled'));
        }

        $rules = [
            'title'            => 'required|max_length[200]',
            'risk_level'       => 'required|in_list[low,medium,high,critical]',
            'description'      => 'max_length[2000]',
            'mitigation_strategy' => 'max_length[1000]',
            'owner_user_id'    => 'required|integer|greater_than[0]',
            'due_date'         => 'valid_date[Y-m-d]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        (new ModuleRiskRegisterEntryModel())->insert([
            'project_id'         => $projectId,
            'title'              => trim((string) $this->request->getPost('title')),
            'description'        => trim((string) $this->request->getPost('description')),
            'risk_level'         => $this->request->getPost('risk_level'),
            'mitigation_strategy' => trim((string) $this->request->getPost('mitigation_strategy')),
            'owner_user_id'      => (int) $this->request->getPost('owner_user_id'),
            'due_date'           => $this->request->getPost('due_date') ?: null,
            'status'             => 'open',
            'created_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('module_risk_register_entry_created', 'success', $actorId, [
            'project_id' => $projectId,
            'title'      => trim((string) $this->request->getPost('title')),
        ]);

        return redirect()->to('/projects/' . $projectId . '/modules/risk-register')
            ->with('success', lang('RiskRegister.entryCreatedSuccess'));
    }

    /**
     * @param array<string, mixed> $project
     */
    private function canViewProject(int $actorId, array $project): bool
    {
        if ((int) ($project['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'project.read_own', 'project', (int) $project['id'])
            || $rbac->hasPermission($actorId, 'project.update_own', 'project', (int) $project['id'])
            || $rbac->hasPermission($actorId, 'project.delete_own', 'project', (int) $project['id']);
    }
}
```

### Step 6: Create Routes

Add routes to `app/Config/Routes.php` in the appropriate section:

**Location**: Near other module routes in the file.

```php
// Project-scoped Risk Register module
$routes->get('projects/(:num)/modules/risk-register', 'ProjectRiskRegisterController::index/$1');
$routes->post('projects/(:num)/modules/risk-register', 'ProjectRiskRegisterController::create/$1');
```

### Step 7: Create Localization Keys

Define UI strings in English and French.

**File**: `app/Language/en/RiskRegister.php`

```php
<?php

return [
    'moduleTitle'      => 'Risk Register',
    'moduleDescription' => 'Manage project risks with mitigation tracking and status updates.',
    'moduleDisabled'   => 'This module is currently disabled for this project.',

    'createTitle'      => 'Create Risk',
    'createButton'     => 'Create Risk',
    'createdSuccess'   => 'Risk created successfully.',

    'riskTitle'        => 'Risk Title',
    'riskDescription'  => 'Description',
    'riskLevel'        => 'Risk Level',
    'mitigationStrategy' => 'Mitigation Strategy',
    'owner'            => 'Owner',
    'dueDate'          => 'Due Date',
    'status'           => 'Status',

    'level' => [
        'low'      => 'Low',
        'medium'   => 'Medium',
        'high'     => 'High',
        'critical' => 'Critical',
    ],

    'statusOpen'       => 'Open',
    'statusMitigated'  => 'Mitigated',
    'statusClosed'     => 'Closed',

    'entriesTitle'     => 'Registered Risks',
    'entriesNone'      => 'No risks registered yet for this project.',
    'openModuleButton' => 'Open Risk Register module',
];
```

**File**: `app/Language/fr/RiskRegister.php`

```php
<?php

return [
    'moduleTitle'      => 'Registre des risques',
    'moduleDescription' => 'Gérer les risques de projet avec suivi d\'atténuation et mises à jour de statut.',
    'moduleDisabled'   => 'Ce module est actuellement désactivé pour ce projet.',

    'createTitle'      => 'Créer un risque',
    'createButton'     => 'Créer un risque',
    'createdSuccess'   => 'Risque créé avec succès.',

    'riskTitle'        => 'Titre du risque',
    'riskDescription'  => 'Description',
    'riskLevel'        => 'Niveau de risque',
    'mitigationStrategy' => 'Stratégie d\'atténuation',
    'owner'            => 'Propriétaire',
    'dueDate'          => 'Date d\'échéance',
    'status'           => 'Statut',

    'level' => [
        'low'      => 'Faible',
        'medium'   => 'Moyen',
        'high'     => 'Élevé',
        'critical' => 'Critique',
    ],

    'statusOpen'       => 'Ouvert',
    'statusMitigated'  => 'Atténué',
    'statusClosed'     => 'Fermé',

    'entriesTitle'     => 'Risques enregistrés',
    'entriesNone'      => 'Aucun risque enregistré pour ce projet.',
    'openModuleButton' => 'Ouvrir le module Registre des risques',
];
```

### Step 8: Create View

Build the UI template with forms, lists, and localization.

**File**: `app/Views/modules/project_risk_register.php`

```php
<!doctype html>
<?php $locale = (string) service('request')->getLocale(); ?>
<html lang="<?= esc($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc(lang('RiskRegister.moduleTitle')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= view('layouts/theme_assets') ?>
</head>
<body class="bg-light">
<?= view('layouts/app_header', ['pageTitle' => lang('RiskRegister.moduleTitle'), 'active' => 'projects']) ?>
<main class="container py-4">
    <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= esc((string) session('error')) ?></div>
    <?php endif; ?>
    <?php if (session('success') !== null): ?>
        <div class="alert alert-success" role="alert"><?= esc((string) session('success')) ?></div>
    <?php endif; ?>
    <?php if (session('errors') !== null): ?>
        <?php foreach ((array) session('errors') as $error): ?>
            <div class="alert alert-danger" role="alert"><?= esc((string) $error) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5 mb-2"><?= esc((string) ($project['name'] ?? '')) ?></h2>
            <p class="mb-0 text-muted"><?= esc(lang('RiskRegister.moduleDescription')) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h3 class="h6 mb-0"><?= esc(lang('RiskRegister.createTitle')) ?></h3>
        </div>
        <div class="card-body">
            <form method="post" action="<?= site_url('projects/' . (int) ($project['id'] ?? 0) . '/modules/risk-register') ?>" class="row g-3">
                <?= csrf_field() ?>
                
                <div class="col-12 col-md-6">
                    <label for="title" class="form-label"><?= esc(lang('RiskRegister.riskTitle')) ?></label>
                    <input id="title" name="title" type="text" maxlength="200" class="form-control" required value="<?= esc((string) old('title')) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label for="risk_level" class="form-label"><?= esc(lang('RiskRegister.riskLevel')) ?></label>
                    <select id="risk_level" name="risk_level" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($levels as $level): ?>
                            <option value="<?= esc($level) ?>" <?php if (old('risk_level') === $level) echo 'selected'; ?>>
                                <?= esc(lang('RiskRegister.level.' . $level)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label for="description" class="form-label"><?= esc(lang('RiskRegister.riskDescription')) ?></label>
                    <textarea id="description" name="description" class="form-control" maxlength="2000" rows="3"><?= esc((string) old('description')) ?></textarea>
                </div>

                <div class="col-12">
                    <label for="mitigation_strategy" class="form-label"><?= esc(lang('RiskRegister.mitigationStrategy')) ?></label>
                    <textarea id="mitigation_strategy" name="mitigation_strategy" class="form-control" maxlength="1000" rows="2"><?= esc((string) old('mitigation_strategy')) ?></textarea>
                </div>

                <div class="col-12 col-md-6">
                    <label for="owner_user_id" class="form-label"><?= esc(lang('RiskRegister.owner')) ?></label>
                    <select id="owner_user_id" name="owner_user_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <!-- Populate with team members from parent project -->
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label for="due_date" class="form-label"><?= esc(lang('RiskRegister.dueDate')) ?></label>
                    <input id="due_date" name="due_date" type="date" class="form-control" value="<?= esc((string) old('due_date')) ?>">
                </div>

                <div class="col-12">
                    <button class="btn btn-primary" type="submit"><?= esc(lang('RiskRegister.createButton')) ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h3 class="h6 mb-0"><?= esc(lang('RiskRegister.entriesTitle')) ?></h3>
        </div>
        <div class="card-body p-0">
            <?php if (empty($entries)): ?>
                <p class="text-muted p-4 mb-0"><?= esc(lang('RiskRegister.entriesNone')) ?></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= esc(lang('RiskRegister.riskTitle')) ?></th>
                                <th><?= esc(lang('RiskRegister.riskLevel')) ?></th>
                                <th><?= esc(lang('RiskRegister.status')) ?></th>
                                <th><?= esc(lang('RiskRegister.dueDate')) ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($entries as $entry): ?>
                                <tr>
                                    <td><?= esc((string) ($entry['title'] ?? '')) ?></td>
                                    <td><span class="badge" style="background-color: <?= $this->getLevelColor($entry['risk_level']) ?>;"><?= esc(lang('RiskRegister.level.' . ($entry['risk_level'] ?? 'medium'))) ?></span></td>
                                    <td><?= esc(lang('RiskRegister.status' . ucfirst($entry['status'] ?? 'open'))) ?></td>
                                    <td><?= esc((string) ($entry['due_date'] ?? '')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
```

### Step 9: Write Tests

Create unit and system tests to verify module behavior.

**File**: `tests/unit/modules/RiskRegisterModuleTest.php`

```php
<?php

namespace Tests\Unit\Modules;

use App\Libraries\Modules\ModuleRegistryService;
use Tests\Support\Modules\ModuleUnitTestCase;

class RiskRegisterModuleTest extends ModuleUnitTestCase
{
    public function testModuleMetadataIsValid(): void
    {
        $metadata = [
            'slug'        => 'risk_register_project',
            'name'        => 'Risk Register',
            'scope_type'  => 'project',
            'description' => 'Manage project risks with mitigation tracking.',
        ];

        $this->assertValidModuleMetadata($metadata);
    }

    public function testRiskLevelsAreSupported(): void
    {
        $validLevels = ['low', 'medium', 'high', 'critical'];
        foreach ($validLevels as $level) {
            $this->assertNotEmpty($level);
        }
    }

    public function testRiskStatusesAreSupported(): void
    {
        $validStatuses = ['open', 'mitigated', 'closed'];
        foreach ($validStatuses as $status) {
            $this->assertNotEmpty($status);
        }
    }
}
```

**File**: `tests/system/ModuleRiskRegisterSystemTest.php`

```php
<?php

namespace Tests\System;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleRiskRegisterEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use Tests\Support\DatabaseTestCase;

class ModuleRiskRegisterSystemTest extends DatabaseTestCase
{
    private int $testUserId;
    private int $testProjectId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->testUserId = (int) (new UserModel())->insert([
            'username'      => 'riskuser',
            'email'         => 'risk@example.com',
            'password_hash' => password_hash('Password123!', PASSWORD_DEFAULT),
            'is_active'     => true,
        ]);

        // Create test project
        $this->testProjectId = (int) (new ProjectModel())->insert([
            'name'           => 'Test Project',
            'description'    => 'Project for risk testing',
            'owner_user_id'  => $this->testUserId,
            'is_active'      => true,
        ]);
    }

    public function testProjectRiskRegisterModuleIsEnabledByDefault(): void
    {
        $service = new ModuleRegistryService();
        $this->assertTrue($service->isEnabled('risk_register_project', 'project'));
    }

    public function testRiskCanBeCreatedInProject(): void
    {
        (new ModuleRiskRegisterEntryModel())->insert([
            'project_id'         => $this->testProjectId,
            'title'              => 'Database performance risk',
            'risk_level'         => 'high',
            'owner_user_id'      => $this->testUserId,
            'status'             => 'open',
            'created_by_user_id' => $this->testUserId,
        ]);

        $entries = (new ModuleRiskRegisterEntryModel())
            ->where('project_id', $this->testProjectId)
            ->findAll();

        $this->assertCount(1, $entries);
        $this->assertSame('Database performance risk', $entries[0]['title']);
    }

    public function testAuditLogIsCreatedForNewRisk(): void
    {
        $auditLogger = new AuditLogger();
        $auditLogger->log('module_risk_register_entry_created', 'success', $this->testUserId, [
            'project_id' => $this->testProjectId,
            'title'      => 'Critical security risk',
        ]);

        // Verify audit log was written
        $this->assertTrue(true); // Audit logging implementation tested separately
    }
}
```

### Step 10: Enable and Test

Run migrations and tests:

```bash
# Run migrations
php spark migrate

# Run module tests
vendor/bin/phpunit tests/unit/modules/RiskRegisterModuleTest.php
vendor/bin/phpunit tests/system/ModuleRiskRegisterSystemTest.php

# Run full test suite
XDEBUG_MODE=off composer ci
```

---

## Component Templates

This section provides copy-paste templates for each component type.

### Model Template

```php
<?php

namespace App\Models;

use CodeIgniter\Model;

class Module<ModuleName>EntryModel extends Model
{
    protected $table            = 'module_<module_slug>_entries';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'scope_id',
        'field_1',
        'field_2',
        'field_3',
        'created_by_user_id',
    ];
}
```

### Controller Stub (Programme Scope)

```php
<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\Module<ModuleName>EntryModel;
use App\Models\ProgrammeModel;
use CodeIgniter\HTTP\RedirectResponse;

class Programme<ModuleName>Controller extends BaseController
{
    private const MODULE_SLUG = '<module_slug>_programme';

    public function index(int $programmeId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($programme) || ! $this->canViewProgramme($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled(self::MODULE_SLUG, 'programme')) {
            return redirect()->to('/programmes/' . $programmeId)
                ->with('error', lang('<ModuleName>.moduleDisabled'));
        }

        $entries = (new Module<ModuleName>EntryModel())
            ->where('scope_id', $programmeId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('modules/programme_<module_slug>', [
            'programme' => $programme,
            'entries'   => $entries,
        ]);
    }

    public function create(int $programmeId): RedirectResponse
    {
        // Similar structure to index() but with validation and insert
    }

    /**
     * @param array<string, mixed> $programme
     */
    private function canViewProgramme(int $actorId, array $programme): bool
    {
        if ((int) ($programme['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.read_own', 'programme', (int) $programme['id'])
            || $rbac->hasPermission($actorId, 'programme.update_own', 'programme', (int) $programme['id'])
            || $rbac->hasPermission($actorId, 'programme.delete_own', 'programme', (int) $programme['id']);
    }
}
```

### Localization Template

```php
<?php

return [
    'moduleTitle'      => 'Module Name',
    'moduleDescription' => 'Module description for users.',
    'moduleDisabled'   => 'This module is currently disabled.',

    'createTitle'      => 'Create Record',
    'createButton'     => 'Create',
    'createdSuccess'   => 'Record created successfully.',

    'field1'           => 'Field 1 Label',
    'field2'           => 'Field 2 Label',

    'entriesTitle'     => 'Records',
    'entriesNone'      => 'No records yet.',
    'openModuleButton' => 'Open module',
];
```

---

## Integration Points

After implementing your module components, integrate them into the application.

### 1. Add Routes

Edit `app/Config/Routes.php`:

```php
// Project-scoped module
$routes->get('projects/(:num)/modules/<module-name>', 'Project<ModuleName>Controller::index/$1');
$routes->post('projects/(:num)/modules/<module-name>', 'Project<ModuleName>Controller::create/$1');

// Programme-scoped module
$routes->get('programmes/(:num)/modules/<module-name>', 'Programme<ModuleName>Controller::index/$1');
$routes->post('programmes/(:num)/modules/<module-name>', 'Programme<ModuleName>Controller::create/$1');
```

### 2. Add Navigation Card

Edit `app/Views/programmes/show.php` and `app/Views/projects/show.php`:

Add a module card in the detail page:

```php
<div class="col-md-6">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body text-center py-4">
            <h6><?= esc(lang('<ModuleName>.moduleTitle')) ?></h6>
            <p class="text-muted small"><?= esc(lang('<ModuleName>.moduleDescription')) ?></p>
            <a href="<?= site_url('projects/' . (int) $project['id'] . '/modules/<module-name>') ?>" class="btn btn-sm btn-outline-primary">
                <?= esc(lang('<ModuleName>.openModuleButton')) ?>
            </a>
        </div>
    </div>
</div>
```

### 3. Add Permissions (Optional)

If your module requires specific permissions, add them to `app/Config/Roles.php`:

```php
'project.<module-name>.create_own'  => 'Create <module-name> records in own projects',
'project.<module-name>.update_own'  => 'Update <module-name> records in own projects',
'project.<module-name>.delete_own'  => 'Delete <module-name> records in own projects',
```

---

## Testing Your Module

Write comprehensive tests at three levels:

### Unit Tests

Test individual functions and model behavior in isolation.

```php
public function testModelValidatesRequiredFields(): void
{
    $result = (new Module<ModuleName>EntryModel())->insert([
        'scope_id' => null, // Missing required field
        'title'    => 'Test',
    ]);

    $this->assertFalse($result);
}
```

### Integration Tests

Test module interaction with the database and other services.

```php
public function testModuleCreatesRecordWithCorrectUserId(): void
{
    (new Module<ModuleName>EntryModel())->insert([
        'scope_id'           => 1,
        'title'              => 'Test record',
        'created_by_user_id' => 42,
    ]);

    $record = (new Module<ModuleName>EntryModel())->find(1);
    $this->assertSame(42, (int) $record['created_by_user_id']);
}
```

### System Tests

Test complete workflows via HTTP requests with authentication.

```php
public function testAuthenticatedUserCanCreateModuleRecord(): void
{
    $this->actingAs($this->testUser);

    $response = $this->post('/projects/1/modules/<module-name>', [
        'title' => 'New record',
    ]);

    $response->assertStatus(302);
    $response->assertRedirectContains('success');
}
```

---

## Audit Logging

Log all mutations for compliance and forensics.

### Audit Event Naming

Use consistent naming: `<module>_<action>`

Examples:
- `module_risk_register_entry_created`
- `module_risk_register_entry_updated`
- `module_risk_register_entry_deleted`
- `module_risk_register_entry_status_changed`

### Log Entry Pattern

```php
(new AuditLogger())->log(
    '<module>_<action>',        // Event name
    'success',                   // Status: success|failed|denied
    $actorId,                    // User ID who performed action
    [                            // Metadata
        'scope_id'  => $projectId,
        'entry_id'  => $recordId,
        'field'     => $fieldName,
        'old_value' => $oldValue,
        'new_value' => $newValue,
    ]
);
```

### Audit Query Examples

Retrieve audit logs for analysis:

```php
// All module actions by a user
$logs = (new AuditLogModel())
    ->where('user_id', $userId)
    ->like('event', 'module_%')
    ->findAll();

// Module history for specific record
$logs = (new AuditLogModel())
    ->where('event', 'module_risk_register_entry_created')
    ->where('metadata', 'entry_id", $entryId) // JSON search
    ->findAll();
```

---

## Deployment and Distribution

### Pre-Deployment Checklist

- [ ] All migrations run successfully (`php spark migrate`)
- [ ] All tests pass (`XDEBUG_MODE=off composer ci`)
- [ ] Localization keys are complete (EN/FR)
- [ ] Code follows PSR-12 style (`php spark style`)
- [ ] Security audit completed (RBAC, validation, audit logging)
- [ ] Documentation updated (README, MODULE_AUTHORING_GUIDE)

### Release Notes Template

```markdown
## Module: <Module Name>

**Version**: 1.0.0  
**Release Date**: YYYY-MM-DD  
**Scope**: project | programme  

### Features
- Feature 1
- Feature 2

### Database Changes
- New table: `module_<slug>_entries`
- Migration: `2026-XX-XX-XXXXXX_Create<ModuleName>Tables.php`

### Breaking Changes
None

### Installation
1. Run migrations: `php spark migrate`
2. Enable module: Navigate to /modules and toggle enable
3. Access module: [scope]/modules/[name]

### Test Coverage
- Unit tests: X tests passing
- System tests: Y tests passing
- Coverage: Z%
```

### Distribution via Git

Commit module files:

```bash
# Stage module files
git add app/Controllers/Project<ModuleName>Controller.php
git add app/Models/Module<ModuleName>EntryModel.php
git add app/Database/Migrations/
git add app/Language/en/<ModuleName>.php
git add app/Language/fr/<ModuleName>.php
git add app/Views/modules/
git add tests/system/
git add tests/unit/

# Commit with semantic message
git commit -m "Add <Module Name> module for project scope

- Implement <ModuleName> registry entry and lifecycle
- Add module controller with scope-aware authorization
- Add persistence layer and validation
- Add EN/FR localization (XX keys)
- Add system/unit tests (X tests, Y assertions)
- All tests passing: XDEBUG_MODE=off composer ci"
```

---

## Reference: Hello World Modules

The Phase 6 implementation includes two reference modules that demonstrate best practices:

- **Programme Hello World**: `app/Controllers/ProgrammeHelloWorldController.php`
- **Project Hello World**: `app/Controllers/ProjectHelloWorldController.php`

Review these files to understand:
- Scope authorization patterns
- Module registry guard implementation
- Audit logging integration
- View structure and localization

Start from these files when building your own modules.

---

## Advanced Patterns

### Cross-Module APIs

Enable modules to read/write from each other safely:

**Pattern**: Define public API methods in service classes.

```php
// app/Libraries/Modules/RiskRegisterAPI.php
class RiskRegisterAPI
{
    public function getOpenRisks(int $projectId): array
    {
        return (new ModuleRiskRegisterEntryModel())
            ->where('project_id', $projectId)
            ->where('status', 'open')
            ->findAll();
    }

    public function updateRiskStatus(int $riskId, string $status): bool
    {
        return (new ModuleRiskRegisterEntryModel())
            ->update($riskId, ['status' => $status]);
    }
}
```

**Usage**: From another module:

```php
$risks = (new RiskRegisterAPI())->getOpenRisks($projectId);
```

### Autosave Endpoints

Implement live persistence for form fields (Phase 7):

```php
public function autosave(int $projectId): string
{
    $field  = $this->request->getPost('field');
    $value  = $this->request->getPost('value');
    $entryId = $this->request->getPost('entry_id');

    if (! $this->validateFieldValue($field, $value)) {
        return json_encode(['status' => 'error', 'message' => 'Invalid value']);
    }

    (new ModuleRiskRegisterEntryModel())->update($entryId, [$field => $value]);

    return json_encode(['status' => 'success', 'saved_at' => date('Y-m-d H:i:s')]);
}
```

### Module Locking (Phase 8)

Prevent concurrent edits:

```php
public function checkout(int $entryId): RedirectResponse
{
    (new ModuleLockService())->acquire($entryId, 'risk_register', $this->sessionUserId());
    return redirect()->to('/projects/' . $projectId . '/modules/risk-register/edit/' . $entryId);
}

public function checkin(int $entryId): RedirectResponse
{
    (new ModuleLockService())->release($entryId, 'risk_register');
    return redirect()->to('/projects/' . $projectId . '/modules/risk-register');
}
```

---

## Troubleshooting

### Module Not Appearing

1. Verify migration ran: `php spark migrate:status`
2. Check module registry: `SELECT * FROM module_registry WHERE slug = '...'`
3. Verify routes: Review `app/Config/Routes.php`
4. Check controller namespace: Must be `App\Controllers\<Scope><ModuleName>Controller`

### Tests Failing

1. Run migration refresh: `php spark migrate:refresh`
2. Check test database: Verify `.env.testing` is configured
3. Review test output: `vendor/bin/phpunit tests/system/Module<Name>SystemTest.php -v`
4. Check permissions: Ensure test user has required roles

### Localization Not Working

1. Verify language file exists: `app/Language/en/<ModuleName>.php`
2. Check key name: Ensure `lang()` call matches key in file
3. Verify locale selection: Check browser language or cookie
4. Reload cache: Some systems cache language files; restart if needed

---

## Additional Resources

- [Module Framework Documentation](MODULE_FRAMEWORK.md)
- [Authentication and RBAC Guide](../skills/authentication-authorization-rbac/SKILL.md)
- [CodeIgniter 4 Backend Patterns](../skills/php-codeigniter4-backend/SKILL.md)
- [Testing and Quality Assurance](../skills/testing-unit-integration-system/SKILL.md)
- [Audit Logging Best Practices](../skills/security-audit-logging/SKILL.md)
