<?php

namespace App\Modules\RiskRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;
use App\Models\UserModel;

class ModuleWidget implements ModuleWidgetInterface
{
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\RiskRegisterProject\Views\widget';
    }

    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = (int) ($config['max_entries'] ?? 5);
        if ($maxEntries <= 0) {
            $maxEntries = 5;
        }

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
            'overview_counts' => $overviewCounts,
            'entries' => $entries,
            'entry_count' => count($entries),
            'owners' => $this->ownerOptions(),
            'status_options' => ['open', 'in_review', 'closed'],
            'risk_scale_options' => ['low', 'medium', 'high'],
        ];
    }

    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
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
