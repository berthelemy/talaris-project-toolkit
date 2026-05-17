<?php

/**
 * File documentation for app/Controllers/SiteSettingsController.php.
 */

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Theme\ThemeSettingsService;
use App\Models\ThemeSettingsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * SiteSettingsController component.
 */
class SiteSettingsController extends BaseController
{
    /**
     * Edit operation.
     *
     * @return string|RedirectResponse
     */
    public function edit(): string|RedirectResponse
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageSiteSettings($userId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        return view('site_settings/edit', [
            'settings' => (new ThemeSettingsService())->get(),
        ]);
    }

    /**
     * Update operation.
     */
    public function update(): RedirectResponse
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageSiteSettings($userId)) {
            (new AuditLogger())->log('site_settings_updated', 'failed', $userId, ['reason' => 'not_authorized']);

            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $rules = [
            'site_title' => 'required|min_length[2]|max_length[120]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $siteTitle = trim((string) $this->request->getPost('site_title'));

        $model = new ThemeSettingsModel();
        $existing = $model->first();

        if (is_array($existing)) {
            $model->update((int) $existing['id'], ['site_title' => $siteTitle]);
        } else {
            $model->insert([
                'id' => 1,
                'site_title' => $siteTitle,
            ]);
        }

        (new AuditLogger())->log('site_settings_updated', 'success', $userId, [
            'fields' => ['site_title'],
        ]);

        return redirect()->to('/site-settings')->with('success', lang('SiteSettings.updatedSuccess'));
    }

    private function canManageSiteSettings(int $userId): bool
    {
        return (new RbacService())->hasPermission($userId, 'system.theme.manage', 'system', null);
    }

    private function currentUserId(): ?int
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        $user = (new UserModel())->find((int) $userId);

        if (! is_array($user)) {
            return null;
        }

        return (int) $userId;
    }
}
