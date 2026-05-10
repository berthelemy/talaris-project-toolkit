<?php

namespace App\Modules\RiskRegisterProject\Widgets;

use App\Libraries\Modules\ModuleWidgetInterface;
use App\Models\ModuleHelloWorldEntryModel;

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

        $entries = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', 'risk_register_project')
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

    public function getDefaultConfig(): array
    {
        return ['max_entries' => 5];
    }
}
