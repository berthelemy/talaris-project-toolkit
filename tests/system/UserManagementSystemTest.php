<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleAssignmentModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class UserManagementSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanCreateUpdateAndDeactivateUserWithAuditLogs(): void
    {
        $admin = $this->createUser('admincrud', 'admincrud@example.com', true);
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $create = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users', [
                'username' => 'manageduser',
                'email' => 'manageduser@example.com',
                'password' => 'StrongPass!123',
                'is_active' => '1',
            ]);

        $create->assertRedirectTo('/users');

        $managed = (new UserModel())->where('username', 'manageduser')->first();
        $this->assertNotNull($managed);
        $this->assertTrue((bool) $managed['is_active']);

        $update = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users/' . (int) $managed['id'], [
                'username' => 'manageduser',
                'email' => 'manageduser+updated@example.com',
                'language_preference' => 'fr',
                'profile_description' => 'Updated by admin.',
                'reset_password_to' => 'EvenStronger!456',
                'is_active' => '1',
            ]);

        $update->assertRedirectTo('/users/' . (int) $managed['id'] . '/edit');

        $updated = (new UserModel())->find((int) $managed['id']);
        $this->assertSame('manageduser+updated@example.com', $updated['email']);
        $this->assertSame('fr', $updated['language_preference']);
        $this->assertTrue(password_verify('EvenStronger!456', (string) $updated['password_hash']));

        $deactivate = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users/' . (int) $managed['id'] . '/deactivate');

        $deactivate->assertRedirectTo('/users');

        $inactive = (new UserModel())->find((int) $managed['id']);
        $this->assertFalse((bool) $inactive['is_active']);

        $auditModel = new AuthAuditLogModel();
        $this->assertNotNull($auditModel->where('event_type', 'user_admin_created')->orderBy('id', 'DESC')->first());
        $this->assertNotNull($auditModel->where('event_type', 'user_admin_updated')->orderBy('id', 'DESC')->first());
        $this->assertNotNull($auditModel->where('event_type', 'user_admin_deactivated')->orderBy('id', 'DESC')->first());
    }

    public function testLastActiveAdministratorCannotBeDeactivated(): void
    {
        $admin = $this->createUser('singleadmin', 'singleadmin@example.com', true);
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        (new UserModel())
            ->where('id !=', (int) $admin['id'])
            ->delete();

        $result = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users/' . (int) $admin['id'] . '/deactivate');

        $result->assertRedirectTo('/users');

        $fresh = (new UserModel())->find((int) $admin['id']);
        $this->assertTrue((bool) $fresh['is_active']);
    }

    public function testAdministratorCanAssignAndRevokeScopedRole(): void
    {
        $admin = $this->createUser('rolesadmin', 'rolesadmin@example.com', true);
        $target = $this->createUser('roleuser', 'roleuser@example.com', true);

        $rbac = new RbacService();
        $rbac->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $programmeId = (new ProgrammeModel())->insert([
            'name' => 'Programme A',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);
        $projectId = (new ProjectModel())->insert([
            'name' => 'Project A',
            'description' => null,
            'owner_user_id' => (int) $admin['id'],
        ], true);

        $this->assertIsInt($programmeId);
        $this->assertIsInt($projectId);

        $assign = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users/' . (int) $target['id'] . '/roles', [
                'role_slug' => 'team_member',
                'scope_type' => 'project',
                'project_scope_id' => (string) $projectId,
            ]);

        $assign->assertRedirectTo('/users/' . (int) $target['id'] . '/edit');

        $role = (new RoleModel())->where('slug', 'team_member')->first();
        $assignment = (new UserRoleAssignmentModel())
            ->where('user_id', (int) $target['id'])
            ->where('role_id', (int) $role['id'])
            ->where('scope_type', 'project')
            ->where('scope_id', (int) $projectId)
            ->first();

        $this->assertNotNull($assignment);

        $revoke = $this->withSession($this->authSession($admin))
            ->withBodyFormat('form')
            ->post('/users/' . (int) $target['id'] . '/roles/revoke', [
                'role_slug' => 'team_member',
                'scope_type' => 'project',
                'scope_id' => (string) $projectId,
            ]);

        $revoke->assertRedirectTo('/users/' . (int) $target['id'] . '/edit');

        $removed = (new UserRoleAssignmentModel())
            ->where('user_id', (int) $target['id'])
            ->where('role_id', (int) $role['id'])
            ->where('scope_type', 'project')
            ->where('scope_id', (int) $projectId)
            ->first();

        $this->assertNull($removed);

        $auditModel = new AuthAuditLogModel();
        $this->assertNotNull($auditModel->where('event_type', 'role_assigned')->orderBy('id', 'DESC')->first());
        $this->assertNotNull($auditModel->where('event_type', 'role_revoked')->orderBy('id', 'DESC')->first());
    }

    public function testNonAdministratorCannotOpenUserManagement(): void
    {
        $member = $this->createUser('memberuser', 'memberuser@example.com', true);

        $result = $this->withSession($this->authSession($member))->get('/users');

        $result->assertRedirectTo('/dashboard');
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(string $username, string $email, bool $isActive): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => $isActive ? 1 : 0,
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
