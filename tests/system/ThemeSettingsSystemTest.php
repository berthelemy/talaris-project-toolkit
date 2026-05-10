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
final class ThemeSettingsSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testAdministratorCanUpdateThemeSettingsAndAuditIsWritten(): void
    {
        $admin = $this->createUser('themeadmin', 'themeadmin@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $result = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/theme', [
            'heading_font' => 'merriweather',
            'body_font' => 'source_sans',
            'primary_color' => '#123456',
            'secondary_color' => '#345678',
            'background_color' => '#ffffff',
            'text_color' => '#111111',
        ]);

        $result->assertRedirectTo('/theme');

        $saved = (new ThemeSettingsModel())->first();
        $this->assertNotNull($saved);
        $this->assertSame('merriweather', $saved['heading_font']);
        $this->assertSame('#123456', strtolower((string) $saved['primary_color']));

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'theme_settings_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('success', $audit['status']);
        $this->assertSame((int) $admin['id'], (int) $audit['user_id']);

        $dashboard = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->get('/dashboard');

        $dashboard->assertOK();
        $dashboard->assertSee('--talaris-primary: #123456');
    }

    public function testNonAdministratorCannotAccessThemeSettings(): void
    {
        $member = $this->createUser('themeuser', 'themeuser@example.com');

        $result = $this->withSession([
            'user_id' => (int) $member['id'],
            'username' => (string) $member['username'],
            'last_activity_at' => time(),
        ])->get('/theme');

        $result->assertRedirectTo('/dashboard');

        $postResult = $this->withSession([
            'user_id' => (int) $member['id'],
            'username' => (string) $member['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/theme', [
            'heading_font' => 'poppins',
            'body_font' => 'source_sans',
            'primary_color' => '#123456',
            'secondary_color' => '#345678',
            'background_color' => '#ffffff',
            'text_color' => '#111111',
        ]);

        $postResult->assertRedirectTo('/dashboard');

        $audit = (new AuthAuditLogModel())
            ->where('event_type', 'theme_settings_updated')
            ->orderBy('id', 'DESC')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame('failed', $audit['status']);
        $this->assertSame((int) $member['id'], (int) $audit['user_id']);
    }

    public function testContrastValidationRejectsInaccessibleColors(): void
    {
        $admin = $this->createUser('themeadmin2', 'themeadmin2@example.com');
        (new RbacService())->assignRoleToUser((int) $admin['id'], 'administrator', 'system', null, (int) $admin['id']);

        $result = $this->withSession([
            'user_id' => (int) $admin['id'],
            'username' => (string) $admin['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/theme', [
            'heading_font' => 'poppins',
            'body_font' => 'source_sans',
            'primary_color' => '#f5f5f5',
            'secondary_color' => '#f1f1f1',
            'background_color' => '#ffffff',
            'text_color' => '#fefefe',
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('errors');
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
