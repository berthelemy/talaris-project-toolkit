<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class InstallSetupSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testInstallPageAccessibleBeforeFirstUserExists(): void
    {
        $result = $this->get('/install/admin');

        $result->assertOK();
        $result->assertSee('Initial setup');
    }

    public function testCreateFirstAdminFromInstallPage(): void
    {
        $result = $this->withBodyFormat('form')->post('/install/admin', [
            'username'         => 'adminuser',
            'email'            => 'admin@example.com',
            'password'         => 'StrongPass!123',
            'password_confirm' => 'StrongPass!123',
        ]);

        $result->assertRedirectTo('/dashboard');

        $user = (new UserModel())->where('username', 'adminuser')->first();

        $this->assertNotNull($user);
        $this->assertSame('admin@example.com', $user['email']);
    }

    public function testInstallPageBlockedAfterUserExists(): void
    {
        (new UserModel())->insert([
            'username'      => 'existing',
            'email'         => 'existing@example.com',
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active'     => 1,
        ]);

        $result = $this->get('/install/admin');

        $result->assertRedirectTo('/login');
    }
}
