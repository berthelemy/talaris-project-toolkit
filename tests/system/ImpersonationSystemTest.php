<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ImpersonationSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanStartImpersonationWithAuditLog(): void
    {
        $admin = $this->createUser('adminuser', 'adminuser@example.com');
        $target = $this->createUser('targetuser', 'targetuser@example.com');

        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $result = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/impersonate/' . (int) $target['id']);

        $result->assertRedirectTo('/dashboard');
        $result->assertSessionHas('user_id', (int) $target['id']);
        $result->assertSessionHas('impersonator_user_id', (int) $admin['id']);
        $result->assertSessionHas('is_impersonating', true);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'impersonation_started')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
        $this->assertSame((int) $admin['id'], (int) $audit['user_id']);
    }

    public function testAdministratorCanStopImpersonationWithAuditLog(): void
    {
        $admin = $this->createUser('adminstop', 'adminstop@example.com');
        $target = $this->createUser('targetstop', 'targetstop@example.com');

        $result = $this->withSession([
            'user_id' => (int) $target['id'],
            'username' => (string) $target['username'],
            'impersonator_user_id' => (int) $admin['id'],
            'impersonator_username' => (string) $admin['username'],
            'is_impersonating' => true,
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/impersonate/stop');

        $result->assertRedirectTo('/dashboard');
        $result->assertSessionHas('user_id', (int) $admin['id']);
        $result->assertSessionMissing('impersonator_user_id');
        $result->assertSessionMissing('is_impersonating');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'impersonation_stopped')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
        $this->assertSame((int) $admin['id'], (int) $audit['user_id']);
    }

    public function testNonAdministratorCannotImpersonateAnotherUser(): void
    {
        $member = $this->createUser('memberuser', 'memberuser@example.com');
        $target = $this->createUser('membertarget', 'membertarget@example.com');

        $result = $this->withSession([
            'user_id' => (int) $member['id'],
            'username' => (string) $member['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/impersonate/' . (int) $target['id']);

        $result->assertRedirectTo('/dashboard');
        $result->assertSessionHas('user_id', (int) $member['id']);
        $result->assertSessionMissing('impersonator_user_id');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'impersonation_denied')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('failed', $audit['status']);
        $this->assertSame((int) $member['id'], (int) $audit['user_id']);
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