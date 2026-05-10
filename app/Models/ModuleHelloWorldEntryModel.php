<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleHelloWorldEntryModel extends Model
{
    protected $table            = 'module_hello_world_entries';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'message',
        'created_by_user_id',
    ];
}
