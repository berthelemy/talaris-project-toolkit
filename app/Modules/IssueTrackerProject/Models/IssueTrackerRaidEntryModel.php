<?php

/**
 * File documentation for app/Modules/IssueTrackerProject/Models/IssueTrackerRaidEntryModel.php.
 */

namespace App\Modules\IssueTrackerProject\Models;

use CodeIgniter\Model;

/**
 * Issue Tracker RAID entry persistence model.
 */
class IssueTrackerRaidEntryModel extends Model
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
        'impact_level',
        'date_reported',
        'reporter_user_id',
        'lessons_learned',
        'target_date',
        'review_date',
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'reporter_user_id' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
