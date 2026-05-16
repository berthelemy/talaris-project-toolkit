<?php

namespace App\Modules\DecisionsRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;

/**
 * Provides Decisions Register dashboard widget data.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Project identifier.
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\\Modules\\DecisionsRegisterProject\\Views\\widget';
    }

    /**
     * @param int $scopeId Project identifier.
     * @param array<string,mixed> $config Widget configuration.
     * @return array{entries:list<array<string,mixed>>,entry_count:int}
     */
    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = (int) ($config['max_entries'] ?? 5);
        if ($maxEntries <= 0) {
            $maxEntries = 5;
        }

        $entries = (new ModuleRaidEntryModel())
            ->select('module_raid_entries.*, made_by.username as made_by_username')
            ->join('users as made_by', 'made_by.id = module_raid_entries.made_by_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'decisions_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->orderBy('module_raid_entries.decision_date', 'DESC')
            ->orderBy('module_raid_entries.id', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }

    /**
     * @return array{max_entries:int}
     */
    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }
}
