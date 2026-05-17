<?php

/**
 * File documentation for app/Modules/HelloWorldProgramme/Widgets/ModuleWidget.php.
 */

namespace App\Modules\HelloWorldProgramme\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Libraries\Modules\ModuleRegistryService;
use App\Modules\HelloWorldProgramme\Models\HelloWorldEntryModel;

/**
 * Programme-scope dashboard widget for Hello World module entries.
 */
class ModuleWidget implements ModuleWidgetInterface
{
    /**
     * @param int $scopeId Programme identifier.
     * @return string|null
     */
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\HelloWorldProgramme\Views\widget';
    }

    /**
     * @param int $scopeId Programme identifier.
     * @return array{entries: list<array<string, mixed>>, entry_count: int}
     */
    public function getWidgetData(int $scopeId, array $config = []): array
    {
        $maxEntries = (int) ($config['max_entries'] ?? 5);
        if ($maxEntries <= 0) {
            $maxEntries = 5;
        }

        $entries = (new HelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)
            ->where('scope_type', 'programme')
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
