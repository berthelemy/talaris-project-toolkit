<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthSettingsModel extends Model
{
    protected $table            = 'auth_settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'min_password_length',
        'require_uppercase',
        'require_lowercase',
        'require_number',
        'require_symbol',
        'inactivity_timeout_seconds',
        'reset_token_ttl_minutes',
    ];
}
