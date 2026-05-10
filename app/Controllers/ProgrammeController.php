<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Libraries\Modules\ModuleWidgetService;
use App\Models\ProgrammeModel;
use App\Models\ProgrammeProjectModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProgrammeController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $programmes = (new ProgrammeModel())->orderBy('name', 'ASC')->findAll();

        return view('programmes/index', [
            'programmes'     => $programmes,
            'canCreate'      => $this->canCreateProgramme($actorId),
        ]);
    }

    public function create(): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canCreateProgramme($actorId)) {
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

        $programmes = new ProgrammeModel();
        $programmeId = $programmes->insert([
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'owner_user_id' => $ownerId,
        ], true);

        if (! is_int($programmeId)) {
            return redirect()->back()->withInput()->with('error', lang('Domain.programmeCreateFailed'));
        }

        (new AuditLogger())->log('programme_created', 'success', $actorId, [
            'programme_id' => $programmeId,
            'owner_user_id' => $ownerId,
        ]);

        return redirect()->to('/programmes')->with('success', lang('Domain.programmeCreatedSuccess'));
    }

    public function show(int $programmeId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if ($programme === null) {
            return redirect()->to('/programmes')->with('error', lang('Domain.programmeNotFound'));
        }

        if (! $this->canViewProgramme($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        $linkedProjects = (new ProgrammeProjectModel())
            ->select('projects.id, projects.name, projects.description, projects.created_at')
            ->join('projects', 'projects.id = programme_projects.project_id')
            ->where('programme_projects.programme_id', $programmeId)
            ->orderBy('projects.name', 'ASC')
            ->findAll();

        $widgets = (new ModuleWidgetService())->renderWidgets('programme', $programmeId);

        return view('programmes/show', [
            'programme' => $programme,
            'linkedProjects' => $linkedProjects,
            'widgets' => $widgets,
            'canOpenHelloModule' => (new ModuleRegistryService())
                ->isEnabled(ModuleRegistryService::HELLO_WORLD_PROGRAMME, 'programme'),
        ]);
    }

    public function update(int $programmeId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null || $programme === null || ! $this->canManageProgramme($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $rules = [
            'name' => 'required|max_length[150]',
            'description' => 'permit_empty|max_length[5000]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ProgrammeModel())->update($programmeId, [
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
        ]);

        (new AuditLogger())->log('programme_updated', 'success', $actorId, [
            'programme_id' => $programmeId,
        ]);

        return redirect()->to('/programmes')->with('success', lang('Domain.programmeUpdatedSuccess'));
    }

    public function delete(int $programmeId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null || $programme === null || ! $this->canManageProgramme($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        (new ProgrammeModel())->delete($programmeId);

        (new AuditLogger())->log('programme_deleted', 'success', $actorId, [
            'programme_id' => $programmeId,
        ]);

        return redirect()->to('/programmes')->with('success', lang('Domain.programmeDeletedSuccess'));
    }

    public function edit(int $programmeId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null || $programme === null || ! $this->canManageProgramme($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        return view('programmes/edit', [
            'programme' => $programme,
        ]);
    }

    public function linkProject(int $programmeId, int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null || $programme === null || $project === null || ! $this->canAttachProject($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $links = new ProgrammeProjectModel();
        $existing = $links->where('programme_id', $programmeId)->where('project_id', $projectId)->first();

        if ($existing !== null) {
            return redirect()->to('/projects/' . $projectId . '/edit')->with('success', lang('Domain.projectAlreadyLinked'));
        }

        $links->insert([
            'programme_id' => $programmeId,
            'project_id' => $projectId,
            'linked_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('programme_project_linked', 'success', $actorId, [
            'programme_id' => $programmeId,
            'project_id' => $projectId,
        ]);

        return redirect()->to('/projects/' . $projectId . '/edit')->with('success', lang('Domain.projectLinkedSuccess'));
    }

    public function unlinkProject(int $programmeId, int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null || $programme === null || ! $this->canAttachProject($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        (new ProgrammeProjectModel())
            ->where('programme_id', $programmeId)
            ->where('project_id', $projectId)
            ->delete();

        (new AuditLogger())->log('programme_project_unlinked', 'success', $actorId, [
            'programme_id' => $programmeId,
            'project_id' => $projectId,
        ]);

        return redirect()->to('/projects/' . $projectId . '/edit')->with('success', lang('Domain.projectUnlinkedSuccess'));
    }

    public function assignManager(int $programmeId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);
        $targetUserId = (int) $this->request->getPost('user_id');

        if ($actorId === null || $programme === null || ! $this->canManageProgramme($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        if (! $this->isActiveUser($targetUserId)) {
            return redirect()->back()->with('error', lang('Domain.managerInvalid'));
        }

        (new RbacService())->assignRoleToUser($targetUserId, 'programme_manager', 'programme', $programmeId, $actorId);

        (new AuditLogger())->log('programme_manager_assigned', 'success', $actorId, [
            'programme_id' => $programmeId,
            'target_user_id' => $targetUserId,
        ]);

        return redirect()->to('/dashboard')->with('success', lang('Domain.programmeManagerAssignedSuccess'));
    }

    private function canCreateProgramme(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.create', 'system', null)
            || $this->isSystemAdministrator($actorId);
    }

    /**
     * @param array<string, mixed> $programme
     */
    private function canManageProgramme(int $actorId, array $programme): bool
    {
        if ((int) ($programme['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.update_own', 'programme', (int) $programme['id'])
            || $rbac->hasPermission($actorId, 'programme.delete_own', 'programme', (int) $programme['id'])
            || $this->isSystemAdministrator($actorId);
    }

    /**
     * @param array<string, mixed> $programme
     */
    private function canViewProgramme(int $actorId, array $programme): bool
    {
        if ((int) ($programme['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.read_own', 'programme', (int) $programme['id'])
            || $rbac->hasPermission($actorId, 'programme.update_own', 'programme', (int) $programme['id'])
            || $rbac->hasPermission($actorId, 'programme.delete_own', 'programme', (int) $programme['id'])
            || $this->isSystemAdministrator($actorId);
    }

    /**
     * @param array<string, mixed> $programme
     */
    private function canAttachProject(int $actorId, array $programme): bool
    {
        if ((int) ($programme['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.projects.attach', 'programme', (int) $programme['id'])
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