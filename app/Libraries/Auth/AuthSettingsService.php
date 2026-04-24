<?php

namespace App\Libraries\Auth;

use App\Models\AuthSettingsModel;
use Config\Auth;

class AuthSettingsService
{
    /**
     * @return array<string, int|bool>
     */
    public function get(): array
    {
        $defaults = config(Auth::class);

        $fallback = [
            'min_password_length'        => $defaults->minPasswordLength,
            'require_uppercase'          => $defaults->requireUppercase,
            'require_lowercase'          => $defaults->requireLowercase,
            'require_number'             => $defaults->requireNumber,
            'require_symbol'             => $defaults->requireSymbol,
            'inactivity_timeout_seconds' => $defaults->inactivityTimeoutSeconds,
            'reset_token_ttl_minutes'    => $defaults->resetTokenTtlMinutes,
        ];

        $model = new AuthSettingsModel();
        $row   = $model->first();

        if ($row === null) {
            return $fallback;
        }

        return [
            'min_password_length'        => max(8, (int) ($row['min_password_length'] ?? $fallback['min_password_length'])),
            'require_uppercase'          => (bool) ($row['require_uppercase'] ?? $fallback['require_uppercase']),
            'require_lowercase'          => (bool) ($row['require_lowercase'] ?? $fallback['require_lowercase']),
            'require_number'             => (bool) ($row['require_number'] ?? $fallback['require_number']),
            'require_symbol'             => (bool) ($row['require_symbol'] ?? $fallback['require_symbol']),
            'inactivity_timeout_seconds' => max(60, (int) ($row['inactivity_timeout_seconds'] ?? $fallback['inactivity_timeout_seconds'])),
            'reset_token_ttl_minutes'    => max(5, (int) ($row['reset_token_ttl_minutes'] ?? $fallback['reset_token_ttl_minutes'])),
        ];
    }
}
