<?php

/**
 * HelloWorldProject module widget provider for dashboard cards and summary metrics.
 */

namespace App\Modules\HelloWorldProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Libraries\Modules\ModuleRegistryService;
use App\Modules\HelloWorldProject\Models\HelloWorldEntryModel;

/**
 * Project-scope dashboard widget for Hello World module entries.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Project identifier.
     * @return string|null
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\HelloWorldProject\Views\widget';
    }

    /**
     * @param int $scopeId Project identifier.
     * @return array{entries: list<array<string, mixed>>, entry_count: int}
     */
    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = (int) ($config['max_entries'] ?? 5);
        if ($maxEntries <= 0) {
            $maxEntries = 5;
        }

        $entries = (new HelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROJECT)
            ->where('scope_type', 'project')
            ->where('scope_id', $scopeId)
            ->orderBy('id', 'DESC')
            ->limit($maxEntries)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }

    /**
     * @return array{max_entries: int}
     */
    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }
}
