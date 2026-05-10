<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RbacServiceDatabaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAssignRoleAtSystemScopeCreatesAuditLogAndPermission(): void
    {
        $user = $this->createUser('sysadmin');

        $service = new RbacService();
        $service->assignRoleToUser((int) $user['id'], 'administrator', 'system', null, (int) $user['id']);

        $this->assertTrue($service->hasPermission((int) $user['id'], 'system.users.impersonate', 'system', null));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'role_assigned')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
        $this->assertSame((int) $user['id'], (int) $audit['user_id']);
    }

    public function testMultipleRolesInProjectScopeResolveUnionOfPermissions(): void
    {
        $user = $this->createUser('projectperson');

        $service = new RbacService();
        $service->assignRoleToUser((int) $user['id'], 'team_member', 'project', 42, (int) $user['id']);
        $service->assignRoleToUser((int) $user['id'], 'stakeholder', 'project', 42, (int) $user['id']);

        $roles = $service->roleSlugsForUser((int) $user['id'], 'project', 42);

        $this->assertContains('team_member', $roles);
        $this->assertContains('stakeholder', $roles);
        $this->assertTrue($service->hasPermission((int) $user['id'], 'project.read', 'project', 42));
        $this->assertTrue($service->hasPermission((int) $user['id'], 'reports.read_stakeholder', 'project', 42));
    }

    public function testCustomRolePermissionsAreUsedWhenDefined(): void
    {
        $user = $this->createUser('customroleuser');

        $roleId = (new RoleModel())->insert([
            'slug' => 'custom_viewer',
            'name' => 'Custom Viewer',
            'description' => 'Custom read role',
            'is_predefined' => 0,
            'permissions_json' => json_encode(['custom.dashboard.read'], JSON_THROW_ON_ERROR),
        ], true);

        $this->assertIsInt($roleId);

        (new \App\Models\UserRoleAssignmentModel())->insert([
            'user_id' => (int) $user['id'],
            'role_id' => (int) $roleId,
            'scope_type' => 'system',
            'scope_id' => null,
        ]);

        $service = new RbacService();

        $this->assertTrue($service->hasPermission((int) $user['id'], 'custom.dashboard.read', 'system', null));
    }

    public function testAssignRoleRejectsInvalidScopeConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $user = $this->createUser('badscope');

        (new RbacService())->assignRoleToUser((int) $user['id'], 'team_member', 'project', null, (int) $user['id']);
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(string $username): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => $username,
            'email' => $username . '@example.com',
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return (array) $model->where('username', $username)->first();
    }
}