<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for scoped user-role assignment rows.
 */
class UserRoleAssignmentModel extends Model
{
    protected $table            = 'user_role_assignments';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'user_id',
        'role_id',
        'scope_type',
        'scope_id',
    ];
}