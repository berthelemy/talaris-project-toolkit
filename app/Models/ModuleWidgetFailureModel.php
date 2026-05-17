<?php

/**
 * Persistence model for Module Widget Failure Model.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for widget load/render failures.
 */
class ModuleWidgetFailureModel extends Model
{
    protected $table            = 'module_widget_failures';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'user_id',
        'phase',
        'error_message',
        'trace',
    ];
}
