<?php

/**
 * TasksRegisterProject module persistence model: TasksRaidEntryModel.
 */

namespace App\Modules\TasksRegisterProject\Models;

use CodeIgniter\Model;

/**
 * Tasks Register RAID entry persistence model.
 */
class TasksRaidEntryModel extends Model
{
    protected $table            = 'module_raid_entries';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'title',
        'description',
        'mitigation_actions',
        'owner_user_id',
        'status',
        'priority',
        'impact',
        'likelihood',
        'lessons_learned',
        'target_date',
        'review_date',
        'task_category',
        'related_objective',
        'related_module_entry_id',
        'collaborators',
        'percent_complete',
        'planned_start_date',
        'due_date',
        'completed_date',
        'blocked_reason',
        'next_action',
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'related_module_entry_id' => '?integer',
        'percent_complete' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
