<?php

namespace App\Controllers;

use App\Filters\LocaleFilter;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * LanguageController component.
 */
class LanguageController extends BaseController
{
    /**
     * Switch operation.
     *
     * @return RedirectResponse
     */
    public function switch(): RedirectResponse
    {
        $locale = strtolower(trim((string) $this->request->getPost('locale')));

        if (! in_array($locale, ['en', 'fr'], true)) {
            return redirect()->back()->with('error', lang('Auth.languageSelectionInvalid'));
        }

        return redirect()->back()->setCookie(LocaleFilter::COOKIE_NAME, $locale, 31536000);
    }
}
