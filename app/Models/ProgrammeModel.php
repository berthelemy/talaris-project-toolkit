<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgrammeModel extends Model
{
    protected $table            = 'programmes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'name',
        'description',
        'owner_user_id',
    ];
}