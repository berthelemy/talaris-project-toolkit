<?php

namespace App\Libraries\Modules;

/**
 * Reads module metadata from module.json files under app/Modules.
 */
class ModuleMetadataReader
{
    /**
     * @return array{slug?: string, version?: string, dependencies?: list<string>, widget_permission?: string, widget_config?: array<string, mixed>}|null
     */
    public function read(string $moduleDirectory): ?array
    {
        $path = APPPATH . 'Modules/' . $moduleDirectory . '/module.json';

        if (! is_file($path)) {
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $dependencies = [];
        if (isset($decoded['dependencies']) && is_array($decoded['dependencies'])) {
            foreach ($decoded['dependencies'] as $dependency) {
                if (is_string($dependency) && $dependency !== '') {
                    $dependencies[] = $dependency;
                }
            }
        }

        /** @var array<string, mixed> $widgetConfig */
        $widgetConfig = is_array($decoded['widget_config'] ?? null) ? $decoded['widget_config'] : [];

        return [
            'slug' => is_string($decoded['slug'] ?? null) ? $decoded['slug'] : null,
            'version' => is_string($decoded['version'] ?? null) ? $decoded['version'] : null,
            'dependencies' => $dependencies,
            'widget_permission' => is_string($decoded['widget_permission'] ?? null) ? $decoded['widget_permission'] : null,
            'widget_config' => $widgetConfig,
        ];
    }
}
