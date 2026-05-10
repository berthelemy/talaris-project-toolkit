<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for RBAC role definitions.
 */
class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'slug',
        'name',
        'description',
        'is_predefined',
        'permissions_json',
    ];
}