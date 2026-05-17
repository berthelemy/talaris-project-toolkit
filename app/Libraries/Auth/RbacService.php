<?php

/**
 * File documentation for app/Libraries/Auth/RbacService.php.
 */

namespace App\Libraries\Auth;

use App\Models\RoleModel;
use App\Models\UserRoleAssignmentModel;
use Config\Roles;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Role-based access control orchestration for scoped assignments and permissions.
 */
class RbacService
{
    /**
     * @var list<string>
     */
    private array $allowedScopes = ['system', 'programme', 'project'];

    /**
    * Assign a role to a user within system, programme, or project scope.
     *
    * @param int $userId Target user identifier.
    * @param string $roleSlug Role slug from the roles catalog.
    * @param string $scopeType Scope type: system, programme, or project.
    * @param ?int $scopeId Scope identifier, null only for system scope.
    * @param ?int $actorUserId Actor performing the assignment for audit logging.
     */
    public function assignRoleToUser(int $userId, string $roleSlug, string $scopeType, ?int $scopeId, ?int $actorUserId = null): void
    {
        $this->assertScope($scopeType, $scopeId);

        $role = (new RoleModel())->where('slug', $roleSlug)->first();

        if ($role === null) {
            throw new RuntimeException('Role not found: ' . $roleSlug);
        }

        $assignments = new UserRoleAssignmentModel();
        $query = $assignments
            ->where('user_id', $userId)
            ->where('role_id', (int) $role['id'])
            ->where('scope_type', $scopeType);

        if ($scopeId === null) {
            $query->where('scope_id', null);
        } else {
            $query->where('scope_id', $scopeId);
        }

        if ($query->first() !== null) {
            return;
        }

        $assignments->insert([
            'user_id' => $userId,
            'role_id' => (int) $role['id'],
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);

        (new AuditLogger())->log('role_assigned', 'success', $actorUserId, [
            'target_user_id' => $userId,
            'role_slug' => $roleSlug,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);
    }

    /**
    * Revoke a previously assigned role from a user in the given scope.
     *
    * @param int $userId Target user identifier.
    * @param string $roleSlug Role slug from the roles catalog.
    * @param string $scopeType Scope type: system, programme, or project.
    * @param ?int $scopeId Scope identifier, null only for system scope.
    * @param ?int $actorUserId Actor performing the revoke for audit logging.
    * @return bool True when an assignment was found and revoked.
     */
    public function revokeRoleFromUser(int $userId, string $roleSlug, string $scopeType, ?int $scopeId, ?int $actorUserId = null): bool
    {
        $this->assertScope($scopeType, $scopeId);

        $role = (new RoleModel())->where('slug', $roleSlug)->first();

        if ($role === null) {
            throw new RuntimeException('Role not found: ' . $roleSlug);
        }

        $assignments = new UserRoleAssignmentModel();
        $query = $assignments
            ->where('user_id', $userId)
            ->where('role_id', (int) $role['id'])
            ->where('scope_type', $scopeType);

        if ($scopeId === null) {
            $query->where('scope_id', null);
        } else {
            $query->where('scope_id', $scopeId);
        }

        $assignment = $query->first();

        if ($assignment === null) {
            return false;
        }

        $assignments->delete((int) $assignment['id']);

        (new AuditLogger())->log('role_revoked', 'success', $actorUserId, [
            'target_user_id' => $userId,
            'role_slug' => $roleSlug,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);

        return true;
    }

    /**
    * Check whether a user has an effective permission in a scope.
     *
    * @param int $userId User identifier.
    * @param string $permission Permission key to evaluate.
    * @param string $scopeType Scope type: system, programme, or project.
    * @param ?int $scopeId Scope identifier, null only for system scope.
    * @return bool True when the permission is present in effective permission set.
     */
    public function hasPermission(int $userId, string $permission, string $scopeType, ?int $scopeId): bool
    {
        $permissions = $this->permissionsForUser($userId, $scopeType, $scopeId);

        return in_array($permission, $permissions, true);
    }

    /**
        * Return effective role slugs for a user in the requested scope.
        *
        * System roles are inherited into non-system scopes.
        *
        * @param int $userId User identifier.
        * @param string $scopeType Scope type: system, programme, or project.
        * @param ?int $scopeId Scope identifier, null only for system scope.
     * @return list<string>
     */
    public function roleSlugsForUser(int $userId, string $scopeType, ?int $scopeId): array
    {
        $this->assertScope($scopeType, $scopeId);

        $builder = (new UserRoleAssignmentModel())
            ->select('roles.slug')
            ->join('roles', 'roles.id = user_role_assignments.role_id')
            ->where('user_role_assignments.user_id', $userId)
            ->groupStart();

        if ($scopeType !== 'system') {
            $builder->groupStart()
                ->where('user_role_assignments.scope_type', 'system')
                ->where('user_role_assignments.scope_id', null)
                ->groupEnd()
                ->orGroupStart()
                ->where('user_role_assignments.scope_type', $scopeType)
                ->where('user_role_assignments.scope_id', $scopeId)
                ->groupEnd();
        } else {
            $builder->where('user_role_assignments.scope_type', 'system')
                ->where('user_role_assignments.scope_id', null);
        }

        $rows = $builder->groupEnd()->findAll();

        $slugs = array_map(static fn (array $row): string => (string) $row['slug'], $rows);

        return array_values(array_unique($slugs));
    }

    /**
        * Resolve effective permissions by combining assigned roles and role metadata.
        *
        * @param int $userId User identifier.
        * @param string $scopeType Scope type: system, programme, or project.
        * @param ?int $scopeId Scope identifier, null only for system scope.
     * @return list<string>
     */
    public function permissionsForUser(int $userId, string $scopeType, ?int $scopeId): array
    {
        $roleSlugs = $this->roleSlugsForUser($userId, $scopeType, $scopeId);
        $permissions = [];
        $rolesConfig = config(Roles::class);
        $roleModel = new RoleModel();

        foreach ($roleSlugs as $slug) {
            $role = $roleModel->where('slug', $slug)->first();

            if ($role === null) {
                continue;
            }

            $customPermissions = $this->decodePermissions((string) ($role['permissions_json'] ?? ''));

            if ($customPermissions !== []) {
                $permissions = array_merge($permissions, $customPermissions);

                continue;
            }

            $permissions = array_merge($permissions, $rolesConfig->predefinedPermissions[$slug] ?? []);
        }

        return array_values(array_unique($permissions));
    }

    /**
        * List role assignments for user-management UI rendering.
        *
        * @param int $userId User identifier.
     * @return list<array{id:int, role_slug:string, role_name:string, scope_type:string, scope_id:int|null}>
     */
    public function roleAssignmentsForUser(int $userId): array
    {
        $rows = (new UserRoleAssignmentModel())
            ->select('user_role_assignments.id, user_role_assignments.scope_type, user_role_assignments.scope_id, roles.slug, roles.name')
            ->join('roles', 'roles.id = user_role_assignments.role_id')
            ->where('user_role_assignments.user_id', $userId)
            ->orderBy('roles.name', 'ASC')
            ->findAll();

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'],
            'role_slug' => (string) $row['slug'],
            'role_name' => (string) $row['name'],
            'scope_type' => (string) $row['scope_type'],
            'scope_id' => $row['scope_id'] === null ? null : (int) $row['scope_id'],
        ], $rows);
    }

    private function assertScope(string $scopeType, ?int $scopeId): void
    {
        if (! in_array($scopeType, $this->allowedScopes, true)) {
            throw new InvalidArgumentException('Unsupported scope type: ' . $scopeType);
        }

        if ($scopeType === 'system' && $scopeId !== null) {
            throw new InvalidArgumentException('System scope must not include scope ID.');
        }

        if ($scopeType !== 'system' && ($scopeId === null || $scopeId <= 0)) {
            throw new InvalidArgumentException('Non-system scopes require a positive scope ID.');
        }
    }

    /**
     * @return list<string>
     */
    private function decodePermissions(string $permissionsJson): array
    {
        if ($permissionsJson === '') {
            return [];
        }

        try {
            $decoded = json_decode($permissionsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, static fn ($item): bool => is_string($item) && $item !== ''));
    }
}
