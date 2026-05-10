<?php

use App\Models\AuthAuditLogModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ProfileSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testProfileUpdatePersistsProfileFieldsAndCreatesAuditLog(): void
    {
        $user = $this->createUser();

        $result = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => 'profileuser',
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/profile', [
            'language_preference' => 'fr',
            'profile_description' => 'Profile description updated in system test.',
        ]);

        $result->assertRedirectTo('/profile');

        $updated = (new UserModel())->find((int) $user['id']);
        $this->assertSame('fr', $updated['language_preference']);
        $this->assertSame('Profile description updated in system test.', $updated['profile_description']);

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'profile_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
    }

    public function testPasswordChangeRejectsIncorrectCurrentPasswordAndAuditsFailure(): void
    {
        $user = $this->createUser();

        $result = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => 'profileuser',
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/profile/password', [
            'current_password' => 'WrongPassword',
            'new_password' => 'EvenStronger!456',
            'new_password_confirm' => 'EvenStronger!456',
        ]);

        $result->assertRedirect();

        $unchanged = (new UserModel())->find((int) $user['id']);
        $this->assertTrue(password_verify('StrongPass!123', (string) $unchanged['password_hash']));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'profile_password_change')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('failed', $audit['status']);
    }

    public function testPasswordChangeUpdatesPasswordWhenCurrentPasswordIsValid(): void
    {
        $user = $this->createUser();

        $result = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => 'profileuser',
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/profile/password', [
            'current_password' => 'StrongPass!123',
            'new_password' => 'EvenStronger!456',
            'new_password_confirm' => 'EvenStronger!456',
        ]);

        $result->assertRedirectTo('/profile');

        $changed = (new UserModel())->find((int) $user['id']);
        $this->assertTrue(password_verify('EvenStronger!456', (string) $changed['password_hash']));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'profile_password_change')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => 'profileuser',
            'email' => 'profileuser@example.com',
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return (array) $model->where('username', 'profileuser')->first();
    }
}