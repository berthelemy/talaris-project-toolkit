<?php

namespace App\Modules\DependenciesRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;

/**
 * Provides Dependencies Register dashboard widget data.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Project identifier.
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\DependenciesRegisterProject\Views\widget';
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
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'dependencies_register_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->whereIn('module_raid_entries.impact_level', ['high', 'medium'])
            ->orderBy('module_raid_entries.updated_at', 'DESC')
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
