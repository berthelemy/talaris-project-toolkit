<?php

use App\Models\AuthAuditLogModel;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

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
