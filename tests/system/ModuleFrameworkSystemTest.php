<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ModuleRegistryModel;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ModuleFrameworkSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanToggleModuleAndAuditIsWritten(): void
    {
        $admin = $this->createUser('moduleadmin', 'moduleadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $disable = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/modules/' . ModuleRegistryService::HELLO_WORLD_PROGRAMME . '/toggle', [
                'is_enabled' => '0',
            ]);

        $disable->assertRedirectTo('/modules');

        $row = (new ModuleRegistryModel())->where('slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)->first();
        $this->assertNotNull($row);
        $this->assertFalse((bool) $row['is_enabled']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'module_disabled')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame((int) $admin['id'], (int) $audit['user_id']);
    }

    public function testProgrammeHelloWorldModuleCreatesScopedRecord(): void
    {
        $admin = $this->createUser('moduleprogram', 'moduleprogram@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $programmeId = (new ProgrammeModel())->insert([
            'name' => 'Programme M',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($programmeId);

        $post = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/programmes/' . $programmeId . '/modules/hello-world', [
                'message' => 'Hello from programme module',
            ]);

        $post->assertRedirectTo('/programmes/' . $programmeId . '/modules/hello-world');

        $entry = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)
            ->where('scope_type', 'programme')
            ->where('scope_id', $programmeId)
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame('Hello from programme module', $entry['message']);
    }

    public function testProjectHelloWorldModuleCreatesScopedRecord(): void
    {
        $admin = $this->createUser('moduleproject', 'moduleproject@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Project M',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        $post = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/modules/hello-world', [
                'message' => 'Hello from project module',
            ]);

        $post->assertRedirectTo('/projects/' . $projectId . '/modules/hello-world');

        $entry = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROJECT)
            ->where('scope_type', 'project')
            ->where('scope_id', $projectId)
            ->first();

        $this->assertNotNull($entry);
        $this->assertSame('Hello from project module', $entry['message']);
    }

    public function testDisabledModuleBlocksAccess(): void
    {
        $admin = $this->createUser('moduledisabled', 'moduledisabled@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        (new ModuleRegistryService())->setEnabled(ModuleRegistryService::HELLO_WORLD_PROJECT, false, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Project D',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        $result = $this->withSession($this->authSession($admin))
            ->get('/projects/' . $projectId . '/modules/hello-world');

        $result->assertRedirectTo('/projects/' . $projectId);
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
