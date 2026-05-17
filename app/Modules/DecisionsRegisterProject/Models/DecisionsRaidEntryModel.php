<?php

/**
 * File documentation for app/Modules/DecisionsRegisterProject/Models/DecisionsRaidEntryModel.php.
 */

namespace App\Modules\DecisionsRegisterProject\Models;

use CodeIgniter\Model;

/**
 * Decisions Register RAID entry persistence model.
 */
class DecisionsRaidEntryModel extends Model
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
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'made_by_user_id' => '?integer',
        'approver_user_id' => '?integer',
        'superseded_by_entry_id' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
