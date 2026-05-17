<?php

/**
 * Persistence model for User Model.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for platform user accounts and profile fields.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'username',
        'email',
        'password_hash',
        'is_active',
        'last_login_at',
        'language_preference',
        'profile_description',
        'avatar_path',
    ];
}
