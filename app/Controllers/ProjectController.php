<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Models\ProgrammeModel;
use App\Models\ProgrammeProjectModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProjectController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $projects = (new ProjectModel())->orderBy('name', 'ASC')->findAll();

        return view('projects/index', [
            'projects'  => $projects,
            'canCreate' => $this->canCreateProject($actorId),
        ]);
    }

    public function create(): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canCreateProject($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $rules = [
            'name' => 'required|max_length[150]',
            'description' => 'permit_empty|max_length[5000]',
            'owner_user_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ownerId = $this->resolveOwnerId($actorId, $this->request->getPost('owner_user_id'));

        if (! $this->isActiveUser($ownerId)) {
            return redirect()->back()->withInput()->with('error', lang('Domain.ownerInvalid'));
        }

        $projects = new ProjectModel();
        $projectId = $projects->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'owner_user_id' => $ownerId,
        ], true);

        if (! is_int($projectId)) {
            return redirect()->back()->withInput()->with('error', lang('Domain.projectCreateFailed'));
        }

        (new AuditLogger())->log('project_created', 'success', $actorId, [
            'project_id' => $projectId,
            'owner_user_id' => $ownerId,
        ]);

        return redirect()->to('/projects')->with('success', lang('Domain.projectCreatedSuccess'));
    }

    public function update(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null || $project === null || ! $this->canManageProject($actorId, $project)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $rules = [
            'name' => 'required|max_length[150]',
            'description' => 'permit_empty|max_length[5000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ProjectModel())->update($projectId, [
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
        ]);

        (new AuditLogger())->log('project_updated', 'success', $actorId, [
            'project_id' => $projectId,
        ]);

        return redirect()->to('/projects')->with('success', lang('Domain.projectUpdatedSuccess'));
    }

    public function delete(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null || $project === null || ! $this->canManageProject($actorId, $project)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        (new ProjectModel())->delete($projectId);

        (new AuditLogger())->log('project_deleted', 'success', $actorId, [
            'project_id' => $projectId,
        ]);

        return redirect()->to('/projects')->with('success', lang('Domain.projectDeletedSuccess'));
    }

    public function edit(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null || $project === null || ! $this->canManageProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        $programmes = (new ProgrammeModel())->orderBy('name', 'ASC')->findAll();
        $linkedRows = (new ProgrammeProjectModel())
            ->select('programme_id')
            ->where('project_id', $projectId)
            ->findAll();
        $linkedProgrammeIds = array_values(array_map(
            static fn (array $row): int => (int) ($row['programme_id'] ?? 0),
            $linkedRows,
        ));

        return view('projects/edit', [
            'project' => $project,
            'programmes' => $programmes,
            'linkedProgrammeIds' => $linkedProgrammeIds,
        ]);
    }

    public function assignManager(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);
        $targetUserId = (int) $this->request->getPost('user_id');

        if ($actorId === null || $project === null || ! $this->canManageProject($actorId, $project)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        if (! $this->isActiveUser($targetUserId)) {
            return redirect()->back()->with('error', lang('Domain.managerInvalid'));
        }

        (new RbacService())->assignRoleToUser($targetUserId, 'project_manager', 'project', $projectId, $actorId);

        (new AuditLogger())->log('project_manager_assigned', 'success', $actorId, [
            'project_id' => $projectId,
            'target_user_id' => $targetUserId,
        ]);

        return redirect()->to('/dashboard')->with('success', lang('Domain.projectManagerAssignedSuccess'));
    }

    private function canCreateProject(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'project.create', 'system', null)
            || $this->isSystemAdministrator($actorId);
    }

    /**
     * @param array<string, mixed> $project
     */
    private function canManageProject(int $actorId, array $project): bool
    {
        if ((int) ($project['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'project.update_own', 'project', (int) $project['id'])
            || $rbac->hasPermission($actorId, 'project.delete_own', 'project', (int) $project['id'])
            || $this->isSystemAdministrator($actorId);
    }

    private function isSystemAdministrator(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null);
    }

    private function resolveOwnerId(int $actorId, mixed $ownerUserId): int
    {
        if (is_string($ownerUserId) && trim($ownerUserId) === '') {
            return $actorId;
        }

        return is_numeric($ownerUserId) ? (int) $ownerUserId : $actorId;
    }

    private function isActiveUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $user = (new UserModel())->find($userId);

        return $user !== null && (bool) ($user['is_active'] ?? false);
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
}