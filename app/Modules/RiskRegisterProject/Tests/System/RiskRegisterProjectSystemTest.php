<?php

/**
 * File documentation for app/Modules/RiskRegisterProject/Tests/System/RiskRegisterProjectSystemTest.php.
 */

namespace App\Modules\RiskRegisterProject\Tests\System;

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\RaidEntryStorageModel;
use App\Modules\TestSupport\Testing\ModuleSystemTestCase;

/**
 * Risk Register module system coverage.
 *
 * @internal
 */
final class RiskRegisterProjectSystemTest extends ModuleSystemTestCase
{
    public function testProjectManagerCanCreateUpdateAndCloseRiskEntryWithAuditLogs(): void
    {
        $manager = $this->createUser('risk_manager', 'risk_manager@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 1');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/risk-register', [
                'title' => 'Late vendor delivery',
                'description' => 'Critical package may be delayed.',
                'mitigation_actions' => 'Escalate to procurement and add alternate supplier.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'impact' => 'high',
                'likelihood' => 'high',
                'target_date' => '2026-06-01',
                'review_date' => '2026-05-20',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/risk-register');

        $entry = (new RaidEntryStorageModel())
            ->where('module_slug', 'risk_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('critical', (string) ($entry['priority'] ?? ''));

        $entryId = (int) $entry['id'];

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/risk-register/' . $entryId . '/update', [
                'title' => 'Late vendor delivery updated',
                'description' => 'Updated mitigation actions.',
                'mitigation_actions' => 'Escalation completed.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'in_review',
                'impact' => 'medium',
                'likelihood' => 'high',
                'target_date' => '2026-06-10',
                'review_date' => '2026-05-25',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/risk-register');

        $close = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/risk-register/' . $entryId . '/close');

        $close->assertRedirectTo('/projects/' . $projectId . '/modules/risk-register');

        $updatedEntry = (new RaidEntryStorageModel())->find($entryId);
        $this->assertIsArray($updatedEntry);
        $this->assertSame('closed', (string) ($updatedEntry['status'] ?? ''));
        $this->assertNotEmpty((string) ($updatedEntry['closed_at'] ?? ''));

        $auditModel = new AuthAuditLogModel();
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_created')->first());
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_updated')->first());
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_closed')->first());
    }

    public function testRiskWidgetDrillDownLinkTargetsExistingEntryAnchor(): void
    {
        $manager = $this->createUser('risk_widget_user', 'risk_widget_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 2');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $entryId = (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'High probability outage',
            'description' => 'Service provider uptime risk.',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'impact' => 'high',
            'likelihood' => 'high',
            'priority' => 'critical',
            'mitigation_actions' => 'Add standby provider.',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ], true);

        $projectOverview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $projectOverview->assertOK();
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/risk-register#entry-' . $entryId, $projectOverview->getBody());

        $modulePage = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/risk-register');

        $modulePage->assertOK();
        $this->assertStringContainsString('id="entry-' . $entryId . '"', $modulePage->getBody());
    }

    public function testRiskWidgetUpdatesImmediatelyAfterCreateFromModalPath(): void
    {
        $manager = $this->createUser('risk_cache_user', 'risk_cache_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 3');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId)
            ->assertOK();

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/risk-register', [
                'title' => 'Fresh modal risk',
                'impact' => 'high',
                'likelihood' => 'high',
                'mitigation_actions' => 'Immediate mitigation',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/risk-register');

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $this->assertStringContainsString('Fresh modal risk', $overview->getBody());
    }

    public function testRiskWidgetShowsOpenCountsAndHighPriorityList(): void
    {
        $manager = $this->createUser('risk_counts_user', 'risk_counts_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 4');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Critical outage risk',
            'description' => 'Critical risk entry',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'impact' => 'high',
            'likelihood' => 'high',
            'priority' => 'critical',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'High vendor risk',
            'description' => 'High risk entry',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'impact' => 'high',
            'likelihood' => 'medium',
            'priority' => 'high',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Medium planning risk',
            'description' => 'Medium risk entry',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'impact' => 'medium',
            'likelihood' => 'medium',
            'priority' => 'medium',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Closed low risk',
            'description' => 'Closed risk should not count',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'closed',
            'impact' => 'low',
            'likelihood' => 'low',
            'priority' => 'low',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
            'closed_at' => date('Y-m-d H:i:s'),
        ]);

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $body = $overview->getBody();

        $this->assertStringContainsString((string) lang('Module.riskWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.riskWidgetHighPriorityTitle'), $body);
        $this->assertStringContainsString('Critical outage risk', $body);
        $this->assertStringContainsString('High vendor risk', $body);
        $this->assertStringContainsString('>1</div>', $body);
        $this->assertStringNotContainsString('Closed low risk', $body);
    }

    public function testRiskModulePageUsesModalCreateWithFullRiskFields(): void
    {
        $manager = $this->createUser('risk_modal_user', 'risk_modal_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 5');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $page = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/risk-register');

        $page->assertOK();
        $body = $page->getBody();

        $this->assertStringContainsString('id="risk-add-entry-button"', $body);
        $this->assertStringContainsString('id="riskEntryCreateModal"', $body);
        $this->assertStringContainsString('name="title"', $body);
        $this->assertStringContainsString('name="description"', $body);
        $this->assertStringContainsString('name="mitigation_actions"', $body);
        $this->assertStringContainsString('name="owner_user_id"', $body);
        $this->assertStringContainsString('name="status"', $body);
        $this->assertStringContainsString('name="impact"', $body);
        $this->assertStringContainsString('name="likelihood"', $body);
        $this->assertStringContainsString('name="target_date"', $body);
        $this->assertStringContainsString('name="review_date"', $body);
    }

    public function testRiskEditFormIncludesAllRiskFields(): void
    {
        $manager = $this->createUser('risk_edit_user', 'risk_edit_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 6');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new RaidEntryStorageModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Editable risk',
            'description' => 'Editable description',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'high',
            'impact' => 'high',
            'likelihood' => 'medium',
            'mitigation_actions' => 'Initial mitigation',
            'target_date' => '2026-06-15',
            'review_date' => '2026-05-30',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $page = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/risk-register');

        $page->assertOK();
        $body = $page->getBody();

        $this->assertStringContainsString('data-risk-edit-form="true"', $body);
        $this->assertStringContainsString('data-risk-row-editable', $body);
        $this->assertStringContainsString('data-risk-edit-toggle', $body);
        $this->assertStringContainsString('data-risk-edit-save', $body);
        $this->assertStringContainsString('data-risk-edit-cancel', $body);
    }

    public function testRiskWidgetModalMatchesMainRiskModalFields(): void
    {
        $manager = $this->createUser('risk_widget_modal_user', 'risk_widget_modal_user@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'Risk Module Project 7');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $body = $overview->getBody();

        $this->assertStringContainsString('data-bs-target="#riskModalAdd"', $body);
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/risk-register', $body);
    }
}
