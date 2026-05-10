<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Modules\HelloWorldModuleApi;
use App\Libraries\Modules\ModuleApiAuthorizationService;
use App\Libraries\Modules\ModuleLockService;
use App\Models\ModuleRegistryModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class ModuleApiController extends BaseController
{
    public function read(string $moduleSlug, string $resource): ResponseInterface
    {
        $actorId = $this->sessionUserId();
        if ($actorId === null) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'unauthorized']);
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'module_not_found']);
        }

        $scopeType = (string) $this->request->getGet('scope_type');
        $scopeId = (int) $this->request->getGet('scope_id');

        if (! (new ModuleApiAuthorizationService())->canRead($actorId, $scopeType, $scopeId)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $result = (new HelloWorldModuleApi())->read($moduleSlug, $resource, [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ], $actorId);

        (new AuditLogger())->log('module_api_read', 'success', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);

        return $this->response->setJSON($result);
    }

    public function create(string $moduleSlug, string $resource): ResponseInterface
    {
        $actorId = $this->sessionUserId();
        if ($actorId === null) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'unauthorized']);
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'module_not_found']);
        }

        $scopeType = (string) $this->request->getPost('scope_type');
        $scopeId = (int) $this->request->getPost('scope_id');

        if (! (new ModuleApiAuthorizationService())->canWrite($actorId, $scopeType, $scopeId)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $lockResult = (new ModuleLockService())->acquire($moduleSlug, $scopeType, $scopeId, $actorId);
        if (! $lockResult['ok']) {
            return $this->lockDeniedResponse((array) ($lockResult['lock'] ?? []));
        }

        $result = (new HelloWorldModuleApi())->create($moduleSlug, $resource, [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'message' => (string) $this->request->getPost('message'),
        ], $actorId);

        (new AuditLogger())->log('module_api_write', $result['ok'] ? 'success' : 'failed', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'action' => 'create',
        ]);

        return $this->response->setStatusCode($result['ok'] ? 200 : 422)->setJSON($result);
    }

    public function update(string $moduleSlug, string $resource, int $id): ResponseInterface
    {
        $actorId = $this->sessionUserId();
        if ($actorId === null) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'unauthorized']);
        }

        $module = $this->moduleBySlug($moduleSlug);
        if (! is_array($module) || ! (bool) ($module['is_enabled'] ?? false)) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'module_not_found']);
        }

        $scopeType = $this->requestValue('scope_type');
        $scopeId = (int) $this->requestValue('scope_id');

        if (! (new ModuleApiAuthorizationService())->canWrite($actorId, $scopeType, $scopeId)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $lockResult = (new ModuleLockService())->acquire($moduleSlug, $scopeType, $scopeId, $actorId);
        if (! $lockResult['ok']) {
            return $this->lockDeniedResponse((array) ($lockResult['lock'] ?? []));
        }

        $result = (new HelloWorldModuleApi())->update($moduleSlug, $resource, $id, [
            'message' => $this->requestValue('message'),
            'last_updated_at' => $this->requestValue('last_updated_at'),
        ], $actorId);

        $status = 200;
        if (! $result['ok']) {
            $status = ($result['error'] ?? '') === 'conflict' ? 409 : 422;
        }

        (new AuditLogger())->log('module_api_write', $result['ok'] ? 'success' : 'failed', $actorId, [
            'module_slug' => $moduleSlug,
            'resource' => $resource,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'action' => 'update',
            'target_id' => $id,
        ]);

        return $this->response->setStatusCode($status)->setJSON($result);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function moduleBySlug(string $slug): ?array
    {
        $module = (new ModuleRegistryModel())->where('slug', $slug)->first();

        return is_array($module) ? $module : null;
    }

    private function sessionUserId(): ?int
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        $user = (new UserModel())->find((int) $userId);

        if (! is_array($user)) {
            return null;
        }

        return (int) $userId;
    }

    private function requestValue(string $key): string
    {
        $postValue = $this->request->getPost($key);
        if ($postValue !== null) {
            return trim((string) $postValue);
        }

        $varValue = $this->request->getVar($key);
        if ($varValue !== null) {
            return trim((string) $varValue);
        }

        $rawInput = $this->request->getRawInput();
        if (is_array($rawInput) && array_key_exists($key, $rawInput)) {
            return trim((string) $rawInput[$key]);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $lock
     */
    private function lockDeniedResponse(array $lock): ResponseInterface
    {
        return $this->response->setStatusCode(423)->setJSON([
            'ok' => false,
            'error' => 'locked',
            'lock' => [
                'locked_by_user_id' => (int) ($lock['locked_by_user_id'] ?? 0),
                'locked_by_username' => (string) ($lock['locked_by_username'] ?? ''),
                'expires_at' => (string) ($lock['expires_at'] ?? ''),
            ],
        ]);
    }
}
