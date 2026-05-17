<?php

/**
 * File documentation for app/Controllers/UserManagementController.php.
 */

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\PasswordPolicyService;
use App\Libraries\Auth\RbacService;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;
use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\UserRoleAssignmentModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Admin user lifecycle and scoped role assignment management.
 */
class UserManagementController extends BaseController
{
    /**
     * Render user administration index with filters and role labels.
     *
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageUsers($actorId)) {
            $this->logDenied($actorId, 'index');

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorized'));
        }

        $filters = [
            'username' => trim((string) $this->request->getGet('username')),
            'email' => trim((string) $this->request->getGet('email')),
            'status' => trim((string) $this->request->getGet('status')),
            'role' => trim((string) $this->request->getGet('role')),
        ];

        $users = $this->searchUsers($filters);
        $userIds = array_map(static fn (array $user): int => (int) $user['id'], $users);

        return view('users/index', [
            'users' => $users,
            'filters' => $filters,
            'rolesByUser' => $this->roleLabelsByUser($userIds),
            'availableRoles' => (new RoleModel())->orderBy('name', 'ASC')->findAll(),
        ]);
    }

    /**
        * Create a user account from admin form input.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canManageUsers($actorId)) {
            if ($actorId !== null) {
                $this->logDenied($actorId, 'create');
            }

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorized'));
        }

        $rules = [
            'username' => 'required|max_length[100]|is_unique[users.username]',
            'email' => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'password' => 'required',
            'is_active' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $password = (string) $this->request->getPost('password');
        $policyErrors = (new PasswordPolicyService())->validate($password);

        if ($policyErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $policyErrors);
        }

        $payload = [
            'username' => trim((string) $this->request->getPost('username')),
            'email' => trim((string) $this->request->getPost('email')),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_active' => $this->boolFromPost('is_active', true) ? 1 : 0,
        ];

        $model = new UserModel();
        $userId = $model->insert($payload, true);

        if (! is_int($userId)) {
            return redirect()->back()->withInput()->with('error', lang('UserAdmin.createFailed'));
        }

        (new AuditLogger())->log('user_admin_created', 'success', $actorId, [
            'target_user_id' => $userId,
            'after' => [
                'username' => $payload['username'],
                'email' => $payload['email'],
                'is_active' => (int) $payload['is_active'],
            ],
        ]);

        return redirect()->to('/users')->with('success', lang('UserAdmin.createdSuccess'));
    }

    /**
        * Render user edit screen with assignments and scope options.
     *
        * @param int $userId Target user identifier.
     * @return string|RedirectResponse
     */
    public function edit(int $userId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageUsers($actorId)) {
            $this->logDenied($actorId, 'edit');

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorized'));
        }

        $user = (new UserModel())->find($userId);

        if ($user === null) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.userNotFound'));
        }

        return view('users/edit', [
            'user' => $user,
            'availableRoles' => (new RoleModel())->orderBy('name', 'ASC')->findAll(),
            'assignments' => (new RbacService())->roleAssignmentsForUser($userId),
            'programmes' => (new ProgrammeModel())->orderBy('name', 'ASC')->findAll(),
            'projects' => (new ProjectModel())->orderBy('name', 'ASC')->findAll(),
            'canManageRoles' => $this->canManageRoles($actorId),
        ]);
    }

    /**
        * Update user profile fields and optional password reset.
     *
        * @param int $userId Target user identifier.
     * @return RedirectResponse
     */
    public function update(int $userId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canManageUsers($actorId)) {
            if ($actorId !== null) {
                $this->logDenied($actorId, 'update');
            }

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorized'));
        }

        $existing = (new UserModel())->find($userId);

        if ($existing === null) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.userNotFound'));
        }

        $rules = [
            'username' => 'required|max_length[100]|is_unique[users.username,id,' . $userId . ']',
            'email' => 'required|valid_email|max_length[255]|is_unique[users.email,id,' . $userId . ']',
            'language_preference' => 'permit_empty|in_list[en,fr]',
            'profile_description' => 'permit_empty|max_length[1000]',
            'is_active' => 'permit_empty|in_list[0,1]',
            'reset_password_to' => 'permit_empty',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'username' => trim((string) $this->request->getPost('username')),
            'email' => trim((string) $this->request->getPost('email')),
            'language_preference' => $this->nullableString((string) $this->request->getPost('language_preference')),
            'profile_description' => $this->nullableString((string) $this->request->getPost('profile_description')),
            'is_active' => $this->boolFromPost('is_active', false) ? 1 : 0,
        ];

        $newPassword = (string) $this->request->getPost('reset_password_to');

        if (trim($newPassword) !== '') {
            $policyErrors = (new PasswordPolicyService())->validate($newPassword);

            if ($policyErrors !== []) {
                return redirect()->back()->withInput()->with('errors', $policyErrors);
            }

            $payload['password_hash'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if ((int) $payload['is_active'] === 0 && $this->isLastActiveAdministrator($userId)) {
            return redirect()->back()->withInput()->with('error', lang('UserAdmin.lastAdminProtection'));
        }

        (new UserModel())->update($userId, $payload);

        (new AuditLogger())->log('user_admin_updated', 'success', $actorId, [
            'target_user_id' => $userId,
            'before' => [
                'username' => (string) $existing['username'],
                'email' => (string) $existing['email'],
                'language_preference' => $existing['language_preference'],
                'profile_description' => $existing['profile_description'],
                'is_active' => (int) ($existing['is_active'] ?? 0),
            ],
            'after' => [
                'username' => $payload['username'],
                'email' => $payload['email'],
                'language_preference' => $payload['language_preference'],
                'profile_description' => $payload['profile_description'],
                'is_active' => (int) $payload['is_active'],
                'password_reset' => array_key_exists('password_hash', $payload),
            ],
        ]);

        return redirect()->to('/users/' . $userId . '/edit')->with('success', lang('UserAdmin.updatedSuccess'));
    }

    /**
        * Deactivate user account with last-admin safety guard.
     *
        * @param int $userId Target user identifier.
     * @return RedirectResponse
     */
    public function deactivate(int $userId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canManageUsers($actorId)) {
            if ($actorId !== null) {
                $this->logDenied($actorId, 'deactivate');
            }

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorized'));
        }

        $user = (new UserModel())->find($userId);

        if ($user === null) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.userNotFound'));
        }

        if ($this->isLastActiveAdministrator($userId)) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.lastAdminProtection'));
        }

        (new UserModel())->update($userId, ['is_active' => 0]);

        (new AuditLogger())->log('user_admin_deactivated', 'success', $actorId, [
            'target_user_id' => $userId,
            'before' => ['is_active' => (int) ($user['is_active'] ?? 0)],
            'after' => ['is_active' => 0],
        ]);

        return redirect()->to('/users')->with('success', lang('UserAdmin.deactivatedSuccess'));
    }

    /**
        * Assign a scoped role to a user from admin workflow.
     *
        * @param int $userId Target user identifier.
     * @return RedirectResponse
     */
    public function assignRole(int $userId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canManageRoles($actorId)) {
            if ($actorId !== null) {
                $this->logDenied($actorId, 'assign_role');
            }

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorizedRoleManagement'));
        }

        $user = (new UserModel())->find($userId);

        if ($user === null) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.userNotFound'));
        }

        $roleSlug = trim((string) $this->request->getPost('role_slug'));
        $scopeType = trim((string) $this->request->getPost('scope_type'));

        if ($roleSlug === '' || ! in_array($scopeType, ['system', 'programme', 'project'], true)) {
            return redirect()->back()->with('error', lang('UserAdmin.invalidRoleScope'));
        }

        $scopeId = $this->resolveScopeIdFromPost($scopeType);

        if (! $this->isValidScope($scopeType, $scopeId)) {
            return redirect()->back()->with('error', lang('UserAdmin.invalidRoleScope'));
        }

        if ($roleSlug === 'administrator' && $scopeType !== 'system') {
            return redirect()->back()->with('error', lang('UserAdmin.invalidRoleScope'));
        }

        (new RbacService())->assignRoleToUser($userId, $roleSlug, $scopeType, $scopeId, $actorId);

        return redirect()->to('/users/' . $userId . '/edit')->with('success', lang('UserAdmin.roleAssignedSuccess'));
    }

    /**
        * Revoke a scoped role from a user from admin workflow.
     *
        * @param int $userId Target user identifier.
     * @return RedirectResponse
     */
    public function revokeRole(int $userId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canManageRoles($actorId)) {
            if ($actorId !== null) {
                $this->logDenied($actorId, 'revoke_role');
            }

            return redirect()->to('/dashboard')->with('error', lang('UserAdmin.notAuthorizedRoleManagement'));
        }

        $user = (new UserModel())->find($userId);

        if ($user === null) {
            return redirect()->to('/users')->with('error', lang('UserAdmin.userNotFound'));
        }

        $roleSlug = trim((string) $this->request->getPost('role_slug'));
        $scopeType = trim((string) $this->request->getPost('scope_type'));
        $scopeId = $this->resolveScopeIdFromPost($scopeType, false);

        if ($roleSlug === '' || ! in_array($scopeType, ['system', 'programme', 'project'], true)) {
            return redirect()->back()->with('error', lang('UserAdmin.invalidRoleScope'));
        }

        if (! $this->isValidScope($scopeType, $scopeId)) {
            return redirect()->back()->with('error', lang('UserAdmin.invalidRoleScope'));
        }

        if ($roleSlug === 'administrator' && $scopeType === 'system' && $this->isLastActiveAdministrator($userId)) {
            return redirect()->back()->with('error', lang('UserAdmin.lastAdminProtection'));
        }

        (new RbacService())->revokeRoleFromUser($userId, $roleSlug, $scopeType, $scopeId, $actorId);

        return redirect()->to('/users/' . $userId . '/edit')->with('success', lang('UserAdmin.roleRevokedSuccess'));
    }

    /**
     * @param array{username:string, email:string, status:string, role:string} $filters
     * @return list<array<string, mixed>>
     */
    private function searchUsers(array $filters): array
    {
        $builder = (new UserModel())
            ->select('users.id, users.username, users.email, users.is_active, users.created_at, users.last_login_at')
            ->orderBy('users.username', 'ASC');

        if ($filters['username'] !== '') {
            $builder->like('users.username', $filters['username']);
        }

        if ($filters['email'] !== '') {
            $builder->like('users.email', $filters['email']);
        }

        if ($filters['status'] === 'active') {
            $builder->where('users.is_active', 1);
        }

        if ($filters['status'] === 'inactive') {
            $builder->where('users.is_active', 0);
        }

        if ($filters['role'] !== '') {
            $roleUserIds = (new UserRoleAssignmentModel())
                ->select('user_role_assignments.user_id')
                ->join('roles', 'roles.id = user_role_assignments.role_id')
                ->where('roles.slug', $filters['role'])
                ->groupBy('user_role_assignments.user_id')
                ->findColumn('user_role_assignments.user_id');

            if ($roleUserIds === null || $roleUserIds === []) {
                return [];
            }

            $ids = array_map(static fn ($value): int => (int) $value, $roleUserIds);
            $builder->whereIn('users.id', $ids);
        }

        return $builder->findAll();
    }

    /**
     * @param list<int> $userIds
     * @return array<int, list<string>>
     */
    private function roleLabelsByUser(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        $rows = (new UserRoleAssignmentModel())
            ->select('user_role_assignments.user_id, roles.name, user_role_assignments.scope_type, user_role_assignments.scope_id')
            ->join('roles', 'roles.id = user_role_assignments.role_id')
            ->whereIn('user_role_assignments.user_id', $userIds)
            ->orderBy('roles.name', 'ASC')
            ->findAll();

        $grouped = [];

        foreach ($rows as $row) {
            $uid = (int) $row['user_id'];
            $grouped[$uid] ??= [];
            $grouped[$uid][] = (string) $row['name'] . ' (' . $this->scopeLabel((string) $row['scope_type'], $row['scope_id']) . ')';
        }

        return $grouped;
    }

    private function scopeLabel(string $scopeType, mixed $scopeId): string
    {
        if ($scopeType === 'system') {
            return 'system';
        }

        if ($scopeId === null) {
            return $scopeType;
        }

        return $scopeType . ' #' . (int) $scopeId;
    }

    private function canManageUsers(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'system.users.invite', 'system', null)
            || $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null);
    }

    private function canManageRoles(int $actorId): bool
    {
        return (new RbacService())->hasPermission($actorId, 'system.roles.manage', 'system', null);
    }

    private function isLastActiveAdministrator(int $targetUserId): bool
    {
        $rbac = new RbacService();
        $isTargetAdmin = $rbac->hasPermission($targetUserId, 'system.users.impersonate', 'system', null);

        if (! $isTargetAdmin) {
            return false;
        }

        $activeUsers = (new UserModel())
            ->select('id')
            ->where('is_active', 1)
            ->findAll();

        $activeAdminCount = 0;

        foreach ($activeUsers as $activeUser) {
            if ($rbac->hasPermission((int) $activeUser['id'], 'system.users.impersonate', 'system', null)) {
                $activeAdminCount++;
            }
        }

        return $activeAdminCount <= 1;
    }

    private function resolveScopeIdFromPost(string $scopeType, bool $fromEditableInputs = true): ?int
    {
        if ($scopeType === 'system') {
            return null;
        }

        if ($fromEditableInputs) {
            $field = $scopeType === 'programme' ? 'programme_scope_id' : 'project_scope_id';
            $raw = $this->request->getPost($field);

            return is_numeric($raw) ? (int) $raw : null;
        }

        $raw = $this->request->getPost('scope_id');

        return is_numeric($raw) ? (int) $raw : null;
    }

    private function isValidScope(string $scopeType, ?int $scopeId): bool
    {
        if ($scopeType === 'system') {
            return $scopeId === null;
        }

        if ($scopeId === null || $scopeId <= 0) {
            return false;
        }

        if ($scopeType === 'programme') {
            return (new ProgrammeModel())->find($scopeId) !== null;
        }

        if ($scopeType === 'project') {
            return (new ProjectModel())->find($scopeId) !== null;
        }

        return false;
    }

    private function boolFromPost(string $field, bool $default): bool
    {
        $raw = $this->request->getPost($field);

        if ($raw === null) {
            return $default;
        }

        if (is_bool($raw)) {
            return $raw;
        }

        $value = strtolower(trim((string) $raw));

        return in_array($value, ['1', 'true', 'on', 'yes'], true);
    }

    private function sessionUserId(): ?int
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        return (int) $userId;
    }

    private function nullableString(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function logDenied(int $actorId, string $action): void
    {
        (new AuditLogger())->log('user_admin_denied', 'failed', $actorId, [
            'action' => $action,
        ]);
    }
}
