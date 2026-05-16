<?php

namespace App\Modules\IssueTrackerProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Modules\RaidShared\Models\ModuleRaidEntryModel;

/**
 * Provides Issue Tracker dashboard widget definitions and data.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Project identifier.
     * @param array<string,mixed> $config Widget configuration.
     * @return list<array{key:string,name:string,view:string,data:array<string,mixed>}>
     */
    public function getWidgetDefinitions(int $scopeId, array $config = []): array
    {
        $maxEntries = max(1, (int) ($config['max_entries'] ?? 5));

        return [
            [
                'key' => 'overview',
                'name' => (string) lang('Module.issuesWidgetOverviewTitle'),
                'view' => 'App\\Modules\\IssueTrackerProject\\Views\\widget_overview',
                'data' => [
                    'status_counts' => $this->overviewStatusCounts($scopeId),
                    'priority_counts' => $this->overviewPriorityCounts($scopeId),
                ],
            ],
            [
                'key' => 'high_priority',
                'name' => (string) lang('Module.issuesWidgetHighPriorityTitle'),
                'view' => 'App\\Modules\\IssueTrackerProject\\Views\\widget_high_priority',
                'data' => $this->highPriorityData($scopeId, $maxEntries),
            ],
            [
                'key' => 'overdue',
                'name' => (string) lang('Module.issuesWidgetOverdueTitle'),
                'view' => 'App\\Modules\\IssueTrackerProject\\Views\\widget_overdue',
                'data' => $this->overdueData($scopeId, $maxEntries),
            ],
        ];
    }

    /**
     * @param int $scopeId Project identifier.
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return null;
    }

    /**
     * @param int $scopeId Project identifier.
     * @param array<string,mixed> $config Widget configuration.
     * @return array<string,mixed>
     */
    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = max(1, (int) ($config['max_entries'] ?? 5));

        return [
            'status_counts' => $this->overviewStatusCounts($scopeId),
            'priority_counts' => $this->overviewPriorityCounts($scopeId),
            'high_priority_entries' => $this->highPriorityData($scopeId, $maxEntries)['entries'],
            'high_priority_entry_count' => $this->highPriorityData($scopeId, $maxEntries)['entry_count'],
            'overdue_entries' => $this->overdueData($scopeId, $maxEntries)['entries'],
            'overdue_entry_count' => $this->overdueData($scopeId, $maxEntries)['entry_count'],
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
     * @return array{open:int,in_review:int,blocked:int,resolved:int,closed:int}
     */
    private function overviewStatusCounts(int $scopeId): array
    {
        $counts = [
            'open' => 0,
            'in_review' => 0,
            'blocked' => 0,
            'resolved' => 0,
            'closed' => 0,
        ];

        $rows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.status, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'issue_tracker_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->groupBy('module_raid_entries.status')
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
     * @return array{low:int,medium:int,high:int,critical:int}
     */
    private function overviewPriorityCounts(int $scopeId): array
    {
        $counts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'critical' => 0,
        ];

        $rows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.priority, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'issue_tracker_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status !=', 'closed')
            ->groupBy('module_raid_entries.priority')
            ->findAll();

        foreach ($rows as $row) {
            $priority = (string) ($row['priority'] ?? '');
            if (! array_key_exists($priority, $counts)) {
                continue;
            }

            $counts[$priority] = (int) ($row['total'] ?? 0);
        }

        return $counts;
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    private function highPriorityData(int $scopeId, int $maxEntries): array
    {
        $entries = (new ModuleRaidEntryModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->whereIn('priority', ['high', 'critical'])
            ->where('status !=', 'closed')
            ->orderBy('updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    private function overdueData(int $scopeId, int $maxEntries): array
    {
        $today = date('Y-m-d');

        $entries = (new ModuleRaidEntryModel())
            ->where('module_slug', 'issue_tracker_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->where('target_date <', $today)
            ->where('status !=', 'closed')
            ->orderBy('target_date', 'ASC')
            ->orderBy('updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
