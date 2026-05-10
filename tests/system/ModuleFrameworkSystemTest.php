<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleEditLockModel;
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
    /** @var list<string> */
    private array $temporaryModuleDirectories = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryModuleDirectories as $directory) {
            $this->removeDirectory($directory);
        }

        $this->temporaryModuleDirectories = [];

        parent::tearDown();
    }

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

    public function testProjectDetailRendersWidgetWhenModuleEnabled(): void
    {
        $admin = $this->createUser('widgetenabled', 'widgetenabled@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Widget Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Widget visibility check',
            'created_by_user_id' => (int) $admin['id'],
        ]);

        $result = $this->withSession($this->authSession($admin))->get('/projects/' . $projectId);

        $result->assertOK();
        $this->assertStringContainsString('Widget visibility check', $result->getBody());
    }

    public function testProjectDetailSkipsWidgetWhenModuleDisabled(): void
    {
        $admin = $this->createUser('widgetdisabled', 'widgetdisabled@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        (new ModuleRegistryService())->setEnabled(ModuleRegistryService::HELLO_WORLD_PROJECT, false, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Widget Disabled Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Should not render in disabled state',
            'created_by_user_id' => (int) $admin['id'],
        ]);

        $result = $this->withSession($this->authSession($admin))->get('/projects/' . $projectId);

        $result->assertOK();
        $this->assertStringNotContainsString('Should not render in disabled state', $result->getBody());
    }

    public function testProjectWidgetRenderingRespectsScopeAccessBoundaries(): void
    {
        $owner = $this->createUser('widgetowner', 'widgetowner@example.com');
        $outsider = $this->createUser('widgetoutsider', 'widgetoutsider@example.com');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Restricted Widget Project',
            'description' => null,
            'owner_user_id' => (int) $owner['id'],
        ], true);

        $this->assertIsInt($projectId);

        $result = $this->withSession($this->authSession($outsider))->get('/projects/' . $projectId);

        $result->assertRedirectTo('/projects');
    }

    public function testProjectDetailHandlesWidgetExceptionsWithoutBreakingPage(): void
    {
        $admin = $this->createUser('widgeterror', 'widgeterror@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $projectId = (new ProjectModel())->insert([
            'name' => 'Widget Error Project',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Healthy widget still renders',
            'created_by_user_id' => (int) $admin['id'],
        ]);

        $brokenSlug = $this->createTemporaryBrokenWidgetModule();

        (new ModuleRegistryModel())->insert([
            'slug' => $brokenSlug,
            'name' => 'Broken Widget (Project)',
            'scope_type' => 'project',
            'description' => 'Intentional exception for test coverage.',
            'is_enabled' => 1,
        ]);

        $result = $this->withSession($this->authSession($admin))->get('/projects/' . $projectId);

        $result->assertOK();
        $this->assertStringContainsString('Healthy widget still renders', $result->getBody());
    }

    public function testAdminCanViewAndReleaseActiveModuleLocks(): void
    {
        $admin = $this->createUser('modulelockadmin', 'modulelockadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $lockId = (new ModuleEditLockModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => 55,
            'locked_by_user_id' => (int) $admin['id'],
            'acquired_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + 600),
        ], true);

        $page = $this->withSession($this->authSession($admin))->get('/modules');
        $page->assertOK();
        $this->assertStringContainsString('/modules/locks/', $page->getBody());

        $release = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/modules/locks/' . (int) $lockId . '/release');

        $release->assertRedirectTo('/modules');

        $lock = (new ModuleEditLockModel())->find((int) $lockId);
        $this->assertNull($lock);
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

    private function createTemporaryBrokenWidgetModule(): string
    {
        $moduleName = 'BrokenWidgetProject';
        $slug = 'broken_widget_project';
        $baseDirectory = APPPATH . 'Modules/' . $moduleName;
        $widgetDirectory = $baseDirectory . '/Widgets';

        if (! is_dir($widgetDirectory)) {
            mkdir($widgetDirectory, 0777, true);
        }

        $widgetClass = <<<'PHP'
<?php

namespace App\Modules\BrokenWidgetProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;

class ModuleWidget implements ModuleWidgetInterface
{
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\\Modules\\HelloWorldProject\\Views\\widget';
    }

    public function getWidgetData(int $scopeId, array $config = []): array
    {
        throw new \RuntimeException('Intentional widget failure for system test.');
    }

    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }
}
PHP;

        file_put_contents($widgetDirectory . '/ModuleWidget.php', $widgetClass);

        $this->temporaryModuleDirectories[] = $baseDirectory;

        return $slug;
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $entries = array_diff(scandir($directory) ?: [], ['.', '..']);

        foreach ($entries as $entry) {
            $path = $directory . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
