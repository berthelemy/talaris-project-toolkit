<?php

/**
 * Persistence model for Module Registry Model.
 */

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
        'display_order',
        'is_enabled',
        'version',
        'dependencies_json',
        'widget_permission',
        'widget_config_json',
    ];

    protected array $casts = [
        'is_enabled' => 'boolean',
        'display_order' => 'integer',
    ];
}
