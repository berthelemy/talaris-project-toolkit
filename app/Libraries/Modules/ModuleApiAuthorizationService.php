<?php

/**
 * File documentation for app/Libraries/Modules/ModuleApiAuthorizationService.php.
 */

namespace App\Libraries\Modules;

use App\Libraries\Auth\RbacService;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;

class ModuleApiAuthorizationService
{
    public function canRead(int $userId, string $scopeType, int $scopeId): bool
    {
        return $this->hasScopePermission($userId, $scopeType, $scopeId, false);
    }

    public function canWrite(int $userId, string $scopeType, int $scopeId): bool
    {
        return $this->hasScopePermission($userId, $scopeType, $scopeId, true);
    }

    private function hasScopePermission(int $userId, string $scopeType, int $scopeId, bool $forWrite): bool
    {
        if ($scopeType === 'project') {
            $project = (new ProjectModel())->find($scopeId);
            if (! is_array($project)) {
                return false;
            }

            if ((int) ($project['owner_user_id'] ?? 0) === $userId) {
                return true;
            }

            $rbac = new RbacService();
            if ($forWrite) {
                return $rbac->hasPermission($userId, 'project.update_own', 'project', $scopeId)
                    || $rbac->hasPermission($userId, 'project.content.update', 'project', $scopeId)
                    || $rbac->hasPermission($userId, 'system.users.impersonate', 'system', null);
            }

            return $rbac->hasPermission($userId, 'project.read_own', 'project', $scopeId)
                || $rbac->hasPermission($userId, 'project.read', 'project', $scopeId)
                || $rbac->hasPermission($userId, 'project.update_own', 'project', $scopeId)
                || $rbac->hasPermission($userId, 'system.users.impersonate', 'system', null);
        }

        if ($scopeType === 'programme') {
            $programme = (new ProgrammeModel())->find($scopeId);
            if (! is_array($programme)) {
                return false;
            }

            if ((int) ($programme['owner_user_id'] ?? 0) === $userId) {
                return true;
            }

            $rbac = new RbacService();
            if ($forWrite) {
                return $rbac->hasPermission($userId, 'programme.update_own', 'programme', $scopeId)
                    || $rbac->hasPermission($userId, 'system.users.impersonate', 'system', null);
            }

            return $rbac->hasPermission($userId, 'programme.read_own', 'programme', $scopeId)
                || $rbac->hasPermission($userId, 'programme.update_own', 'programme', $scopeId)
                || $rbac->hasPermission($userId, 'system.users.impersonate', 'system', null);
        }

        return false;
    }
}
