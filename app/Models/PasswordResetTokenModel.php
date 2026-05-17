<?php

/**
 * Persistence model for Password Reset Token Model.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for password reset token lifecycle records.
 */
class PasswordResetTokenModel extends Model
{
    protected $table            = 'password_reset_tokens';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'user_id',
        'token_hash',
        'expires_at',
        'used_at',
    ];
}
