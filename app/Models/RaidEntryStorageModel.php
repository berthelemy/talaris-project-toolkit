<?php

/**
 * File documentation for app/Models/RaidEntryStorageModel.php.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Generic RAID entry storage model used by cross-module tests and utilities.
 */
class RaidEntryStorageModel extends Model
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
        'impact_if_not_valid',
        'impact_level',
        'date_reported',
        'reporter_user_id',
        'lessons_learned',
        'target_date',
        'review_date',
        'decision_date',
        'made_by_user_id',
        'decision_category',
        'decision_rationale',
        'alternatives_considered',
        'chosen_option',
        'approver_user_id',
        'implementation_actions',
        'superseded_by_entry_id',
        'dependency_type',
        'related_work_package',
        'depends_on',
        'escalation_required',
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
        'reporter_user_id' => '?integer',
        'made_by_user_id' => '?integer',
        'approver_user_id' => '?integer',
        'superseded_by_entry_id' => '?integer',
        'escalation_required' => 'integer',
        'related_module_entry_id' => '?integer',
        'percent_complete' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
