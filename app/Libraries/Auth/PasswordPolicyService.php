<?php

namespace App\Libraries\Auth;

class PasswordPolicyService
{
    public function __construct(private readonly AuthSettingsService $settingsService = new AuthSettingsService())
    {
    }

    /**
     * @return array<string, int|bool>
     */
    public function policy(): array
    {
        return $this->settingsService->get();
    }

    /**
     * @return list<string>
     */
    public function validate(string $password): array
    {
        $policy = $this->policy();
        $errors = [];

        if (mb_strlen($password) < (int) $policy['min_password_length']) {
            $errors[] = lang('Auth.passwordPolicyMinLength', ['length' => (int) $policy['min_password_length']]);
        }

        if ($policy['require_uppercase'] && ! preg_match('/[A-Z]/', $password)) {
            $errors[] = lang('Auth.passwordPolicyUppercase');
        }

        if ($policy['require_lowercase'] && ! preg_match('/[a-z]/', $password)) {
            $errors[] = lang('Auth.passwordPolicyLowercase');
        }

        if ($policy['require_number'] && ! preg_match('/\d/', $password)) {
            $errors[] = lang('Auth.passwordPolicyNumber');
        }

        if ($policy['require_symbol'] && ! preg_match('/[^A-Za-z\d]/', $password)) {
            $errors[] = lang('Auth.passwordPolicySymbol');
        }

        return $errors;
    }
}
