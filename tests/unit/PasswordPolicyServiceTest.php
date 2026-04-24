<?php

use App\Libraries\Auth\AuthSettingsService;
use App\Libraries\Auth\PasswordPolicyService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PasswordPolicyServiceTest extends CIUnitTestCase
{
    public function testValidateReturnsErrorsForWeakPassword(): void
    {
        $service = new PasswordPolicyService(new class () extends AuthSettingsService {
            public function get(): array
            {
                return [
                    'min_password_length'        => 12,
                    'require_uppercase'          => true,
                    'require_lowercase'          => true,
                    'require_number'             => true,
                    'require_symbol'             => true,
                    'inactivity_timeout_seconds' => 900,
                    'reset_token_ttl_minutes'    => 60,
                ];
            }
        });

        $errors = $service->validate('weakpass');

        $this->assertNotEmpty($errors);
    }

    public function testValidateAcceptsCompliantPassword(): void
    {
        $service = new PasswordPolicyService(new class () extends AuthSettingsService {
            public function get(): array
            {
                return [
                    'min_password_length'        => 12,
                    'require_uppercase'          => true,
                    'require_lowercase'          => true,
                    'require_number'             => true,
                    'require_symbol'             => true,
                    'inactivity_timeout_seconds' => 900,
                    'reset_token_ttl_minutes'    => 60,
                ];
            }
        });

        $errors = $service->validate('StrongPass!123');

        $this->assertSame([], $errors);
    }
}
