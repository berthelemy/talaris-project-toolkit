<?php

namespace App\Modules\MeetingNotesProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Libraries\Modules\ModuleRegistryService;
use App\Modules\MeetingNotesProject\Models\MeetingNoteModel;
use App\Modules\RaidShared\Models\ModuleRaidEntryModel;
use App\Models\UserModel;

/**
 * Provides Meeting Notes dashboard widget definitions and data.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    private const ACTION_TASK_CATEGORY = 'meeting_action';

    /**
     * @param int $scopeId
     * @param array<string,mixed> $config
     * @return list<array{key:string,name:string,view:string,data:array<string,mixed>}>
     */
    public function getWidgetDefinitions(int $scopeId, array $config = []): array
    {
        $maxEntries = max(1, (int) ($config['max_entries'] ?? 5));
        $tasksModuleEnabled = (new ModuleRegistryService())->isEnabled('tasks_register_project', 'project');
        $decisionsModuleEnabled = (new ModuleRegistryService())->isEnabled('decisions_register_project', 'project');

        return [
            [
                'key' => 'overview',
                'name' => 'Meetings Overview',
                'view' => 'App\\Modules\\MeetingNotesProject\\Views\\widget_overview',
                'data' => [
                    'status_counts' => $this->statusCounts($scopeId),
                    'type_counts' => $this->typeCounts($scopeId),
                    'owners' => $this->ownerOptions(),
                    'tasks_module_enabled' => $tasksModuleEnabled,
                    'decisions_module_enabled' => $decisionsModuleEnabled,
                ],
            ],
            [
                'key' => 'open_actions',
                'name' => 'Open Meeting Actions',
                'view' => 'App\\Modules\\MeetingNotesProject\\Views\\widget_open_actions',
                'data' => [
                    'entries' => $this->openActions($scopeId, $maxEntries),
                    'entry_count' => count($this->openActions($scopeId, $maxEntries)),
                    'owners' => $this->ownerOptions(),
                    'tasks_module_enabled' => $tasksModuleEnabled,
                    'decisions_module_enabled' => $decisionsModuleEnabled,
                ],
            ],
            [
                'key' => 'upcoming_followups',
                'name' => 'Upcoming Follow-ups',
                'view' => 'App\\Modules\\MeetingNotesProject\\Views\\widget_upcoming_followups',
                'data' => [
                    'entries' => $this->upcomingFollowups($scopeId, $maxEntries),
                    'entry_count' => count($this->upcomingFollowups($scopeId, $maxEntries)),
                    'owners' => $this->ownerOptions(),
                    'tasks_module_enabled' => $tasksModuleEnabled,
                    'decisions_module_enabled' => $decisionsModuleEnabled,
                ],
            ],
        ];
    }

    /**
     * @param int $scopeId
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return null;
    }

    /**
     * @param int $scopeId
     * @param array<string,mixed> $config
     * @return array<string,mixed>
     */
    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = max(1, (int) ($config['max_entries'] ?? 5));

        return [
            'status_counts' => $this->statusCounts($scopeId),
            'type_counts' => $this->typeCounts($scopeId),
            'open_action_entries' => $this->openActions($scopeId, $maxEntries),
            'upcoming_followup_entries' => $this->upcomingFollowups($scopeId, $maxEntries),
        ];
    }

    /**
     * @return array{max_entries:int}
     */
    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }

    /**
     * @return array{draft:int,finalized:int,archived:int}
     */
    private function statusCounts(int $scopeId): array
    {
        $counts = [
            'draft' => 0,
            'finalized' => 0,
            'archived' => 0,
        ];

        $rows = (new MeetingNoteModel())
            ->select('status, COUNT(*) AS total')
            ->where('module_slug', 'meeting_notes_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->groupBy('status')
            ->findAll();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            if (! array_key_exists($status, $counts)) {
                continue;
            }

            $counts[$status] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    /**
     * @return array{stand-up:int,planning:int,steering:int,review:int,retrospective:int,other:int}
     */
    private function typeCounts(int $scopeId): array
    {
        $counts = [
            'stand-up' => 0,
            'planning' => 0,
            'steering' => 0,
            'review' => 0,
            'retrospective' => 0,
            'other' => 0,
        ];

        $rows = (new MeetingNoteModel())
            ->select('meeting_type, COUNT(*) AS total')
            ->where('module_slug', 'meeting_notes_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->groupBy('meeting_type')
            ->findAll();

        foreach ($rows as $row) {
            $type = (string) ($row['meeting_type'] ?? 'other');
            if ($type === '') {
                $type = 'other';
            }

            if (! array_key_exists($type, $counts)) {
                $type = 'other';
            }

            $counts[$type] += (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function openActions(int $scopeId, int $maxEntries): array
    {
        return (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, module_meeting_notes.title AS meeting_title, users.username AS owner_username')
            ->join('module_meeting_notes', 'module_meeting_notes.id = module_raid_entries.related_module_entry_id', 'left')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'tasks_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.task_category', self::ACTION_TASK_CATEGORY)
            ->whereIn('module_raid_entries.status', ['open', 'in_progress', 'blocked'])
            ->orderBy('module_raid_entries.due_date', 'ASC')
            ->orderBy('module_raid_entries.id', 'DESC')
            ->limit($maxEntries)
            ->findAll();
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function upcomingFollowups(int $scopeId, int $maxEntries): array
    {
        $today = date('Y-m-d');
        $horizon = date('Y-m-d', strtotime('+14 days'));

        return (new MeetingNoteModel())
            ->where('module_slug', 'meeting_notes_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->where('follow_up_date >=', $today)
            ->where('follow_up_date <=', $horizon)
            ->where('status !=', 'closed')
            ->orderBy('follow_up_date', 'ASC')
            ->limit($maxEntries)
            ->findAll();
    }

    /**
     * @return list<array{id:int,username:string}>
     */
    private function ownerOptions(): array
    {
        $rows = (new UserModel())
            ->select('id, username')
            ->where('is_active', 1)
            ->orderBy('username', 'ASC')
            ->findAll();

        return array_map(static fn (array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
        ], $rows);
    }
}