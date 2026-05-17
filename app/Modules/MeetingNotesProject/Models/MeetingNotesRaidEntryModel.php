<?php

/**
 * MeetingNotesProject module persistence model: MeetingNotesRaidEntryModel.
 */

namespace App\Modules\MeetingNotesProject\Models;

use CodeIgniter\Model;

/**
 * RAID entry persistence model used for Meeting Notes related links.
 */
class MeetingNotesRaidEntryModel extends Model
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
        'owner_user_id',
        'status',
        'priority',
        'decision_date',
        'made_by_user_id',
        'decision_rationale',
        'task_category',
        'related_objective',
        'related_module_entry_id',
        'due_date',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'made_by_user_id' => '?integer',
        'related_module_entry_id' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
