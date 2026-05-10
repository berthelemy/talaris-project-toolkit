<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\PasswordPolicyService;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProfileController extends BaseController
{
    public function edit(): string|RedirectResponse
    {
        $user = $this->currentUser();

        if ($user === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        return view('auth/profile', [
            'user' => $user,
        ]);
    }

    public function update(): RedirectResponse
    {
        $user = $this->currentUser();

        if ($user === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $rules = [
            'language_preference' => 'permit_empty|in_list[en,fr]',
            'profile_description' => 'permit_empty|max_length[1000]',
            'avatar'              => 'permit_empty|uploaded[avatar]|max_size[avatar,2048]|is_image[avatar]|mime_in[avatar,image/jpeg,image/png,image/gif,image/webp]',
        ];

        if (! $this->validateData(
            array_merge($this->request->getPost(), ['avatar' => $this->request->getFile('avatar')]),
            $rules,
        )) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'language_preference' => $this->normalizeOptionalString((string) $this->request->getPost('language_preference')),
            'profile_description' => $this->normalizeOptionalString((string) $this->request->getPost('profile_description')),
        ];

        $avatarFile = $this->request->getFile('avatar');

        if ($avatarFile !== null && $avatarFile->isValid() && ! $avatarFile->hasMoved()) {
            $avatarDir = WRITEPATH . 'uploads/avatars/';

            if (! is_dir($avatarDir)) {
                mkdir($avatarDir, 0755, true);
            }

            $newName = $avatarFile->getRandomName();
            $avatarFile->move($avatarDir, $newName);
            $payload['avatar_path'] = 'uploads/avatars/' . $newName;
        }

        (new UserModel())->update((int) $user['id'], $payload);

        (new AuditLogger())->log('profile_updated', 'success', (int) $user['id'], [
            'fields' => array_keys($payload),
        ]);

        return redirect()->to('/profile')->with('success', lang('Auth.profileUpdatedSuccess'));
    }

    public function changePassword(): RedirectResponse
    {
        $user = $this->currentUser();

        if ($user === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $newPasswordConfirm = (string) $this->request->getPost('new_password_confirm');

        if ($currentPassword === '' || ! password_verify($currentPassword, (string) $user['password_hash'])) {
            (new AuditLogger())->log('profile_password_change', 'failed', (int) $user['id'], [
                'reason' => 'current_password_invalid',
            ]);

            return redirect()->back()->with('error', lang('Auth.currentPasswordInvalid'));
        }

        if ($newPassword === '' || $newPassword !== $newPasswordConfirm) {
            return redirect()->back()->with('error', lang('Auth.passwordConfirmationMismatch'));
        }

        $policyErrors = (new PasswordPolicyService())->validate($newPassword);

        if ($policyErrors !== []) {
            return redirect()->back()->with('errors', $policyErrors);
        }

        (new UserModel())->update((int) $user['id'], [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        (new AuditLogger())->log('profile_password_change', 'success', (int) $user['id']);

        return redirect()->to('/profile')->with('success', lang('Auth.profilePasswordUpdatedSuccess'));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function currentUser(): ?array
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        return (new UserModel())->find((int) $userId);
    }

    private function normalizeOptionalString(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}