<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * LocaleFilter component.
 */
class LocaleFilter implements FilterInterface
{
    public const COOKIE_NAME = 'talaris_locale';

    /**
     * @var list<string>
     */
    private array $supportedLocales = ['en', 'fr'];

    private string $defaultLocale = 'en';

    /**
     * Before operation.
     *
     * @param RequestInterface $request
     * @param mixed $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return null;
        }

        $locale = $this->resolveLocale($request);

        $request->setLocale($locale);
        service('language')->setLocale($locale);

        return null;
    }

    /**
     * After operation.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    private function resolveLocale(IncomingRequest $request): string
    {
        $cookieLocale = strtolower((string) $request->getCookie(self::COOKIE_NAME));

        if ($this->isSupportedLocale($cookieLocale)) {
            return $cookieLocale;
        }

        $profileLocale = $this->resolveProfileLocale();

        if ($profileLocale !== null) {
            return $profileLocale;
        }

        $browserLocale = $this->resolveBrowserLocale($request);

        if ($browserLocale !== null) {
            return $browserLocale;
        }

        return $this->defaultLocale;
    }

    private function resolveProfileLocale(): ?string
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        $user = (new UserModel())->find((int) $userId);

        if (! is_array($user)) {
            return null;
        }

        $locale = strtolower((string) ($user['language_preference'] ?? ''));

        if (! $this->isSupportedLocale($locale)) {
            return null;
        }

        return $locale;
    }

    private function isSupportedLocale(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales, true);
    }

    private function resolveBrowserLocale(IncomingRequest $request): ?string
    {
        $acceptLanguage = strtolower((string) $request->getHeaderLine('Accept-Language'));

        if ($acceptLanguage === '') {
            return null;
        }

        $entries = explode(',', $acceptLanguage);

        foreach ($entries as $entry) {
            $rawLocale = trim((string) explode(';', $entry)[0]);

            if ($rawLocale === '') {
                continue;
            }

            $locale = substr($rawLocale, 0, 2);

            if ($this->isSupportedLocale($locale)) {
                return $locale;
            }
        }

        return null;
    }
}
