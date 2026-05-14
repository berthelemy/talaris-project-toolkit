<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\AuthAuditLogModel;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ModuleWidgetLayoutPreferenceModel;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class WidgetLayoutPreferencesSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanUpdateDefaultWidgetLayoutWithAudit(): void
    {
        $admin = $this->createUser('layoutadmin', 'layoutadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $result = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/widget-layout-default', [
                'is_visible' => '0',
                'display_order' => '77',
            ]);

        $result->assertRedirectTo('/modules');

        $pref = (new ModuleWidgetLayoutPreferenceModel())
            ->where('scope_type', 'project')
            ->where('scope_id', 0)
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROJECT)
            ->first();

        $this->assertNotNull($pref);
        $this->assertFalse((bool) $pref['is_visible']);
        $this->assertSame(77, (int) $pref['display_order']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'module_widget_default_layout_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame((int) $admin['id'], (int) $audit['user_id']);
    }

    public function testProjectManagerCanUpdateProjectWidgetLayoutWithAudit(): void
    {
        $manager = $this->createUser('layoutpm', 'layoutpm@example.com');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Layout Managed Project',
            'description' => null,
            'status' => 'not_started',
            'owner_user_id' => (int) $manager['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $result = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/widgets/layout', [
                'widget_order' => [
                    ModuleRegistryService::HELLO_WORLD_PROJECT => '19',
                ],
            ]);

        $result->assertRedirectTo('/projects/' . $projectId);

        $pref = (new ModuleWidgetLayoutPreferenceModel())
            ->where('scope_type', 'project')
            ->where('scope_id', $projectId)
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROJECT)
            ->first();

        $this->assertNotNull($pref);
        $this->assertFalse((bool) $pref['is_visible']);
        $this->assertSame(19, (int) $pref['display_order']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'project_widget_layout_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame((int) $manager['id'], (int) $audit['user_id']);
    }

    public function testHiddenWidgetPreferenceRemovesWidgetFromProjectOverview(): void
    {
        $owner = $this->createUser('layoutowner', 'layoutowner@example.com');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Widget Visibility Project',
            'description' => null,
            'status' => 'in_progress',
            'owner_user_id' => (int) $owner['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Widget should be hidden after preference update',
            'created_by_user_id' => (int) $owner['id'],
        ]);

        (new ModuleWidgetLayoutPreferenceModel())->insert([
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'is_visible' => 0,
            'display_order' => 10,
            'updated_by_user_id' => (int) $owner['id'],
        ]);

        $response = $this->withSession($this->authSession($owner))->get('/projects/' . $projectId);

        $response->assertOK();
        $this->assertStringNotContainsString('Widget should be hidden after preference update', $response->getBody());
    }

    public function testProjectWidgetLayoutPageShowsDragAndKeyboardOrderingControls(): void
    {
        $manager = $this->createUser('layoutui', 'layoutui@example.com');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Layout UI Project',
            'description' => null,
            'status' => 'not_started',
            'owner_user_id' => (int) $manager['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $manager['id']);

        $page = $this->withSession($this->authSession($manager))->get('/projects/' . $projectId . '/widgets/layout');

        $page->assertOK();
        $body = $page->getBody();

        $this->assertStringContainsString('data-widget-order-list', $body);
        $this->assertStringContainsString('data-widget-move="up"', $body);
        $this->assertStringContainsString('data-widget-move="down"', $body);
        $this->assertStringContainsString('js/widget-layout-ordering.js', $body);
    }

    public function testProgrammeManagerCanUpdateProgrammeWidgetLayoutWithAudit(): void
    {
        $manager = $this->createUser('layoutprogramme', 'layoutprogramme@example.com');

        $programmeId = (new ProgrammeModel())->insert([
            'name' => 'Layout Managed Programme',
            'description' => null,
            'owner_user_id' => (int) $manager['id'],
        ], true);

        $this->assertIsInt($programmeId);

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'programme_manager', 'programme', $programmeId, (int) $manager['id']);

        $result = $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/programmes/' . $programmeId . '/widgets/layout', [
                'widget_visible' => [
                    ModuleRegistryService::HELLO_WORLD_PROGRAMME => '1',
                ],
                'widget_order' => [
                    ModuleRegistryService::HELLO_WORLD_PROGRAMME => '7',
                ],
            ]);

        $result->assertRedirectTo('/programmes/' . $programmeId);

        $pref = (new ModuleWidgetLayoutPreferenceModel())
            ->where('scope_type', 'programme')
            ->where('scope_id', $programmeId)
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)
            ->first();

        $this->assertNotNull($pref);
        $this->assertTrue((bool) $pref['is_visible']);
        $this->assertSame(7, (int) $pref['display_order']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'programme_widget_layout_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame((int) $manager['id'], (int) $audit['user_id']);
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
