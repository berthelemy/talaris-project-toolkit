<?php

/**
 * Theme settings controller for branding and visual customization updates.
 */

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Theme\ThemeSettingsService;
use App\Models\ThemeSettingsModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * ThemeSettingsController component.
 */
class ThemeSettingsController extends BaseController
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

        if (! $this->canManageTheme($userId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $themeService = new ThemeSettingsService();

        return view('theme/edit', [
            'settings' => $themeService->get(),
            'fontOptions' => $themeService->fontOptions(),
        ]);
    }

    /**
     * Update operation.
     *
     * @return RedirectResponse
     */
    public function update(): RedirectResponse
    {
        $userId = $this->currentUserId();

        if ($userId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageTheme($userId)) {
            (new AuditLogger())->log('theme_settings_updated', 'failed', $userId, ['reason' => 'not_authorized']);

            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $themeService = new ThemeSettingsService();
        $allowedFonts = implode(',', $themeService->allowedFonts());

        $rules = [
            'heading_font' => 'required|in_list[' . $allowedFonts . ']',
            'body_font' => 'required|in_list[' . $allowedFonts . ']',
            'primary_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'secondary_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'background_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'text_color' => 'required|regex_match[/^#[0-9A-Fa-f]{6}$/]',
            'remove_logo' => 'permit_empty|in_list[1]',
            'logo' => 'permit_empty|max_size[logo,2048]|is_image[logo]|mime_in[logo,image/jpeg,image/png,image/gif,image/webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'heading_font' => (string) $this->request->getPost('heading_font'),
            'body_font' => (string) $this->request->getPost('body_font'),
            'primary_color' => strtolower((string) $this->request->getPost('primary_color')),
            'secondary_color' => strtolower((string) $this->request->getPost('secondary_color')),
            'background_color' => strtolower((string) $this->request->getPost('background_color')),
            'text_color' => strtolower((string) $this->request->getPost('text_color')),
        ];

        $contrastErrors = $this->contrastErrors($payload);

        if ($contrastErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $contrastErrors);
        }

        $model = new ThemeSettingsModel();
        $existing = $model->first();
        $publicDir = FCPATH . 'uploads/theme/';

        if (! is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        if ((string) $this->request->getPost('remove_logo') === '1') {
            if (is_array($existing) && ! empty($existing['logo_path'])) {
                $this->deleteLogoFile((string) $existing['logo_path']);
            }

            $payload['logo_path'] = null;
        }

        $logoFile = $this->request->getFile('logo');

        if ($logoFile !== null && $logoFile->isValid() && ! $logoFile->hasMoved()) {
            if (is_array($existing) && ! empty($existing['logo_path'])) {
                $this->deleteLogoFile((string) $existing['logo_path']);
            }

            $newName = $logoFile->getRandomName();
            $logoFile->move($publicDir, $newName);
            $payload['logo_path'] = 'uploads/theme/' . $newName;
        }

        if (is_array($existing)) {
            $model->update((int) $existing['id'], $payload);
        } else {
            $payload['id'] = 1;
            $model->insert($payload);
        }

        (new AuditLogger())->log('theme_settings_updated', 'success', $userId, [
            'fields' => array_keys($payload),
        ]);

        return redirect()->to('/theme')->with('success', lang('Theme.updatedSuccess'));
    }

    private function canManageTheme(int $userId): bool
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

    /**
     * @param array<string, string> $payload
     *
     * @return list<string>
     */
    private function contrastErrors(array $payload): array
    {
        $errors = [];

        $textContrast = $this->contrastRatio($payload['text_color'], $payload['background_color']);
        if ($textContrast < 4.5) {
            $errors[] = lang('Theme.contrastTextBackground');
        }

        $primaryContrast = $this->contrastRatio($payload['primary_color'], $payload['background_color']);
        if ($primaryContrast < 4.5) {
            $errors[] = lang('Theme.contrastPrimaryBackground');
        }

        $secondaryContrast = $this->contrastRatio($payload['secondary_color'], $payload['background_color']);
        if ($secondaryContrast < 3.0) {
            $errors[] = lang('Theme.contrastSecondaryBackground');
        }

        return $errors;
    }

    private function contrastRatio(string $hexA, string $hexB): float
    {
        $lumA = $this->luminance($hexA);
        $lumB = $this->luminance($hexB);

        $lighter = max($lumA, $lumB);
        $darker = min($lumA, $lumB);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function luminance(string $hex): float
    {
        [$red, $green, $blue] = $this->hexToRgb($hex);

        $channels = [$red / 255, $green / 255, $blue / 255];
        $adjusted = array_map(static function (float $channel): float {
            return $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;
        }, $channels);

        return (0.2126 * $adjusted[0]) + (0.7152 * $adjusted[1]) + (0.0722 * $adjusted[2]);
    }

    /**
     * @return array{int, int, int}
     */
    private function hexToRgb(string $hex): array
    {
        $cleanHex = ltrim($hex, '#');

        return [
            hexdec(substr($cleanHex, 0, 2)),
            hexdec(substr($cleanHex, 2, 2)),
            hexdec(substr($cleanHex, 4, 2)),
        ];
    }

    private function deleteLogoFile(string $relativePath): void
    {
        $absolutePath = FCPATH . ltrim($relativePath, '/');

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
