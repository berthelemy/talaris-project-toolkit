<?php

namespace App\Libraries\Modules;

use App\Models\ModuleRegistryModel;

/**
 * Validates module dependency declarations from the registry.
 */
class ModuleDependencyResolver
{
    /**
     * @return array{ok: bool, missing: list<string>}
     */
    public function validateEnable(string $slug): array
    {
        $registry = new ModuleRegistryModel();
        $module = $registry->where('slug', $slug)->first();

        if (! is_array($module)) {
            return ['ok' => false, 'missing' => []];
        }

        $dependencies = $this->decodeDependencies((string) ($module['dependencies_json'] ?? ''));
        if ($dependencies === []) {
            return ['ok' => true, 'missing' => []];
        }

        $missing = [];

        foreach ($dependencies as $dependency) {
            $dependencyModule = $registry->where('slug', $dependency)->first();
            if (! is_array($dependencyModule) || ! (bool) ($dependencyModule['is_enabled'] ?? false)) {
                $missing[] = $dependency;
            }
        }

        return [
            'ok' => $missing === [],
            'missing' => $missing,
        ];
    }

    /**
     * @return list<string>
     */
    private function decodeDependencies(string $json): array
    {
        if ($json === '') {
            return [];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return [];
        }

        $dependencies = [];

        foreach ($decoded as $value) {
            if (is_string($value) && $value !== '') {
                $dependencies[] = $value;
            }
        }

        return $dependencies;
    }
}
