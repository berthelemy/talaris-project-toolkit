<?php

use App\Libraries\Auth\RbacService;
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
final class ModuleAutosaveSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAutosaveUpdatesEntryAndWritesAuditLog(): void
    {
        $admin = $this->createUser('autosaveadmin', 'autosaveadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Autosave Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $entryId = (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Original message',
            'created_by_user_id' => (int) $admin['id'],
        ], true);

        $entry = (new ModuleHelloWorldEntryModel())->find((int) $entryId);
        $this->assertIsArray($entry);

        $result = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/hello-world/entries/' . (int) $entryId . '/autosave', [
                'message' => 'Autosaved message',
                'last_updated_at' => (string) ($entry['updated_at'] ?? ''),
            ]);

        $result->assertOK();
        $data = json_decode($result->getJSON(), true);
        $this->assertTrue((bool) ($data['ok'] ?? false));

        $updated = (new ModuleHelloWorldEntryModel())->find((int) $entryId);
        $this->assertIsArray($updated);
        $this->assertSame('Autosaved message', (string) $updated['message']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'autosave_update')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', (string) $audit['status']);
    }

    public function testAutosaveConflictReturns409(): void
    {
        $admin = $this->createUser('autosaveconflict', 'autosaveconflict@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Conflict Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $entryId = (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Conflict message',
            'created_by_user_id' => (int) $admin['id'],
        ], true);

        $result = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/hello-world/entries/' . (int) $entryId . '/autosave', [
                'message' => 'Attempted overwrite',
                'last_updated_at' => '2000-01-01 00:00:00',
            ]);

        $result->assertStatus(409);
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
