<?php

namespace App\Modules\HelloWorldProgramme\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;

class ModuleWidget implements ModuleWidgetInterface
{
    public function getWidgetView(int $scopeId): ?string
    {
        return 'App\Modules\HelloWorldProgramme\Views\widget';
    }

    public function getWidgetData(int $scopeId): array
    {
        $entries = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)
            ->where('scope_type', 'programme')
            ->where('scope_id', $scopeId)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->findAll();

        return [
            'entries' => $entries,
            'entry_count' => count($entries),
        ];
    }
}
