<?php

use App\Models\AuthAuditLogModel;
use App\Models\ModuleEditLockModel;
use App\Models\AuthSettingsModel;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use Config\Email as EmailConfig;
use Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockEmail;

/**
 * @internal
 */
final class AuthSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        (new UserModel())->insert([
            'username'      => 'phase2user',
            'email'         => 'phase2@example.com',
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active'     => 1,
        ]);
    }

    public function testSuccessfulLoginCreatesAuditEvent(): void
    {
        $result = $this->withBodyFormat('form')->post('/login', [
            'username' => 'phase2user',
            'password' => 'StrongPass!123',
        ]);

        $result->assertRedirectTo('/dashboard');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'login')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
    }

    public function testFailedLoginCreatesAuditEvent(): void
    {
        $this->withBodyFormat('form')->post('/login', [
            'username' => 'phase2user',
            'password' => 'WrongPassword',
        ]);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'login')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('failed', $audit['status']);
    }

    public function testForgotPasswordCreatesTokenAndAuditEvent(): void
    {
        $email = new MockEmail(config(EmailConfig::class));
        $email->returnValue = true;
        Services::injectMock('email', $email);

        $result = $this->withBodyFormat('form')->post('/forgot-password', [
            'email' => 'phase2@example.com',
        ]);

        $result->assertRedirectTo('/forgot-password');

        $user = (new UserModel())->where('email', 'phase2@example.com')->first();
        $token = (new PasswordResetTokenModel())
            ->where('user_id', (int) $user['id'])
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($token);
        $this->assertSame((int) $user['id'], (int) $token['user_id']);
        $this->assertSame(64, strlen((string) $token['token_hash']));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'password_reset_requested')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
    }

    public function testInactiveSessionIsLoggedOutAndAudited(): void
    {
        (new AuthSettingsModel())->update(1, [
            'inactivity_timeout_seconds' => 60,
            'reset_token_ttl_minutes' => 60,
        ]);

        $user = (new UserModel())->where('username', 'phase2user')->first();

        (new ModuleEditLockModel())->insert([
            'module_slug' => 'hello_world_project',
            'scope_type' => 'project',
            'scope_id' => 123,
            'locked_by_user_id' => (int) $user['id'],
            'acquired_at' => date('Y-m-d H:i:s', time() - 70),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        $result = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => 'phase2user',
            'last_activity_at' => time() - 61,
        ])->get('/dashboard');

        $result->assertRedirectTo('/login');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'session_timeout_logout')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
        $this->assertSame((int) $user['id'], (int) $audit['user_id']);

        $remainingLocks = (new ModuleEditLockModel())
            ->where('locked_by_user_id', (int) $user['id'])
            ->findAll();

        $this->assertSame([], $remainingLocks);
    }

    public function testLogoutReleasesActiveModuleLocks(): void
    {
        $user = (new UserModel())->where('username', 'phase2user')->first();

        (new ModuleEditLockModel())->insert([
            'module_slug' => 'hello_world_programme',
            'scope_type' => 'programme',
            'scope_id' => 12,
            'locked_by_user_id' => (int) $user['id'],
            'acquired_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        $result = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => 'phase2user',
            'last_activity_at' => time(),
        ])->post('/logout');

        $result->assertRedirectTo('/login');

        $remainingLocks = (new ModuleEditLockModel())
            ->where('locked_by_user_id', (int) $user['id'])
            ->findAll();

        $this->assertSame([], $remainingLocks);
    }

    public function testResetPasswordUpdatesPasswordAndAudit(): void
    {
        $user = (new UserModel())->where('username', 'phase2user')->first();

        $rawToken = bin2hex(random_bytes(32));
        (new PasswordResetTokenModel())->insert([
            'user_id'    => (int) $user['id'],
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        $result = $this->withBodyFormat('form')->post('/reset-password/' . $rawToken, [
            'password'         => 'EvenStronger!456',
            'password_confirm' => 'EvenStronger!456',
        ]);

        $result->assertRedirectTo('/login');

        $updatedUser = (new UserModel())->find((int) $user['id']);
        $this->assertTrue(password_verify('EvenStronger!456', $updatedUser['password_hash']));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'password_reset_completed')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
    }
}
