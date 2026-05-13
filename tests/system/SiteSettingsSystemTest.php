<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\ThemeSettingsModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class SiteSettingsSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanUpdateSiteTitleAndHeaderReflectsNewValue(): void
    {
        $admin = $this->createUser('siteadmin', 'siteadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $result = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/site-settings', [
            'site_title' => 'ACME Portfolio Hub',
        ]);

        $result->assertRedirectTo('/site-settings');

        $saved = (new ThemeSettingsModel())->first();
        $this->assertNotNull($saved);
        $this->assertSame('ACME Portfolio Hub', (string) ($saved['site_title'] ?? ''));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'site_settings_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);

        $dashboard = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->get('/dashboard');

        $dashboard->assertOK();
        $dashboard->assertSee('ACME Portfolio Hub');
    }

    public function testNonAdministratorCannotAccessSiteSettings(): void
    {
        $member = $this->createUser('sitemember', 'sitemember@example.com');

        $result = $this->withSession([
            'user_id' => (int) $member['id'],
            'username' => (string) $member['username'],
            'last_activity_at' => time(),
        ])->get('/site-settings');

        $result->assertRedirectTo('/dashboard');

        $postResult = $this->withSession([
            'user_id' => (int) $member['id'],
            'username' => (string) $member['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/site-settings', [
            'site_title' => 'Unauthorized Attempt',
        ]);

        $postResult->assertRedirectTo('/dashboard');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'site_settings_updated')
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
