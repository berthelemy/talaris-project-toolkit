<?php

use App\Filters\LocaleFilter;
use App\Models\UserModel;
use Config\Services;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class LocalizationSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();

        Services::superglobals()->unsetCookie(LocaleFilter::COOKIE_NAME);
    }

    public function testFrenchBrowserLocaleRendersFrenchLoginStrings(): void
    {
        $result = $this->withHeaders([
            'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
        ])->get('/login');

        $result->assertOK();
        $this->assertSame('fr', service('request')->getLocale());
    }

    public function testUnsupportedBrowserLocaleFallsBackToEnglish(): void
    {
        $result = $this->withHeaders([
            'Accept-Language' => 'es-ES,es;q=0.9',
        ])->get('/login');

        $result->assertOK();
        $this->assertSame('en', service('request')->getLocale());
    }

    public function testLanguageSelectorCookiePersistsAcrossSignedOutAndSignedInScreens(): void
    {
        $user = $this->createUser();

        $switchResult = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'last_activity_at' => time(),
        ])->withBodyFormat('form')->post('/language', [
            'locale' => 'fr',
        ]);

        $switchResult->assertRedirect();
        $switchResult->assertCookie(LocaleFilter::COOKIE_NAME, 'fr');

        Services::superglobals()->setCookie(LocaleFilter::COOKIE_NAME, 'fr');

        $signedOutResult = $this->get('/login');

        $signedOutResult->assertOK();
        $this->assertSame('fr', service('request')->getLocale());

        $signedInResult = $this->withSession([
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'last_activity_at' => time(),
        ])->get('/dashboard');

        $signedInResult->assertOK();
        $this->assertSame('fr', service('request')->getLocale());
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => 'localeuser',
            'email' => 'localeuser@example.com',
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return (array) $model->where('username', 'localeuser')->first();
    }
}
