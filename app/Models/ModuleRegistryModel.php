<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for registered module metadata and enabled state.
 */
class ModuleRegistryModel extends Model
{
    protected $table            = 'module_registry';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'slug',
        'name',
        'scope_type',
        'description',
        'is_enabled',
    ];
}
