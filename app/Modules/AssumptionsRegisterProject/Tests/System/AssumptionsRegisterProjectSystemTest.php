<?php

/**
 * File documentation for app/Modules/AssumptionsRegisterProject/Tests/System/AssumptionsRegisterProjectSystemTest.php.
 */

namespace App\Modules\AssumptionsRegisterProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Assumptions Register module system coverage.
 *
 * @internal
 */
final class AssumptionsRegisterProjectSystemTest extends ModuleSystemTestCase
{
    public function testAssumptionsModuleCreatesRecord(): void
    {
        $manager = $this->createUser('assumption_user', 'assumption_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Assumptions Module Project 1');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/assumptions-register', [
                'title' => 'Data source remains available',
                'description' => 'Third-party feed availability assumption.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'priority' => 'medium',
                'target_date' => '2026-06-15',
                'review_date' => '2026-05-30',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/assumptions-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'assumptions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('Data source remains available', (string) ($entry['title'] ?? ''));
    }

    public function testAssumptionsAndDependenciesModulesCreateRecords(): void
    {
        $manager = $this->createUser('assumption_dependency_user', 'assumption_dependency_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Assumptions Module Project 2');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $assumptionCreate = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/assumptions-register', [
                'title' => 'Data source remains available',
                'description' => 'Third-party feed availability assumption.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'priority' => 'medium',
                'target_date' => '2026-06-15',
                'review_date' => '2026-05-30',
            ]);

        $assumptionCreate->assertRedirectTo('/projects/' . $projectId . '/modules/assumptions-register');

        $dependencyCreate = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/dependencies-register', [
                'title' => 'Vendor API credentials',
                'description' => 'Need credentials before integration testing.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'priority' => 'high',
                'target_date' => '2026-06-05',
                'review_date' => '2026-05-25',
            ]);

        $dependencyCreate->assertRedirectTo('/projects/' . $projectId . '/modules/dependencies-register');

        $assumptionEntry = (new RaidEntryStorageModel())
            ->where('module_slug', 'assumptions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $dependencyEntry = (new RaidEntryStorageModel())
            ->where('module_slug', 'dependencies_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($assumptionEntry);
        $this->assertIsArray($dependencyEntry);
    }
}
