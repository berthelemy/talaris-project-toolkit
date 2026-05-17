<?php

namespace App\Modules\DependenciesRegisterProject\Models;

use CodeIgniter\Model;

/**
 * Dependencies Register RAID entry persistence model.
 */
class DependenciesRaidEntryModel extends Model
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
        'lessons_learned',
        'target_date',
        'review_date',
        'dependency_type',
        'related_work_package',
        'depends_on',
        'escalation_required',
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'escalation_required' => 'integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
