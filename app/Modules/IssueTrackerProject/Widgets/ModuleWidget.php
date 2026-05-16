<?php

namespace App\Modules\IssueTrackerProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleRaidEntryModel;

/**
 * Provides Issue Tracker dashboard widget data.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Project identifier.
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\IssueTrackerProject\Views\widget';
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
            ->select('module_raid_entries.*, users.username as owner_username, reporter.username as reporter_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->join('users as reporter', 'reporter.id = module_raid_entries.reporter_user_id', 'left')
            ->where('module_raid_entries.module_slug', 'issue_tracker_project')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $scopeId)
            ->whereIn('module_raid_entries.status', ['open', 'in_review'])
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
