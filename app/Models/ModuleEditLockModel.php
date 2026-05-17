<?php

/**
 * File documentation for app/Models/ModuleEditLockModel.php.
 */

namespace App\Models;

use CodeIgniter\Model;

class ModuleEditLockModel extends Model
{
    protected $table            = 'module_edit_locks';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'locked_by_user_id',
        'acquired_at',
        'expires_at',
    ];
}
