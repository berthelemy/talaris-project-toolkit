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
        $this->assertStringContainsString('lecture seule', strtolower($page->getBody()));

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
