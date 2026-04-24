<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
    public int $minPasswordLength = 12;

    public bool $requireUppercase = true;

    public bool $requireLowercase = true;

    public bool $requireNumber = true;

    public bool $requireSymbol = true;

    public int $inactivityTimeoutSeconds = 900;

    public int $resetTokenTtlMinutes = 60;
}
