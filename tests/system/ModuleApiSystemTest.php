<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleLockService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ModuleApiSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAuthorizedApiCreateReadAndUpdateFlowsAreAudited(): void
    {
        $admin = $this->createUser('apiadmin', 'apiadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'API Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        $create = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/api/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/entries', [
                'scope_type' => 'project',
                'scope_id' => $projectId,
                'message' => 'Created via API',
            ]);

        $create->assertOK();
        $createData = json_decode($create->getJSON(), true);
        $this->assertTrue((bool) ($createData['ok'] ?? false));

        $entryId = (int) ($createData['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $read = $this->withSession($this->authSession($admin))
            ->get('/api/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/entries?scope_type=project&scope_id=' . $projectId);

        $read->assertOK();
        $readData = json_decode($read->getJSON(), true);
        $this->assertTrue((bool) ($readData['ok'] ?? false));
        $this->assertNotEmpty($readData['data'] ?? []);

        $update = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/api/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/entries/' . $entryId . '?_method=PUT', [
                'scope_type' => 'project',
                'scope_id' => $projectId,
                'message' => 'Updated via API',
            ]);

        $update->assertOK();
        $updateData = json_decode($update->getJSON(), true);
        $this->assertTrue((bool) ($updateData['ok'] ?? false));

        $entry = (new ModuleHelloWorldEntryModel())->find($entryId);
        $this->assertIsArray($entry);
        $this->assertSame('Updated via API', (string) $entry['message']);

        $readAudit = (new AuthAuditLogModel())
            ->where('event_type', 'module_api_read')
            ->orderBy('id', 'DESC')
            ->first();
        $this->assertNotNull($readAudit);

        $writeAudit = (new AuthAuditLogModel())
            ->where('event_type', 'module_api_write')
            ->orderBy('id', 'DESC')
            ->first();
        $this->assertNotNull($writeAudit);
    }

    public function testUnauthorizedApiWriteIsForbidden(): void
    {
        $owner = $this->createUser('apiowner', 'apiowner@example.com');
        $outsider = $this->createUser('apioutsider', 'apioutsider@example.com');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Restricted API Project',
            'description' => null,
            'owner_user_id' => (int) $owner['id'],
        ], true);

        $result = $this->withSession($this->authSession($outsider))
            ->withBodyFormat('form')
            ->post('/api/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/entries', [
                'scope_type' => 'project',
                'scope_id' => $projectId,
                'message' => 'Should fail',
            ]);

        $result->assertStatus(403);
    }

    public function testApiUpdateReturns423WhenContextLockedByAnotherEditor(): void
    {
        $editorA = $this->createUser('api-lock-a', 'api-lock-a@example.com');
        $editorB = $this->createUser('api-lock-b', 'api-lock-b@example.com');

        (new RbacService())->assignRoleToUser((int) $editorA['id'], 'administrator', 'system', null, (int) $editorA['id']);
        (new RbacService())->assignRoleToUser((int) $editorB['id'], 'administrator', 'system', null, (int) $editorB['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'API Lock Project',
            'description' => null,
            'owner_user_id' => (int) $editorA['id'],
        ], true);

        $entryId = (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'API lock target',
            'created_by_user_id' => (int) $editorA['id'],
        ], true);

        (new ModuleLockService())->acquire(ModuleRegistryService::HELLO_WORLD_PROJECT, 'project', (int) $projectId, (int) $editorA['id']);

        $result = $this->withSession($this->authSession($editorB))
            ->withBodyFormat('form')
            ->post('/api/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/entries/' . (int) $entryId . '?_method=PUT', [
                'scope_type' => 'project',
                'scope_id' => $projectId,
                'message' => 'Blocked API update',
            ]);

        $result->assertStatus(423);
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
