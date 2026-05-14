<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Libraries\Modules\ModuleWidgetLayoutService;
use App\Libraries\Modules\ModuleWidgetService;
use App\Models\ProgrammeModel;
use App\Models\ProgrammeProjectModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Handle programme lifecycle, ownership actions, and project link management.
 */
class ProgrammeController extends BaseController
{
    /**
     * Show programme listing with create permission state for the current actor.
     *
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $programmes = (new ProgrammeModel())->orderBy('name', 'ASC')->findAll();
        $programmeIds = array_values(array_filter(array_map(
            static fn (array $programme): int => (int) ($programme['id'] ?? 0),
            $programmes,
        )));

        $statusesByProgramme = [];
        if ($programmeIds !== []) {
            $rows = (new ProgrammeProjectModel())
                ->select('programme_projects.programme_id, projects.status')
                ->join('projects', 'projects.id = programme_projects.project_id')
                ->whereIn('programme_projects.programme_id', $programmeIds)
                ->findAll();

            foreach ($rows as $row) {
                $programmeId = (int) ($row['programme_id'] ?? 0);
                $statusesByProgramme[$programmeId] ??= [];
                $statusesByProgramme[$programmeId][] = (string) ($row['status'] ?? 'not_started');
            }
        }

        foreach ($programmes as $index => $programme) {
            $programmeId = (int) ($programme['id'] ?? 0);
            $programmes[$index]['calculated_status'] = $this->computeProgrammeStatusFromStatuses(
                $statusesByProgramme[$programmeId] ?? [],
            );
        }

        return view('programmes/index', [
            'programmes'     => $programmes,
            'canCreate'      => $this->canCreateProgramme($actorId),
        ]);
    }

    /**
        * Create a new programme after validation and owner resolution.
     *
     * @return RedirectResponse
     */
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

    /**
        * Display a single programme with linked projects and enabled module widgets.
     *
        * @param int $programmeId Programme identifier.
     * @return string|RedirectResponse
     */
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
            ->select('projects.id, projects.name, projects.description, projects.status, projects.created_at')
            ->join('projects', 'projects.id = programme_projects.project_id')
            ->where('programme_projects.programme_id', $programmeId)
            ->orderBy('projects.name', 'ASC')
            ->findAll();

        $programmeStatus = $this->computeProgrammeStatus($linkedProjects);

        $widgetService = new ModuleWidgetService();
        $widgets = $widgetService->renderWidgets('programme', $programmeId);
        $canManageWidgetLayout = $this->canManageProgrammeWidgetLayout($actorId, $programme);
        $widgetLayoutOptions = $this->buildProgrammeWidgetLayoutOptions($programmeId, $actorId, $widgetService);

        return view('programmes/show', [
            'programme' => $programme,
            'programmeStatus' => $programmeStatus,
            'linkedProjects' => $linkedProjects,
            'widgets' => $widgets,
            'canManageWidgetLayout' => $canManageWidgetLayout,
            'widgetLayoutOptions' => $widgetLayoutOptions,
            'canOpenHelloModule' => (new ModuleRegistryService())
                ->isEnabled(ModuleRegistryService::HELLO_WORLD_PROGRAMME, 'programme'),
        ]);
    }

    /**
        * Update editable programme fields and write an audit event.
     *
        * @param int $programmeId Programme identifier.
     * @return RedirectResponse
     */
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

    /**
        * Delete a programme when actor has management access.
     *
        * @param int $programmeId Programme identifier.
     * @return RedirectResponse
     */
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

    /**
        * Render programme edit screen for authorized actors.
     *
        * @param int $programmeId Programme identifier.
     * @return string|RedirectResponse
     */
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

    /**
        * Link an existing project to a programme.
     *
        * @param int $programmeId Programme identifier.
        * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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

    /**
        * Remove a project-to-programme link.
     *
        * @param int $programmeId Programme identifier.
        * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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

    /**
        * Assign programme_manager role to a selected active user.
     *
        * @param int $programmeId Programme identifier.
     * @return RedirectResponse
     */
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

    /**
     * Display widget layout management page for a programme.
     *
     * @param int $programmeId Programme identifier.
     * @return string|RedirectResponse
     */
    public function editWidgetLayout(int $programmeId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if ($programme === null) {
            return redirect()->to('/programmes')->with('error', lang('Domain.programmeNotFound'));
        }

        if (! $this->canViewProgramme($actorId, $programme) || ! $this->canManageProgrammeWidgetLayout($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        $widgetService = new ModuleWidgetService();
        $widgetLayoutOptions = $this->buildProgrammeWidgetLayoutOptions($programmeId, $actorId, $widgetService);

        return view('programmes/widget_layout_edit', [
            'programme' => $programme,
            'widgetLayoutOptions' => $widgetLayoutOptions,
        ]);
    }

    /**
     * Update programme widget layout preferences.
     *
     * @param int $programmeId Programme identifier.
     * @return RedirectResponse
     */
    public function updateWidgetLayout(int $programmeId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null || $programme === null || ! $this->canManageProgrammeWidgetLayout($actorId, $programme)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $widgetService = new ModuleWidgetService();
        $registry = new ModuleRegistryService();
        $layoutService = new ModuleWidgetLayoutService();
        $modules = $registry->getEnabledModulesByType('programme');

        $visibilityInput = (array) $this->request->getPost('widget_visible');
        $orderInput = (array) $this->request->getPost('widget_order');
        $changes = [];

        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            if (! $widgetService->canAccessModuleForActor($actorId, $module, $programmeId)) {
                continue;
            }

            $isVisible = isset($visibilityInput[$slug]);
            $displayOrder = max(0, (int) ($orderInput[$slug] ?? (int) ($module['display_order'] ?? 0)));

            $layoutService->upsert('programme', $programmeId, $slug, $isVisible, $displayOrder, $actorId);
            $changes[] = [
                'module_slug' => $slug,
                'is_visible' => $isVisible,
                'display_order' => $displayOrder,
            ];
        }

        (new AuditLogger())->log('programme_widget_layout_updated', 'success', $actorId, [
            'programme_id' => $programmeId,
            'changes' => $changes,
        ]);

        return redirect()->to('/programmes/' . $programmeId)->with('success', lang('Module.programmeLayoutUpdatedSuccess'));
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
    private function canManageProgrammeWidgetLayout(int $actorId, array $programme): bool
    {
        if ($this->canManageProgramme($actorId, $programme)) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'programme.content.update', 'programme', (int) ($programme['id'] ?? 0))
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

    /**
     * @param list<array<string, mixed>> $linkedProjects
     */
    private function computeProgrammeStatus(array $linkedProjects): string
    {
        if ($linkedProjects === []) {
            return 'not_started';
        }

        $statuses = array_values(array_filter(array_map(
            static fn (array $project): string => (string) ($project['status'] ?? 'not_started'),
            $linkedProjects,
        )));

        if (count(array_diff($statuses, ['completed'])) === 0) {
            return 'completed';
        }


        return $this->computeProgrammeStatusFromStatuses($statuses);
    }

    /**
     * @param list<string> $statuses
     */
    private function computeProgrammeStatusFromStatuses(array $statuses): string
    {
        if ($statuses === []) {
            return 'not_started';
        }

        if (count(array_diff($statuses, ['completed'])) === 0) {
            return 'completed';
        }

        if (in_array('blocked', $statuses, true)) {
            return 'blocked';
        }

        if (in_array('at_risk', $statuses, true)) {
            return 'at_risk';
        }

        if (in_array('in_progress', $statuses, true) || in_array('on_track', $statuses, true)) {
            return 'in_progress';
        }

        if (count(array_diff($statuses, ['cancelled'])) === 0) {
            return 'cancelled';
        }

        if (in_array('on_hold', $statuses, true)) {
            return 'on_hold';
        }

        return 'not_started';
    }

    /**
     * @return list<array{slug: string, name: string, is_visible: bool, display_order: int}>
     */
    private function buildProgrammeWidgetLayoutOptions(int $programmeId, int $actorId, ModuleWidgetService $widgetService): array
    {
        $modules = (new ModuleRegistryService())->getEnabledModulesByType('programme');
        $layoutService = new ModuleWidgetLayoutService();
        $defaults = $layoutService->getDefaultByScope('programme');
        $scoped = $layoutService->getScoped('programme', $programmeId);

        $options = [];
        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '' || ! $widgetService->canAccessModuleForActor($actorId, $module, $programmeId)) {
                continue;
            }

            $layout = $layoutService->resolveForModule($module, $defaults, $scoped);
            $options[] = [
                'slug' => $slug,
                'name' => (string) ($module['name'] ?? $slug),
                'is_visible' => $layout['is_visible'],
                'display_order' => $layout['display_order'],
            ];
        }

        usort($options, static function (array $a, array $b): int {
            $orderCompare = ((int) $a['display_order']) <=> ((int) $b['display_order']);
            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return $options;
    }
}