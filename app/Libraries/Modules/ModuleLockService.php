<?php

/**
 * File documentation for app/Libraries/Modules/ModuleLockService.php.
 */

namespace App\Libraries\Modules;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\AuthSettingsService;
use App\Models\ModuleEditLockModel;
use App\Models\UserModel;

class ModuleLockService
{
    /**
     * @return array{ok: bool, lock: array<string, mixed>|null}
     */
    public function acquire(string $moduleSlug, string $scopeType, int $scopeId, int $userId): array
    {
        $this->purgeExpired();

        $model = new ModuleEditLockModel();
        $existing = $this->findLock($moduleSlug, $scopeType, $scopeId);
        $now = date('Y-m-d H:i:s');
        $expiresAt = $this->expiresAt();

        if (! is_array($existing)) {
            $model->insert([
                'module_slug' => $moduleSlug,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
                'locked_by_user_id' => $userId,
                'acquired_at' => $now,
                'expires_at' => $expiresAt,
            ]);

            $lock = $this->findLock($moduleSlug, $scopeType, $scopeId);
            $this->auditAcquire('success', $userId, $moduleSlug, $scopeType, $scopeId, $lock);

            return ['ok' => true, 'lock' => $lock];
        }

        $ownerId = (int) ($existing['locked_by_user_id'] ?? 0);
        if ($ownerId === $userId) {
            $model->update((int) $existing['id'], [
                'acquired_at' => $now,
                'expires_at' => $expiresAt,
            ]);

            return ['ok' => true, 'lock' => $this->findLock($moduleSlug, $scopeType, $scopeId)];
        }

        $this->auditDenied($userId, $moduleSlug, $scopeType, $scopeId, $existing);

        return ['ok' => false, 'lock' => $existing];
    }

    public function isLockOwner(string $moduleSlug, string $scopeType, int $scopeId, int $userId): bool
    {
        $this->purgeExpired();
        $lock = $this->findLock($moduleSlug, $scopeType, $scopeId);

        if (! is_array($lock)) {
            return false;
        }

        return (int) ($lock['locked_by_user_id'] ?? 0) === $userId;
    }

    public function releaseAllForUser(int $userId, string $reason): int
    {
        $model = new ModuleEditLockModel();
        $locks = $model->where('locked_by_user_id', $userId)->findAll();

        if ($locks === []) {
            return 0;
        }

        $released = 0;
        foreach ($locks as $lock) {
            $lockId = (int) ($lock['id'] ?? 0);
            if ($lockId <= 0) {
                continue;
            }

            if ($model->delete($lockId)) {
                $released++;
                (new AuditLogger())->log('module_lock_released', 'success', $userId, [
                    'module_slug' => (string) ($lock['module_slug'] ?? ''),
                    'scope_type' => (string) ($lock['scope_type'] ?? ''),
                    'scope_id' => (int) ($lock['scope_id'] ?? 0),
                    'reason' => $reason,
                    'forced' => false,
                ]);
            }
        }

        return $released;
    }

    public function releaseByIdAsAdmin(int $lockId, int $actorId): bool
    {
        $model = new ModuleEditLockModel();
        $lock = $model->find($lockId);

        if (! is_array($lock)) {
            return false;
        }

        $deleted = (bool) $model->delete($lockId);

        if ($deleted) {
            (new AuditLogger())->log('module_lock_released', 'success', $actorId, [
                'module_slug' => (string) ($lock['module_slug'] ?? ''),
                'scope_type' => (string) ($lock['scope_type'] ?? ''),
                'scope_id' => (int) ($lock['scope_id'] ?? 0),
                'reason' => 'admin_recovery',
                'forced' => true,
                'previous_owner_user_id' => (int) ($lock['locked_by_user_id'] ?? 0),
            ]);
        }

        return $deleted;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function activeLocks(): array
    {
        $this->purgeExpired();

        $model = new ModuleEditLockModel();
        $locks = $model
            ->orderBy('acquired_at', 'DESC')
            ->findAll();

        if ($locks === []) {
            return [];
        }

        $users = new UserModel();

        foreach ($locks as &$lock) {
            $owner = $users->find((int) ($lock['locked_by_user_id'] ?? 0));
            $lock['locked_by_username'] = is_array($owner) ? (string) ($owner['username'] ?? '') : '';
        }
        unset($lock);

        return $locks;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findLock(string $moduleSlug, string $scopeType, int $scopeId): ?array
    {
        $lock = (new ModuleEditLockModel())
            ->where('module_slug', $moduleSlug)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->first();

        if (! is_array($lock)) {
            return null;
        }

        $owner = (new UserModel())->find((int) ($lock['locked_by_user_id'] ?? 0));
        $lock['locked_by_username'] = is_array($owner) ? (string) ($owner['username'] ?? '') : '';

        return $lock;
    }

    public function purgeExpired(): void
    {
        (new ModuleEditLockModel())
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->delete();
    }

    private function expiresAt(): string
    {
        $timeoutSeconds = (int) ((new AuthSettingsService())->get()['inactivity_timeout_seconds'] ?? 900);

        return date('Y-m-d H:i:s', time() + max(60, $timeoutSeconds));
    }

    /**
     * @param array<string, mixed>|null $lock
     */
    private function auditAcquire(string $status, int $userId, string $moduleSlug, string $scopeType, int $scopeId, ?array $lock): void
    {
        (new AuditLogger())->log('module_lock_acquired', $status, $userId, [
            'module_slug' => $moduleSlug,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'lock_id' => (int) ($lock['id'] ?? 0),
            'expires_at' => (string) ($lock['expires_at'] ?? ''),
        ]);
    }

    /**
     * @param array<string, mixed> $existing
     */
    private function auditDenied(int $userId, string $moduleSlug, string $scopeType, int $scopeId, array $existing): void
    {
        (new AuditLogger())->log('module_lock_denied', 'failed', $userId, [
            'module_slug' => $moduleSlug,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'locked_by_user_id' => (int) ($existing['locked_by_user_id'] ?? 0),
            'expires_at' => (string) ($existing['expires_at'] ?? ''),
        ]);
    }
}
