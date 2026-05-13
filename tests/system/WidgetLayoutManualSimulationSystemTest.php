<?php

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Simulated manual walkthrough for widget layout defaults and project overrides.
 *
 * @internal
 */
final class WidgetLayoutManualSimulationSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testManualLikeFlowForAdminDefaultsAndProjectManagerOverrides(): void
    {
        $admin = $this->createUser('manualadmin', 'manualadmin@example.com');
        $manager = $this->createUser('manualpm', 'manualpm@example.com');

        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $modulesPage = $this->withSession($this->authSession($admin))->get('/modules');
        $modulesPage->assertOK();
        $this->assertStringContainsString('/widget-layout-default', $modulesPage->getBody());

        $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/modules/' . ModuleRegistryService::HELLO_WORLD_PROJECT . '/widget-layout-default', [
                'is_visible' => '0',
                'display_order' => '22',
            ])
            ->assertRedirectTo('/modules');

        $projectId = (new ProjectModel())->insert([
            'name' => 'Manual Simulation Project',
            'description' => 'Simulated manual validation project',
            'status' => 'in_progress',
            'owner_user_id' => (int) $manager['id'],
        ], true);

        $this->assertIsInt($projectId);

        (new RbacService())->assignRoleToUser((int) $manager['id'], 'project_manager', 'project', $projectId, (int) $admin['id']);

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => 'Manual flow hidden by default',
            'created_by_user_id' => (int) $manager['id'],
        ]);

        $projectPageBeforeOverride = $this->withSession($this->authSession($manager))->get('/projects/' . $projectId);
        $projectPageBeforeOverride->assertOK();
        $this->assertStringContainsString('/projects/' . $projectId . '/widgets/layout', $projectPageBeforeOverride->getBody());
        $this->assertStringNotContainsString('Manual flow hidden by default', $projectPageBeforeOverride->getBody());

        $this->withSession($this->authSession($manager))
            ->withBodyFormat('form')
            ->post('/projects/' . $projectId . '/widgets/layout', [
                'widget_visible' => [
                    ModuleRegistryService::HELLO_WORLD_PROJECT => '1',
                ],
                'widget_order' => [
                    ModuleRegistryService::HELLO_WORLD_PROJECT => '5',
                ],
            ])
            ->assertRedirectTo('/projects/' . $projectId);

        $projectPageAfterOverride = $this->withSession($this->authSession($manager))->get('/projects/' . $projectId);
        $projectPageAfterOverride->assertOK();
        $this->assertStringContainsString('Manual flow hidden by default', $projectPageAfterOverride->getBody());
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
