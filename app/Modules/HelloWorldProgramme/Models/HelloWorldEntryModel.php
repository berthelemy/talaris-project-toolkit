<?php

namespace App\Modules\HelloWorldProgramme\Models;

use CodeIgniter\Model;

/**
 * HelloWorldEntryModel component.
 */
class HelloWorldEntryModel extends Model
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
