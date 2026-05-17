<?php

/**
 * File documentation for app/Modules/DependenciesRegisterProject/Tests/System/DependenciesRegisterProjectSystemTest.php.
 */

namespace App\Modules\DependenciesRegisterProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Dependencies Register module system coverage.
 *
 * @internal
 */
final class DependenciesRegisterProjectSystemTest extends ModuleSystemTestCase
{
    public function testDependenciesModuleSupportsLifecycleAndExtendedFields(): void
    {
        $manager = $this->createUser('dependency_lifecycle_user', 'dependency_lifecycle_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Dependencies Module Project 1');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/dependencies-register', [
                'title' => 'Security vendor assessment',
                'description' => 'Assessment needed before release gate.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'at_risk',
                'priority' => 'critical',
                'impact_level' => 'high',
                'dependency_type' => 'external',
                'related_work_package' => 'Release readiness gate',
                'depends_on' => 'External security audit partner',
                'mitigation_actions' => 'Escalate procurement and define fallback supplier.',
                'escalation_required' => '1',
                'target_date' => '2026-05-01',
                'review_date' => '2026-05-10',
                'lessons_learned' => 'Early vendor engagement is required.',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/dependencies-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'dependencies_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('at_risk', (string) ($entry['status'] ?? ''));
    }
}
