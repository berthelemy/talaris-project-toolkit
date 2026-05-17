<?php

/**
 * File documentation for app/Modules/MeetingNotesProject/Models/MeetingNoteModel.php.
 */

namespace App\Modules\MeetingNotesProject\Models;

use CodeIgniter\Model;

/**
 * Persistence model for meeting note records.
 */
class MeetingNoteModel extends Model
{
    protected $table            = 'module_meeting_notes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'module_slug',
        'scope_type',
        'scope_id',
        'title',
        'purpose',
        'meeting_date',
        'meeting_type',
        'context_level',
        'related_objective',
        'chair_user_id',
        'minute_taker_user_id',
        'attendees_text',
        'absentees_text',
        'agenda_text',
        'discussion_text',
        'decisions_text',
        'raised_links_text',
        'follow_up_date',
        'status',
        'lessons_learned',
        'closed_at',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected array $casts = [
        'scope_id' => 'integer',
        'chair_user_id' => '?integer',
        'minute_taker_user_id' => '?integer',
        'created_by_user_id' => 'integer',
        'updated_by_user_id' => '?integer',
    ];
}
