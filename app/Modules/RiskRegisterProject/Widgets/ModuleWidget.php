<?php

namespace App\Modules\RiskRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;
use App\Models\UserModel;

/**
 * Provides Risk Register dashboard widget definitions and data.
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
                'name' => (string) lang('Module.riskWidgetOverviewTitle'),
                'view' => 'App\Modules\RiskRegisterProject\Views\widget_overview',
                'data' => [
                    'overview_counts' => $this->overviewCounts($scopeId),
                ],
            ],
            [
                'key' => 'high_priority',
                'name' => (string) lang('Module.riskWidgetHighPriorityTitle'),
                'view' => 'App\Modules\RiskRegisterProject\Views\widget_high_priority',
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
     * @return array{low:int,medium:int,high:int,critical:int}
     */
    private function overviewCounts(int $scopeId): array
    {
        $overviewCounts = [
            'low' => 0,
            'medium' => 0,
            'high' => 0,
            'critical' => 0,
        ];

        $countRows = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.priority, COUNT(*) AS total')
            ->where('module_raid_entries.module_slug', 'risk_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status !=', 'closed')
            ->groupBy('module_raid_entries.priority')
            ->findAll();

        foreach ($countRows as $row) {
            $priority = (string) ($row['priority'] ?? '');
            if (! array_key_exists($priority, $overviewCounts)) {
                continue;
            }

            $overviewCounts[$priority] = (int) ($row['total'] ?? 0);
        }

        return $overviewCounts;
    }

    /**
     * @return array{entries:list<array<string,mixed>>,entry_count:int,owners:list<array{id:int,username:string}>,status_options:list<string>,risk_scale_options:list<string>}
     */
    private function highPriorityData(int $scopeId, int $maxEntries): array
    {
        $entries = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'risk_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->where('module_raid_entries.status !=', 'closed')
            ->whereIn('module_raid_entries.priority', ['high', 'critical'])
            ->orderBy('module_raid_entries.priority', 'DESC')
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
            'owners' => $this->ownerOptions(),
            'status_options' => ['open', 'in_review', 'closed'],
            'risk_scale_options' => ['low', 'medium', 'high'],
        ];
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
