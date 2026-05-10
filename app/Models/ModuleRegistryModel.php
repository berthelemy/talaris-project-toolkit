<?php

namespace App\Models;

use CodeIgniter\Model;

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
