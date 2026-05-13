<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for widget visibility and ordering preferences by scope.
 */
class ModuleWidgetLayoutPreferenceModel extends Model
{
    protected $table            = 'module_widget_layout_preferences';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'scope_type',
        'scope_id',
        'module_slug',
        'is_visible',
        'display_order',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'is_visible' => 'boolean',
        'display_order' => '?integer',
        'updated_by_user_id' => '?integer',
    ];
}
