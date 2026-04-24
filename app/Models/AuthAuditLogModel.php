<?php

namespace App\Models;

use CodeIgniter\Model;

class AuthAuditLogModel extends Model
{
    protected $table            = 'auth_audit_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'user_id',
        'event_type',
        'status',
        'ip_address',
        'user_agent',
        'metadata',
    ];
}
