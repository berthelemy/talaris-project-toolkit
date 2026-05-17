<?php

/**
 * File documentation for app/Models/ProjectModel.php.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for project entities.
 */
class ProjectModel extends Model
{
    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'status',
        'owner_user_id',
    ];
}
