<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\AuthSettingsService;
use App\Libraries\Auth\PasswordPolicyService;
use App\Models\PasswordResetTokenModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AuthController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        $session = session();

        if ($session->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin(): RedirectResponse
    {
        $rules = [
            'username' => 'required|max_length[100]',
            'password' => 'required',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())
            ->groupStart()
            ->where('username', $username)
            ->orWhere('email', $username)
            ->groupEnd()
            ->first();

        $audit = new AuditLogger();

        if ($user === null || ! (bool) $user['is_active'] || ! password_verify($password, (string) $user['password_hash'])) {
            $audit->log('login', 'failed', $user['id'] ?? null, ['username' => $username]);

            return redirect()->back()->withInput()->with('error', lang('Auth.invalidCredentials'));
        }

        session()->regenerate();
        session()->set([
            'user_id'          => (int) $user['id'],
            'username'         => (string) $user['username'],
            'last_activity_at' => time(),
        ]);

        (new UserModel())->update((int) $user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        $audit->log('login', 'success', (int) $user['id']);

        return redirect()->to('/dashboard')->with('success', lang('Auth.loginSuccess'));
    }

    public function logout(): RedirectResponse
    {
        $userId = session('user_id');

        if ($userId !== null) {
            (new AuditLogger())->log('logout', 'success', (int) $userId);
        }

        session()->destroy();

        return redirect()->to('/login')->with('success', lang('Auth.logoutSuccess'));
    }

    public function forgotPassword(): string
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink(): RedirectResponse
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = (string) $this->request->getPost('email');
        $user  = (new UserModel())->where('email', $email)->first();

        if ($user !== null) {
            $rawToken  = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $settings  = (new AuthSettingsService())->get();

            $expiresAt = date('Y-m-d H:i:s', time() + ((int) $settings['reset_token_ttl_minutes'] * 60));

            (new PasswordResetTokenModel())->insert([
                'user_id'    => (int) $user['id'],
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
            ]);

            $resetUrl = base_url('reset-password/' . $rawToken);

            $message = lang('Auth.resetEmailBody', [
                'username' => (string) $user['username'],
                'link'     => $resetUrl,
            ]);

            $mailer = service('email');
            $mailer->setTo((string) $user['email']);
            $mailer->setSubject(lang('Auth.resetEmailSubject'));
            $mailer->setMessage($message);

            $sent = $mailer->send();

            (new AuditLogger())->log('password_reset_requested', $sent ? 'success' : 'queued', (int) $user['id']);
        }

        return redirect()->to('/forgot-password')->with('success', lang('Auth.resetEmailSent'));
    }

    public function resetPasswordForm(string $token): string|RedirectResponse
    {
        if (! $this->hasValidResetToken($token)) {
            return redirect()->to('/forgot-password')->with('error', lang('Auth.resetTokenInvalid'));
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    public function resetPassword(string $token): RedirectResponse
    {
        $tokenRow = $this->findValidResetToken($token);

        if ($tokenRow === null) {
            return redirect()->to('/forgot-password')->with('error', lang('Auth.resetTokenInvalid'));
        }

        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        if ($password === '' || $password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('error', lang('Auth.passwordConfirmationMismatch'));
        }

        $policyErrors = (new PasswordPolicyService())->validate($password);

        if ($policyErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $policyErrors);
        }

        (new UserModel())->update((int) $tokenRow['user_id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        (new PasswordResetTokenModel())->update((int) $tokenRow['id'], ['used_at' => date('Y-m-d H:i:s')]);
        (new AuditLogger())->log('password_reset_completed', 'success', (int) $tokenRow['user_id']);

        return redirect()->to('/login')->with('success', lang('Auth.passwordResetSuccess'));
    }

    private function hasValidResetToken(string $token): bool
    {
        return $this->findValidResetToken($token) !== null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findValidResetToken(string $token): ?array
    {
        $tokenHash = hash('sha256', $token);

        return (new PasswordResetTokenModel())
            ->where('token_hash', $tokenHash)
            ->where('used_at', null)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();
    }
}
