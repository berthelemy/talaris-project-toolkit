<?php

namespace App\Modules\DecisionsRegisterProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Decisions Register module system coverage.
 *
 * @internal
 */
final class DecisionsRegisterProjectSystemTest extends ModuleSystemTestCase
{
    public function testDecisionsModuleCreatesRecordWithDateAndActor(): void
    {
        $manager = $this->createUser('decision_user', 'decision_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Decisions Module Project 1');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/decisions-register', [
                'description' => 'Approved moving milestone two by one week.',
                'decision_date' => '2026-05-11',
                'made_by_user_id' => (int) $manager['id'],
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/decisions-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'decisions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('2026-05-11', (string) ($entry['decision_date'] ?? ''));
    }

    public function testDecisionsModuleSupportsLifecycleAndExtendedFields(): void
    {
        $manager = $this->createUser('decision_lifecycle_user', 'decision_lifecycle_user@example.com');
        $approver = $this->createUser('decision_approver_user', 'decision_approver_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Decisions Module Project 2');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/decisions-register', [
                'title' => 'Adopt integration pattern',
                'description' => 'Adopt event-driven integration for module orchestration.',
                'decision_date' => '2026-05-16',
                'made_by_user_id' => (int) $manager['id'],
                'approver_user_id' => (int) $approver['id'],
                'status' => 'approved',
                'decision_category' => 'Architecture',
                'decision_rationale' => 'Reduce coupling across modules.',
                'alternatives_considered' => 'Synchronous direct calls.',
                'chosen_option' => 'Event-driven internal API.',
                'implementation_actions' => 'Create event contracts and handlers.',
                'priority' => 'high',
                'target_date' => '2026-06-01',
                'review_date' => '2026-05-25',
                'lessons_learned' => 'Document decision context for future teams.',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/decisions-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'decisions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('approved', (string) ($entry['status'] ?? ''));
    }

    public function testDecisionsAndDependenciesWidgetsExposeRequiredCards(): void
    {
        $manager = $this->createUser('decision_dep_widgets_user', 'decision_dep_widgets_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Decisions Module Project 3');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'decisions_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Key decision for widget',
            'description' => 'Widget coverage decision.',
            'owner_user_id' => (int) $manager['id'],
            'decision_date' => '2026-05-16',
            'made_by_user_id' => (int) $manager['id'],
            'status' => 'approved',
            'priority' => 'high',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'dependencies_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'At-risk dependency for widget',
            'description' => 'Widget coverage dependency.',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'at_risk',
            'priority' => 'high',
            'impact_level' => 'high',
            'target_date' => '2026-05-01',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $projectOverview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $projectOverview->assertOK();
        $body = $projectOverview->getBody();

        $this->assertStringContainsString((string) lang('Module.decisionsWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.decisionsWidgetPendingTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.decisionsWidgetRecentKeyTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.dependenciesWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.dependenciesWidgetAtRiskTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.dependenciesWidgetOverdueTitle'), $body);
    }
}
