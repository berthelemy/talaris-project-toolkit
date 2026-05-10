<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for per-widget usage metrics.
 */
class ModuleWidgetMetricModel extends Model
{
    protected $table            = 'module_widget_metrics';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'loaded_count',
        'rendered_count',
        'error_count',
        'last_rendered_at',
    ];
}
