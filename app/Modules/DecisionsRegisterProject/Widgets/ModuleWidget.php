<?php

namespace App\Modules\DecisionsRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;

/**
 * Provides Decisions Register dashboard widget definitions and data.
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
                'name' => (string) lang('Module.decisionsWidgetOverviewTitle'),
                'view' => 'App\\Modules\\DecisionsRegisterProject\\Views\\widget_overview',
                'data' => [
                    'overview_counts' => $this->overviewCounts($scopeId),
                ],
            ],
            [
                'key' => 'pending_implementation',
                'name' => (string) lang('Module.decisionsWidgetPendingTitle'),
                'view' => 'App\\Modules\\DecisionsRegisterProject\\Views\\widget_pending_implementation',
                'data' => $this->pendingImplementationData($scopeId, $maxEntries),
            ],
            [
                'key' => 'recent_key',
                'name' => (string) lang('Module.decisionsWidgetRecentKeyTitle'),
                'view' => 'App\\Modules\\DecisionsRegisterProject\\Views\\widget_recent_key',
                'data' => $this->recentKeyDecisionsData($scopeId, $maxEntries),
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
            'overview_counts' => $this->overviewCounts($scopeId),
            'pending_entries' => $this->pendingImplementationData($scopeId, $maxEntries)['entries'],
            'pending_entry_count' => $this->pendingImplementationData($scopeId, $maxEntries)['entry_count'],
            'recent_key_entries' => $this->recentKeyDecisionsData($scopeId, $maxEntries)['entries'],
            'recent_key_entry_count' => $this->recentKeyDecisionsData($scopeId, $maxEntries)['entry_count'],
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
     * @return array{draft:int,proposed:int,approved:int,implemented:int,rejected:int,superseded:int,closed:int}
     */
    private function overviewCounts(int $scopeId): array
    {
        $overviewCounts = [
            'draft' => 0,
            'proposed' => 0,
            'approved' => 0,
            'implemented' => 0,
            'rejected' => 0,
            'superseded' => 0,
            'closed' => 0,
        ];

        $countRows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.status, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'decisions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->groupBy('module_raid_entries.status')
            ->findAll();

        foreach ($countRows as $row) {
            $status = (string) ($row['status'] ?? '');
            if (! array_key_exists($status, $overviewCounts)) {
                continue;
            }

            $overviewCounts[$status] = (int) ($row['total'] ?? 0);
        }

        return $overviewCounts;
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    private function pendingImplementationData(int $scopeId, int $maxEntries): array
    {
        $entries = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, made_by.username as made_by_username')
            ->join('users as made_by', 'made_by.id = module_raid_entries.made_by_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'decisions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status', 'approved')
            ->orderBy('module_raid_entries.target_date', 'ASC')
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
    private function recentKeyDecisionsData(int $scopeId, int $maxEntries): array
    {
        $entries = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, made_by.username as made_by_username')
            ->join('users as made_by', 'made_by.id = module_raid_entries.made_by_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'decisions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->whereIn('module_raid_entries.status', ['approved', 'implemented'])
            ->whereIn('module_raid_entries.priority', ['high', 'critical'])
            ->orderBy('module_raid_entries.decision_date', 'DESC')
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
