<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleInternalApiService;
use App\Libraries\Modules\ModuleLockService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ModuleApiSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAuthorizedApiCreateReadAndUpdateFlowsAreAudited(): void
    {
        $admin = $this->createUser('apiadmin', 'apiadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);
        $apiService = new ModuleInternalApiService();

        $projectId = (new ProjectModel())->insert([
            'name' => 'API Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        $createData = $apiService->create(ModuleRegistryService::HELLO_WORLD_PROJECT, 'entries', [
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Created via API',
        ], (int) $admin['id']);
        $this->assertTrue((bool) ($createData['ok'] ?? false));

        $entryId = (int) ($createData['id'] ?? 0);
        $this->assertGreaterThan(0, $entryId);

        $readData = $apiService->read(ModuleRegistryService::HELLO_WORLD_PROJECT, 'entries', [
            'scope_type' => 'project',
            'scope_id' => $projectId,
        ], (int) $admin['id']);
        $this->assertTrue((bool) ($readData['ok'] ?? false));
        $this->assertNotEmpty($readData['data'] ?? []);

        $updateData = $apiService->update(ModuleRegistryService::HELLO_WORLD_PROJECT, 'entries', $entryId, [
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Updated via API',
        ], (int) $admin['id']);
        $this->assertTrue((bool) ($updateData['ok'] ?? false));

        $entry = (new ModuleHelloWorldEntryModel())->find($entryId);
        $this->assertIsArray($entry);
        $this->assertSame('Updated via API', (string) $entry['message']);

        $readAudit = (new AuthAuditLogModel())
            ->where('event_type', 'module_internal_api_read')
            ->orderBy('id', 'DESC')
            ->first();
        $this->assertNotNull($readAudit);

        $writeAudit = (new AuthAuditLogModel())
            ->where('event_type', 'module_internal_api_write')
            ->orderBy('id', 'DESC')
            ->first();
        $this->assertNotNull($writeAudit);
    }

    public function testUnauthorizedApiWriteIsForbidden(): void
    {
        $owner = $this->createUser('apiowner', 'apiowner@example.com');
        $outsider = $this->createUser('apioutsider', 'apioutsider@example.com');
        $apiService = new ModuleInternalApiService();

        $projectId = (new ProjectModel())->insert([
            'name' => 'Restricted API Project',
            'description' => null,
            'owner_user_id' => (int) $owner['id'],
        ], true);

        $result = $apiService->create(ModuleRegistryService::HELLO_WORLD_PROJECT, 'entries', [
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Should fail',
        ], (int) $outsider['id']);

        $this->assertFalse((bool) ($result['ok'] ?? true));
        $this->assertSame('forbidden', (string) ($result['error'] ?? ''));
    }

    public function testApiUpdateReturns423WhenContextLockedByAnotherEditor(): void
    {
        $editorA = $this->createUser('api-lock-a', 'api-lock-a@example.com');
        $editorB = $this->createUser('api-lock-b', 'api-lock-b@example.com');
        $apiService = new ModuleInternalApiService();

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

        $result = $apiService->update(ModuleRegistryService::HELLO_WORLD_PROJECT, 'entries', (int) $entryId, [
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Blocked API update',
        ], (int) $editorB['id']);

        $this->assertFalse((bool) ($result['ok'] ?? true));
        $this->assertSame('locked', (string) ($result['error'] ?? ''));
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

}
