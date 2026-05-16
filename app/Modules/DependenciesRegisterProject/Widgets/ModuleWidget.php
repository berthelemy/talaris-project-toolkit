<?php

namespace App\Modules\DependenciesRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;

/**
 * Provides Dependencies Register dashboard widget definitions and data.
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
                'name' => (string) lang('Module.dependenciesWidgetOverviewTitle'),
                'view' => 'App\\Modules\\DependenciesRegisterProject\\Views\\widget_overview',
                'data' => [
                    'status_counts' => $this->overviewStatusCounts($scopeId),
                    'impact_counts' => $this->overviewImpactCounts($scopeId),
                ],
            ],
            [
                'key' => 'at_risk',
                'name' => (string) lang('Module.dependenciesWidgetAtRiskTitle'),
                'view' => 'App\\Modules\\DependenciesRegisterProject\\Views\\widget_at_risk',
                'data' => $this->atRiskData($scopeId, $maxEntries),
            ],
            [
                'key' => 'overdue',
                'name' => (string) lang('Module.dependenciesWidgetOverdueTitle'),
                'view' => 'App\\Modules\\DependenciesRegisterProject\\Views\\widget_overdue',
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
            'impact_counts' => $this->overviewImpactCounts($scopeId),
            'at_risk_entries' => $this->atRiskData($scopeId, $maxEntries)['entries'],
            'at_risk_entry_count' => $this->atRiskData($scopeId, $maxEntries)['entry_count'],
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
     * @return array{open:int,in_progress:int,at_risk:int,blocked:int,fulfilled:int,cancelled:int,closed:int}
     */
    private function overviewStatusCounts(int $scopeId): array
    {
        $statusCounts = [
            'open' => 0,
            'in_progress' => 0,
            'at_risk' => 0,
            'blocked' => 0,
            'fulfilled' => 0,
            'cancelled' => 0,
            'closed' => 0,
        ];

        $rows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.status, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'dependencies_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->groupBy('module_raid_entries.status')
            ->findAll();

        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
            if (! array_key_exists($status, $statusCounts)) {
                continue;
            }

            $statusCounts[$status] = (int) ($row['total'] ?? 0);
        }

        return $statusCounts;
    }

    /**
     * @return array{low:int,medium:int,high:int}
     */
    private function overviewImpactCounts(int $scopeId): array
    {
        $impactCounts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
        ];

        $rows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.impact_level, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'dependencies_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->groupBy('module_raid_entries.impact_level')
            ->findAll();

        foreach ($rows as $row) {
            $impact = (string) ($row['impact_level'] ?? '');
            if (! array_key_exists($impact, $impactCounts)) {
                continue;
            }

            $impactCounts[$impact] = (int) ($row['total'] ?? 0);
        }

        return $impactCounts;
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    private function atRiskData(int $scopeId, int $maxEntries): array
    {
        $entries = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'dependencies_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->groupStart()
            ->whereIn('module_raid_entries.status', ['at_risk', 'blocked'])
            ->orWhere('module_raid_entries.impact_level', 'high')
            ->groupEnd()
            ->orderBy('module_raid_entries.updated_at', 'DESC')
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
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'dependencies_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.target_date <', $today)
            ->whereNotIn('module_raid_entries.status', ['fulfilled', 'closed'])
            ->orderBy('module_raid_entries.target_date', 'ASC')
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
