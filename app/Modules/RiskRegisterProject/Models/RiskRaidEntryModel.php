<?php

/**
 * File documentation for app/Modules/RiskRegisterProject/Models/RiskRaidEntryModel.php.
 */

namespace App\Modules\RiskRegisterProject\Models;

use CodeIgniter\Model;

/**
 * Risk Register RAID entry persistence model.
 */
class RiskRaidEntryModel extends Model
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
        'target_date',
        'review_date',
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'owner_user_id' => 'integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
