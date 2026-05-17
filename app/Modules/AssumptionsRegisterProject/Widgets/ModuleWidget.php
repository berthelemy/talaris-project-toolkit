<?php

/**
 * File documentation for app/Modules/AssumptionsRegisterProject/Widgets/ModuleWidget.php.
 */

namespace App\Modules\AssumptionsRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Modules\AssumptionsRegisterProject\Models\AssumptionsRaidEntryModel;

/**
 * Provides Assumptions Register dashboard widget definitions and data.
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
        $maxEntries = (int) ($config['max_entries'] ?? 5);
        if ($maxEntries <= 0) {
            $maxEntries = 5;
        }

        return [
            [
                'key' => 'overview',
                'name' => (string) lang('Module.assumptionsWidgetOverviewTitle'),
                'view' => 'App\Modules\AssumptionsRegisterProject\Views\widget_overview',
                'data' => [
                    'overview_counts' => $this->overviewCounts($scopeId),
                ],
            ],
            [
                'key' => 'high_priority',
                'name' => (string) lang('Module.assumptionsWidgetHighPriorityTitle'),
                'view' => 'App\Modules\AssumptionsRegisterProject\Views\widget_high_priority',
                'data' => $this->highPriorityData($scopeId, $maxEntries),
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

        return array_merge(
            ['overview_counts' => $this->overviewCounts($scopeId)],
            $this->highPriorityData($scopeId, $maxEntries),
        );
    }

    /**
     * @return array{max_entries:int}
     */
    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }

    /**
     * @return array{low:int,medium:int,high:int}
     */
    private function overviewCounts(int $scopeId): array
    {
        $overviewCounts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
        ];

        $countRows = (new AssumptionsRaidEntryModel())
            ->select('module_raid_entries.impact_level, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'assumptions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status !=', 'closed')
            ->groupBy('module_raid_entries.impact_level')
            ->findAll();

        foreach ($countRows as $row) {
            $impactLevel = (string) ($row['impact_level'] ?? '');
            if (! array_key_exists($impactLevel, $overviewCounts)) {
                continue;
            }

            $overviewCounts[$impactLevel] = (int) ($row['total'] ?? 0);
        }

        return $overviewCounts;
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    private function highPriorityData(int $scopeId, int $maxEntries): array
    {
        $entries = (new AssumptionsRaidEntryModel())
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'assumptions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status !=', 'closed')
            ->where('module_raid_entries.impact_level', 'high')
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
