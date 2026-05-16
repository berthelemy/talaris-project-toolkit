<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleRaidEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class RaidModulesSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testProjectManagerCanCreateUpdateAndCloseRiskEntryWithAuditLogs(): void
    {
        $manager = $this->createUser('raidmanager', 'raidmanager@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 1');

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

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'risk_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('Late vendor delivery', (string) $entry['title']);
        $this->assertSame('critical', (string) $entry['priority']);

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

        $updatedEntry = (new ModuleRaidEntryModel())->find($entryId);
        $this->assertIsArray($updatedEntry);
        $this->assertSame('closed', (string) $updatedEntry['status']);
        $this->assertNotEmpty((string) ($updatedEntry['closed_at'] ?? ''));

        $auditModel = new AuthAuditLogModel();
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_created')->first());
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_updated')->first());
        $this->assertNotNull($auditModel->where('event_type', 'raid_entry_closed')->first());
    }

    public function testTeamMemberHasReadOnlyAccessAndCannotCreateRaidEntry(): void
    {
        $owner = $this->createUser('raidowner', 'raidowner@example.com');
        $member = $this->createUser('raidmember', 'raidmember@example.com');
        $projectId = $this->createProject((int) $owner['id'], 'RAID Project 2');

        (new RbacService())->assignRoleToUser((int) $member['id'], 'team_member', 'project', $projectId, (int) $owner['id']);

        $page = $this->withSession($this->authSession($member))
            ->get('/projects/' . $projectId . '/modules/issue-tracker');

        $page->assertOK();
        $this->assertStringContainsString(strtolower((string) lang('Module.readOnlyNotice')), strtolower($page->getBody()));

        $post = $this->withSession($this->authSession($member))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker', [
                'title' => 'Blocked by dependency',
                'description' => 'Cannot proceed',
                'owner_user_id' => (int) $member['id'],
                'status' => 'open',
                'priority' => 'medium',
                'target_date' => '2026-06-01',
                'review_date' => '2026-05-21',
            ]);

        $post->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertNull($entry);
    }

    public function testAssumptionsAndDependenciesModulesCreateRecords(): void
    {
        $manager = $this->createUser('raidmanager2', 'raidmanager2@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 3');

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

        $assumptionEntry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'assumptions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $dependencyEntry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'dependencies_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($assumptionEntry);
        $this->assertIsArray($dependencyEntry);
    }

    public function testDecisionsModuleCreatesRecordWithDateAndActor(): void
    {
        $manager = $this->createUser('raidmanager4', 'raidmanager4@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 5');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/decisions-register', [
                'description' => 'Approved moving milestone two by one week.',
                'decision_date' => '2026-05-11',
                'made_by_user_id' => (int) $manager['id'],
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/decisions-register');

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'decisions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('2026-05-11', (string) ($entry['decision_date'] ?? ''));
        $this->assertSame((int) $manager['id'], (int) ($entry['made_by_user_id'] ?? 0));
    }

    public function testDecisionsModuleSupportsLifecycleAndExtendedFields(): void
    {
        $manager = $this->createUser('raidmanager_decision_lifecycle', 'raidmanager_decision_lifecycle@example.com');
        $approver = $this->createUser('raidapprover_decision_lifecycle', 'raidapprover_decision_lifecycle@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Decisions Lifecycle');

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

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'decisions_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('approved', (string) ($entry['status'] ?? ''));
        $this->assertSame('Architecture', (string) ($entry['decision_category'] ?? ''));
        $this->assertSame((int) $approver['id'], (int) ($entry['approver_user_id'] ?? 0));
        $this->assertSame('Create event contracts and handlers.', (string) ($entry['implementation_actions'] ?? ''));

        $entryId = (int) ($entry['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/decisions-register/' . $entryId . '/update', [
                'title' => 'Adopt integration pattern',
                'description' => 'Implementation complete and validated.',
                'decision_date' => '2026-05-16',
                'made_by_user_id' => (int) $manager['id'],
                'approver_user_id' => (int) $approver['id'],
                'status' => 'implemented',
                'decision_category' => 'Architecture',
                'decision_rationale' => 'Reduce coupling across modules.',
                'alternatives_considered' => 'Synchronous direct calls.',
                'chosen_option' => 'Event-driven internal API.',
                'implementation_actions' => 'Implemented and rolled out.',
                'priority' => 'high',
                'target_date' => '2026-06-01',
                'review_date' => '2026-05-28',
                'lessons_learned' => 'Implementation completed with lower risk.',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/decisions-register');

        $updatedEntry = (new ModuleRaidEntryModel())->find($entryId);
        $this->assertIsArray($updatedEntry);
        $this->assertSame('implemented', (string) ($updatedEntry['status'] ?? ''));
        $this->assertSame('Implemented and rolled out.', (string) ($updatedEntry['implementation_actions'] ?? ''));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/decisions-register/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/decisions-register');
        $this->assertNull((new ModuleRaidEntryModel())->find($entryId));
    }

    public function testDependenciesModuleSupportsLifecycleAndExtendedFields(): void
    {
        $manager = $this->createUser('raidmanager_dependency_lifecycle', 'raidmanager_dependency_lifecycle@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Dependencies Lifecycle');

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

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'dependencies_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('at_risk', (string) ($entry['status'] ?? ''));
        $this->assertSame('external', (string) ($entry['dependency_type'] ?? ''));
        $this->assertSame('Release readiness gate', (string) ($entry['related_work_package'] ?? ''));
        $this->assertSame(1, (int) ($entry['escalation_required'] ?? 0));

        $entryId = (int) ($entry['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/dependencies-register/' . $entryId . '/update', [
                'title' => 'Security vendor assessment',
                'description' => 'Dependency completed.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'fulfilled',
                'priority' => 'high',
                'impact_level' => 'medium',
                'dependency_type' => 'external',
                'related_work_package' => 'Release readiness gate',
                'depends_on' => 'External security audit partner',
                'mitigation_actions' => 'Assessment complete.',
                'escalation_required' => '0',
                'target_date' => '2026-05-01',
                'review_date' => '2026-05-16',
                'lessons_learned' => 'Dependency fulfilled successfully.',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/dependencies-register');

        $updatedEntry = (new ModuleRaidEntryModel())->find($entryId);
        $this->assertIsArray($updatedEntry);
        $this->assertSame('fulfilled', (string) ($updatedEntry['status'] ?? ''));
        $this->assertSame(0, (int) ($updatedEntry['escalation_required'] ?? 1));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/dependencies-register/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/dependencies-register');
        $this->assertNull((new ModuleRaidEntryModel())->find($entryId));
    }

    public function testIssueModuleFilterAndSortWorkForOperationalUsage(): void
    {
        $manager = $this->createUser('raidmanager3', 'raidmanager3@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 4');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new ModuleRaidEntryModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Open incident',
            'description' => 'Needs response',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'high',
            'target_date' => '2026-05-15',
            'review_date' => '2026-05-14',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new ModuleRaidEntryModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Closed incident',
            'description' => 'Completed',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'closed',
            'priority' => 'low',
            'target_date' => '2026-05-10',
            'review_date' => '2026-05-10',
            'closed_at' => date('Y-m-d H:i:s'),
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $result = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/issue-tracker?status=open&q=Open&sort=priority_desc');

        $result->assertOK();
        $body = $result->getBody();

        $this->assertStringContainsString('Open incident', $body);
        $this->assertStringNotContainsString('Closed incident', $body);
    }

    public function testRiskWidgetDrillDownLinkTargetsExistingEntryAnchor(): void
    {
        $manager = $this->createUser('raidmanager5', 'raidmanager5@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 6');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $entryId = (new ModuleRaidEntryModel())->insert([
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

        $this->assertIsInt($entryId);

        $projectOverview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $projectOverview->assertOK();
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/risk-register#entry-' . $entryId, $projectOverview->getBody());

        $modulePage = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId . '/modules/risk-register');

        $modulePage->assertOK();
        $this->assertStringContainsString('id="entry-' . $entryId . '"', $modulePage->getBody());
    }

    public function testDecisionsAndDependenciesWidgetsExposeRequiredCards(): void
    {
        $manager = $this->createUser('raidmanager_widgets_phase11', 'raidmanager_widgets_phase11@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Widget Coverage');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new ModuleRaidEntryModel())->insert([
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

        (new ModuleRaidEntryModel())->insert([
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

    public function testRiskWidgetUpdatesImmediatelyAfterCreateFromModalPath(): void
    {
        $manager = $this->createUser('raidmanager6', 'raidmanager6@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 7');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        // Prime widget HTML/data cache with current overview state.
        $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId)
            ->assertOK();

        // Create via the same route used by the widget modal popup.
        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/risk-register', [
                'title' => 'Fresh modal risk',
                'impact' => 'high',
                'likelihood' => 'high',
                'mitigation_actions' => 'Immediate mitigation',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/risk-register');

        // Verify overview widget reflects the new entry without waiting for cache TTL.
        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $this->assertStringContainsString('Fresh modal risk', $overview->getBody());
    }

    public function testRiskWidgetShowsOpenCountsAndHighPriorityList(): void
    {
        $manager = $this->createUser('raidmanager7', 'raidmanager7@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 8');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new ModuleRaidEntryModel())->insert([
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

        (new ModuleRaidEntryModel())->insert([
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

        (new ModuleRaidEntryModel())->insert([
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

        (new ModuleRaidEntryModel())->insert([
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
        $manager = $this->createUser('raidmanager8', 'raidmanager8@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 9');

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
        $manager = $this->createUser('raidmanager9', 'raidmanager9@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 10');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new ModuleRaidEntryModel())->insert([
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
        $manager = $this->createUser('raidmanager10', 'raidmanager10@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project 11');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $body = $overview->getBody();

        $this->assertStringContainsString('id="riskModalAdd"', $body);
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

    public function testIssueModuleSupportsExtendedFieldsLifecycleAndDelete(): void
    {
        $manager = $this->createUser('raidmanager_issue_ext', 'raidmanager_issue_ext@example.com');
        $reporter = $this->createUser('raidreporter_issue_ext', 'raidreporter_issue_ext@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Issues Extended');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker', [
                'title' => 'Critical integration issue',
                'description' => 'Issue needs urgent resolution.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'open',
                'priority' => 'high',
                'date_reported' => '2026-05-16',
                'reporter_user_id' => (int) $reporter['id'],
                'impact_level' => 'high',
                'mitigation_actions' => 'Run hotfix and regression checks.',
                'lessons_learned' => 'Track incident timeline centrally.',
                'target_date' => '2026-05-20',
                'review_date' => '2026-05-18',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('high', (string) ($entry['impact_level'] ?? ''));
        $this->assertSame((int) $reporter['id'], (int) ($entry['reporter_user_id'] ?? 0));

        $entryId = (int) ($entry['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker/' . $entryId . '/update', [
                'title' => 'Critical integration issue',
                'description' => 'Issue resolved after fix deployment.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'resolved',
                'priority' => 'medium',
                'date_reported' => '2026-05-16',
                'reporter_user_id' => (int) $reporter['id'],
                'impact_level' => 'medium',
                'mitigation_actions' => 'Fix applied and monitored.',
                'lessons_learned' => 'Add pre-release integration checklist.',
                'target_date' => '2026-05-20',
                'review_date' => '2026-05-19',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');

        $updated = (new ModuleRaidEntryModel())->find($entryId);
        $this->assertIsArray($updated);
        $this->assertSame('resolved', (string) ($updated['status'] ?? ''));
        $this->assertSame('medium', (string) ($updated['impact_level'] ?? ''));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/issue-tracker/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/issue-tracker');
        $this->assertNull((new ModuleRaidEntryModel())->find($entryId));
    }

    public function testTasksModuleSupportsExtendedFieldsLifecycleAndDelete(): void
    {
        $manager = $this->createUser('raidmanager_tasks_ext', 'raidmanager_tasks_ext@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Tasks Extended');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $create = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register', [
                'title' => 'Prepare release runbook',
                'description' => 'Compile release steps and rollback checks.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'in_progress',
                'priority' => 'high',
                'task_category' => 'Delivery',
                'related_objective' => 'Release readiness',
                'collaborators' => 'Ops, QA',
                'percent_complete' => 45,
                'planned_start_date' => '2026-05-10',
                'due_date' => '2026-05-25',
                'blocked_reason' => 'Waiting on security sign-off.',
                'next_action' => 'Schedule sign-off meeting.',
                'lessons_learned' => 'Checklist draft should start earlier.',
            ]);

        $create->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');

        $entry = (new ModuleRaidEntryModel())
            ->where('module_slug', 'tasks_register_project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertIsArray($entry);
        $this->assertSame('Delivery', (string) ($entry['task_category'] ?? ''));
        $this->assertSame(45, (int) ($entry['percent_complete'] ?? 0));

        $entryId = (int) ($entry['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $update = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register/' . $entryId . '/update', [
                'title' => 'Prepare release runbook',
                'description' => 'Runbook finalized.',
                'owner_user_id' => (int) $manager['id'],
                'status' => 'completed',
                'priority' => 'medium',
                'task_category' => 'Delivery',
                'related_objective' => 'Release readiness',
                'collaborators' => 'Ops, QA',
                'percent_complete' => 100,
                'planned_start_date' => '2026-05-10',
                'due_date' => '2026-05-25',
                'completed_date' => '2026-05-20',
                'blocked_reason' => '',
                'next_action' => 'Publish runbook to project space.',
                'lessons_learned' => 'Coordinate sign-off earlier in cycle.',
            ]);

        $update->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');

        $updated = (new ModuleRaidEntryModel())->find($entryId);
        $this->assertIsArray($updated);
        $this->assertSame('completed', (string) ($updated['status'] ?? ''));
        $this->assertSame(100, (int) ($updated['percent_complete'] ?? 0));
        $this->assertSame('2026-05-20', (string) ($updated['completed_date'] ?? ''));

        $delete = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/tasks-register/' . $entryId . '/delete');

        $delete->assertRedirectTo('/projects/' . $projectId . '/modules/tasks-register');
        $this->assertNull((new ModuleRaidEntryModel())->find($entryId));
    }

    public function testIssueAndTasksWidgetsExposeCardsAndActions(): void
    {
        $manager = $this->createUser('raidmanager_issue_task_widgets', 'raidmanager_issue_task_widgets@example.com');
        $projectId = $this->createProject((int) $manager['id'], 'RAID Project Issue Task Widgets');

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        (new ModuleRaidEntryModel())->insert([
            'module_slug' => 'issue_tracker_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Issue widget entry',
            'description' => 'Issue to display widgets',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'high',
            'target_date' => '2026-05-01',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        (new ModuleRaidEntryModel())->insert([
            'module_slug' => 'tasks_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => 'Task widget entry',
            'description' => 'Task to display widgets',
            'owner_user_id' => (int) $manager['id'],
            'status' => 'open',
            'priority' => 'medium',
            'due_date' => '2026-05-01',
            'created_by_user_id' => (int) $manager['id'],
            'updated_by_user_id' => (int) $manager['id'],
        ]);

        $overview = $this->withSession($this->authSession($manager))
            ->get('/projects/' . $projectId);

        $overview->assertOK();
        $body = $overview->getBody();

        $this->assertStringContainsString((string) lang('Module.issuesWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.issuesWidgetHighPriorityTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.issuesWidgetOverdueTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetOverviewTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetMyOpenTitle'), $body);
        $this->assertStringContainsString((string) lang('Module.tasksWidgetOverdueTitle'), $body);

        $this->assertStringContainsString('/projects/' . $projectId . '/modules/issue-tracker', $body);
        $this->assertStringContainsString('/projects/' . $projectId . '/modules/tasks-register', $body);
        $this->assertStringContainsString('issueOverviewModalAdd', $body);
        $this->assertStringContainsString('taskOverviewModalAdd', $body);
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(string $username, string $email): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return (array) $model->where('username', $username)->first();
    }

    private function createProject(int $ownerId, string $name): int
    {
        $projectId = (new ProjectModel())->insert([
            'name' => $name,
            'description' => null,
            'owner_user_id' => $ownerId,
        ], true);

        $this->assertIsInt($projectId);

        return $projectId;
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function authSession(array $user): array
    {
        return [
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'last_activity_at' => time(),
        ];
    }
}
