<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgrammeProjectModel extends Model
{
    protected $table            = 'programme_projects';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'programme_id',
        'project_id',
        'linked_by_user_id',
    ];
}