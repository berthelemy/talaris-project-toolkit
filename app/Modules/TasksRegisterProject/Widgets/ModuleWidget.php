<?php

/**
 * TasksRegisterProject module widget provider for dashboard cards and summary metrics.
 */

namespace App\Modules\TasksRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Modules\TasksRegisterProject\Models\TasksRaidEntryModel;

/**
 * Provides Tasks Register dashboard widget definitions and data.
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
                'name' => (string) lang('Module.tasksWidgetOverviewTitle'),
                'view' => 'App\\Modules\\TasksRegisterProject\\Views\\widget_overview',
                'data' => [
                    'status_counts' => $this->overviewStatusCounts($scopeId),
                    'priority_counts' => $this->overviewPriorityCounts($scopeId),
                ],
            ],
            [
                'key' => 'my_open',
                'name' => (string) lang('Module.tasksWidgetMyOpenTitle'),
                'view' => 'App\\Modules\\TasksRegisterProject\\Views\\widget_my_open',
                'data' => $this->myOpenTasksData($scopeId, $maxEntries),
            ],
            [
                'key' => 'overdue',
                'name' => (string) lang('Module.tasksWidgetOverdueTitle'),
                'view' => 'App\\Modules\\TasksRegisterProject\\Views\\widget_overdue',
                'data' => $this->overdueTasksData($scopeId, $maxEntries),
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
            'my_open_entries' => $this->myOpenTasksData($scopeId, $maxEntries)['entries'],
            'my_open_entry_count' => $this->myOpenTasksData($scopeId, $maxEntries)['entry_count'],
            'overdue_entries' => $this->overdueTasksData($scopeId, $maxEntries)['entries'],
            'overdue_entry_count' => $this->overdueTasksData($scopeId, $maxEntries)['entry_count'],
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
     * @return array{open:int,in_progress:int,blocked:int,in_review:int,completed:int,cancelled:int,closed:int}
     */
    private function overviewStatusCounts(int $scopeId): array
    {
        $counts = [
            'open' => 0,
            'in_progress' => 0,
            'blocked' => 0,
            'in_review' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'closed' => 0,
        ];

        $rows = (new TasksRaidEntryModel())
            ->select('module_raid_entries.status, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'tasks_register_project')
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

        $rows = (new TasksRaidEntryModel())
            ->select('module_raid_entries.priority, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'tasks_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->whereNotIn('module_raid_entries.status', ['closed'])
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
    private function myOpenTasksData(int $scopeId, int $maxEntries): array
    {
        $actorId = (int) (session('user_id') ?? 0);

        if ($actorId <= 0) {
            return ['entries' => [], 'entry_count' => 0];
        }

        $entries = (new TasksRaidEntryModel())
            ->where('module_slug', 'tasks_register_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->where('owner_user_id', $actorId)
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->orderBy('due_date', 'ASC')
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
    private function overdueTasksData(int $scopeId, int $maxEntries): array
    {
        $today = date('Y-m-d');

        $entries = (new TasksRaidEntryModel())
            ->where('module_slug', 'tasks_register_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->where('due_date <', $today)
            ->whereNotIn('status', ['completed', 'closed'])
            ->orderBy('due_date', 'ASC')
            ->orderBy('updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
