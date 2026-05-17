<?php

/**
 * Module internal API dispatcher for cross-module integrations.
 */

namespace App\Libraries\Modules;

use App\Libraries\Auth\AuditLogger;
use App\Models\ModuleRegistryModel;

/**
 * Provides internal-only module API access for cross-module integrations.
 */
class ModuleInternalApiService
{
    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function read(string $moduleSlug, string $resource, array $query, int $actorId): array
    {
        if (! $this->supportsModule($moduleSlug)) {
            return ['ok' => false, 'error' => 'unsupported_module'];
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return ['ok' => false, 'error' => 'module_not_found'];
        }

        $scopeType = trim((string) ($query['scope_type'] ?? ''));
        $scopeId = (int) ($query['scope_id'] ?? 0);

        if (! (new ModuleApiAuthorizationService())->canRead($actorId, $scopeType, $scopeId)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $result = $this->providerFor($moduleSlug)->read($moduleSlug, $resource, [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ], $actorId);

        (new AuditLogger())->log('module_internal_api_read', $result['ok'] ?? false ? 'success' : 'failed', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(string $moduleSlug, string $resource, array $data, int $actorId): array
    {
        if (! $this->supportsModule($moduleSlug)) {
            return ['ok' => false, 'error' => 'unsupported_module'];
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return ['ok' => false, 'error' => 'module_not_found'];
        }

        $scopeType = trim((string) ($data['scope_type'] ?? ''));
        $scopeId = (int) ($data['scope_id'] ?? 0);

        if (! (new ModuleApiAuthorizationService())->canWrite($actorId, $scopeType, $scopeId)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $lockResult = (new ModuleLockService())->acquire($moduleSlug, $scopeType, $scopeId, $actorId);
        if (! ($lockResult['ok'] ?? false)) {
            return [
                'ok' => false,
                'error' => 'locked',
                'lock' => [
                    'locked_by_user_id' => (int) (($lockResult['lock']['locked_by_user_id'] ?? 0)),
                    'locked_by_username' => (string) (($lockResult['lock']['locked_by_username'] ?? '')),
                    'expires_at' => (string) (($lockResult['lock']['expires_at'] ?? '')),
                ],
            ];
        }

        $result = $this->providerFor($moduleSlug)->create($moduleSlug, $resource, [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'message' => (string) ($data['message'] ?? ''),
        ], $actorId);

        (new AuditLogger())->log('module_internal_api_write', $result['ok'] ?? false ? 'success' : 'failed', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'action' => 'create',
        ]);

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $moduleSlug, string $resource, int $id, array $data, int $actorId): array
    {
        if (! $this->supportsModule($moduleSlug)) {
            return ['ok' => false, 'error' => 'unsupported_module'];
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return ['ok' => false, 'error' => 'module_not_found'];
        }

        $scopeType = trim((string) ($data['scope_type'] ?? ''));
        $scopeId = (int) ($data['scope_id'] ?? 0);

        if (! (new ModuleApiAuthorizationService())->canWrite($actorId, $scopeType, $scopeId)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $lockResult = (new ModuleLockService())->acquire($moduleSlug, $scopeType, $scopeId, $actorId);
        if (! ($lockResult['ok'] ?? false)) {
            return [
                'ok' => false,
                'error' => 'locked',
                'lock' => [
                    'locked_by_user_id' => (int) (($lockResult['lock']['locked_by_user_id'] ?? 0)),
                    'locked_by_username' => (string) (($lockResult['lock']['locked_by_username'] ?? '')),
                    'expires_at' => (string) (($lockResult['lock']['expires_at'] ?? '')),
                ],
            ];
        }

        $result = $this->providerFor($moduleSlug)->update($moduleSlug, $resource, $id, [
            'message' => (string) ($data['message'] ?? ''),
            'last_updated_at' => (string) ($data['last_updated_at'] ?? ''),
        ], $actorId);

        (new AuditLogger())->log('module_internal_api_write', $result['ok'] ?? false ? 'success' : 'failed', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'action' => 'update',
            'target_id' => $id,
        ]);

        return $result;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function moduleBySlug(string $slug): ?array
    {
        $module = (new ModuleRegistryModel())->where('slug', $slug)->first();

        return is_array($module) ? $module : null;
    }

    private function providerFor(string $moduleSlug): ModuleApiInterface
    {
        return new HelloWorldModuleApi();
    }

    private function supportsModule(string $moduleSlug): bool
    {
        return in_array($moduleSlug, [
            ModuleRegistryService::HELLO_WORLD_PROJECT,
            ModuleRegistryService::HELLO_WORLD_PROGRAMME,
        ], true);
    }
}
