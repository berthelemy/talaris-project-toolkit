<?php

namespace App\Libraries\Modules;

use App\Libraries\Auth\AuditLogger;
use App\Models\ModuleRegistryModel;

/**
 * Manage module registry lookups and enablement lifecycle transitions.
 */
class ModuleRegistryService
{
    public const HELLO_WORLD_PROGRAMME = 'hello_world_programme';
    public const HELLO_WORLD_PROJECT = 'hello_world_project';

    /**
    * Fetch all registered modules ordered by scope and display name.
     *
    * @return list<array<string, mixed>> Registered module rows.
     */
    public function allModules(): array
    {
        return (new ModuleRegistryModel())
            ->orderBy('scope_type', 'ASC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
    * Fetch enabled modules for a specific scope type.
     *
    * @param string $scopeType Either 'programme' or 'project'.
    * @return list<array<string, mixed>> Enabled module rows.
     */
    public function getEnabledModulesByType(string $scopeType): array
    {
        return (new ModuleRegistryModel())
            ->where('scope_type', $scopeType)
            ->where('is_enabled', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
    * Determine whether a specific module is enabled for a scope.
     *
     * @param string $slug Registered module slug.
     * @param string $scopeType Either 'programme' or 'project'.
     * @return bool True when module exists and is enabled for the scope.
     */
    public function isEnabled(string $slug, string $scopeType): bool
    {
        $module = (new ModuleRegistryModel())
            ->where('slug', $slug)
            ->where('scope_type', $scopeType)
            ->first();

        if (! is_array($module)) {
            return false;
        }

        return (bool) ($module['is_enabled'] ?? false);
    }

    /**
    * Change a module enabled state and emit audit evidence when it changes.
     *
     * @param string $slug Registered module slug.
     * @param bool $enabled Desired enabled state.
     * @param int $actorId Authenticated actor performing the change.
    * @return array{ok: bool, message_key: string} Outcome and language key for user feedback.
     */
    public function setEnabled(string $slug, bool $enabled, int $actorId): array
    {
        $model = new ModuleRegistryModel();
        $module = $model->where('slug', $slug)->first();

        if (! is_array($module)) {
            return ['ok' => false, 'message_key' => 'Module.notFound'];
        }

        $current = (bool) ($module['is_enabled'] ?? false);

        if ($current === $enabled) {
            return [
                'ok' => true,
                'message_key' => $enabled ? 'Module.alreadyEnabled' : 'Module.alreadyDisabled',
            ];
        }

        $model->update((int) $module['id'], [
            'is_enabled' => $enabled ? 1 : 0,
        ]);

        (new AuditLogger())->log($enabled ? 'module_enabled' : 'module_disabled', 'success', $actorId, [
            'module_slug' => (string) $module['slug'],
            'scope_type' => (string) $module['scope_type'],
        ]);

        return [
            'ok' => true,
            'message_key' => $enabled ? 'Module.enabledSuccess' : 'Module.disabledSuccess',
        ];
    }
}
