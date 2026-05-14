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
 * Handle project lifecycle, programme links, and project-scope widget pages.
 */
class ProjectController extends BaseController
{
    /**
     * Show project listing with create permission state for the current actor.
     *
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $programmeFilter = (string) $this->request->getGet('programme_id');
        $programmes = (new ProgrammeModel())->orderBy('name', 'ASC')->findAll();

        $builder = (new ProjectModel())
            ->select('projects.id, projects.name, projects.description, projects.status, projects.created_at')
            ->join('programme_projects', 'programme_projects.project_id = projects.id', 'left')
            ->groupBy('projects.id');

        if ($programmeFilter === 'none') {
            $builder->having('COUNT(programme_projects.id) = 0');
        } elseif (ctype_digit($programmeFilter) && (int) $programmeFilter > 0) {
            $builder->where('programme_projects.programme_id', (int) $programmeFilter);
        } else {
            $programmeFilter = '';
        }

        $projects = $builder
            ->orderBy('projects.name', 'ASC')
            ->findAll();

        $projectIds = array_values(array_filter(array_map(
            static fn (array $project): int => (int) ($project['id'] ?? 0),
            $projects,
        )));

        $linkedProgrammesByProject = [];
        if ($projectIds !== []) {
            $links = (new ProgrammeProjectModel())
                ->select('programme_projects.project_id, programmes.id AS programme_id, programmes.name AS programme_name')
                ->join('programmes', 'programmes.id = programme_projects.programme_id')
                ->whereIn('programme_projects.project_id', $projectIds)
                ->orderBy('programmes.name', 'ASC')
                ->findAll();

            foreach ($links as $link) {
                $projectId = (int) ($link['project_id'] ?? 0);
                $linkedProgrammesByProject[$projectId] ??= [];
                $linkedProgrammesByProject[$projectId][] = [
                    'id' => (int) ($link['programme_id'] ?? 0),
                    'name' => (string) ($link['programme_name'] ?? ''),
                ];
            }
        }

        return view('projects/index', [
            'projects'  => $projects,
            'canCreate' => $this->canCreateProject($actorId),
            'programmes' => $programmes,
            'selectedProgrammeId' => $programmeFilter,
            'linkedProgrammesByProject' => $linkedProgrammesByProject,
        ]);
    }

    /**
        * Create a new project after validation and owner resolution.
     *
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null || ! $this->canCreateProject($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $rules = [
            'name' => 'required|max_length[150]',
            'description' => 'permit_empty|max_length[5000]',
            'status' => 'permit_empty|in_list[not_started,in_progress,on_track,at_risk,blocked,on_hold,completed,cancelled]',
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
            'status' => $this->normalizeProjectStatus((string) $this->request->getPost('status')),
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

    /**
        * Display a single project with linked programmes and enabled module widgets.
     *
        * @param int $projectId Project identifier.
     * @return string|RedirectResponse
     */
    public function show(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if ($project === null) {
            return redirect()->to('/projects')->with('error', lang('Domain.projectNotFound'));
        }

        if (! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        $linkedProgrammes = (new ProgrammeProjectModel())
            ->select('programmes.id, programmes.name, programmes.description, programmes.created_at')
            ->join('programmes', 'programmes.id = programme_projects.programme_id')
            ->where('programme_projects.project_id', $projectId)
            ->orderBy('programmes.name', 'ASC')
            ->findAll();

        $widgetService = new ModuleWidgetService();
        $widgets = $widgetService->renderWidgets('project', $projectId);
        $projectModules = $this->buildProjectModuleNavigation($projectId, $actorId, $widgetService);
        $canEditProject = $this->canManageProject($actorId, $project);
        $canManageWidgetLayout = $this->canManageProjectWidgetLayout($actorId, $project);
        $widgetLayoutOptions = $this->buildProjectWidgetLayoutOptions($projectId, $actorId, $widgetService);

        return view('projects/show', [
            'project' => $project,
            'linkedProgrammes' => $linkedProgrammes,
            'widgets' => $widgets,
            'projectModules' => $projectModules,
            'canEditProject' => $canEditProject,
            'canManageWidgetLayout' => $canManageWidgetLayout,
            'widgetLayoutOptions' => $widgetLayoutOptions,
            'canOpenHelloModule' => (new ModuleRegistryService())
                ->isEnabled(ModuleRegistryService::HELLO_WORLD_PROJECT, 'project'),
            'canOpenRiskModule' => (new ModuleRegistryService())
                ->isEnabled('risk_register_project', 'project'),
            'canOpenIssueModule' => (new ModuleRegistryService())
                ->isEnabled('issue_tracker_project', 'project'),
            'canOpenAssumptionsModule' => (new ModuleRegistryService())
                ->isEnabled('assumptions_register_project', 'project'),
            'canOpenDependenciesModule' => (new ModuleRegistryService())
                ->isEnabled('dependencies_register_project', 'project'),
            'canOpenDecisionsModule' => (new ModuleRegistryService())
                ->isEnabled('decisions_register_project', 'project'),
        ]);
    }

    /**
        * Update editable project fields and write an audit event.
     *
        * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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
            'status' => 'permit_empty|in_list[not_started,in_progress,on_track,at_risk,blocked,on_hold,completed,cancelled]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ProjectModel())->update($projectId, [
            'name' => trim((string) $this->request->getPost('name')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'status' => $this->normalizeProjectStatus((string) $this->request->getPost('status')),
        ]);

        (new AuditLogger())->log('project_updated', 'success', $actorId, [
            'project_id' => $projectId,
        ]);

        return redirect()->to('/projects')->with('success', lang('Domain.projectUpdatedSuccess'));
    }

    /**
        * Delete a project when actor has management access.
     *
        * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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

    /**
        * Render project edit screen with current programme link state.
     *
        * @param int $projectId Project identifier.
     * @return string|RedirectResponse
     */
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

    /**
        * Assign project_manager role to a selected active user.
     *
        * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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

    /**
     * Display widget layout management page for a project.
     *
     * @param int $projectId Project identifier.
     * @return string|RedirectResponse
     */
    public function editWidgetLayout(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if ($project === null) {
            return redirect()->to('/projects')->with('error', lang('Domain.projectNotFound'));
        }

        if (! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! $this->canManageProjectWidgetLayout($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        $widgetService = new ModuleWidgetService();
        $projectModules = $this->buildProjectModuleNavigation($projectId, $actorId, $widgetService);
        $widgetLayoutOptions = $this->buildProjectWidgetLayoutOptions($projectId, $actorId, $widgetService);

        return view('projects/widget_layout_edit', [
            'project' => $project,
            'projectModules' => $projectModules,
            'widgetLayoutOptions' => $widgetLayoutOptions,
        ]);
    }

    public function updateWidgetLayout(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null || $project === null || ! $this->canManageProjectWidgetLayout($actorId, $project)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $widgetService = new ModuleWidgetService();
        $registry = new ModuleRegistryService();
        $layoutService = new ModuleWidgetLayoutService();
        $modules = $registry->getEnabledModulesByType('project');

        $visibilityInput = (array) $this->request->getPost('widget_visible');
        $orderInput = (array) $this->request->getPost('widget_order');
        $changes = [];

        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            if (! $widgetService->canAccessModuleForActor($actorId, $module, $projectId)) {
                continue;
            }

            $isVisible = isset($visibilityInput[$slug]);
            $displayOrder = max(0, (int) ($orderInput[$slug] ?? (int) ($module['display_order'] ?? 0)));

            $layoutService->upsert('project', $projectId, $slug, $isVisible, $displayOrder, $actorId);
            $changes[] = [
                'module_slug' => $slug,
                'is_visible' => $isVisible,
                'display_order' => $displayOrder,
            ];
        }

        (new AuditLogger())->log('project_widget_layout_updated', 'success', $actorId, [
            'project_id' => $projectId,
            'changes' => $changes,
        ]);

        return redirect()->to('/projects/' . $projectId)->with('success', lang('Module.projectLayoutUpdatedSuccess'));
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

    /**
     * @param array<string, mixed> $project
     */
    private function canViewProject(int $actorId, array $project): bool
    {
        if ((int) ($project['owner_user_id'] ?? 0) === $actorId) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'project.read_own', 'project', (int) $project['id'])
            || $rbac->hasPermission($actorId, 'project.update_own', 'project', (int) $project['id'])
            || $rbac->hasPermission($actorId, 'project.delete_own', 'project', (int) $project['id'])
            || $this->isSystemAdministrator($actorId);
    }

    /**
     * @param array<string, mixed> $project
     */
    private function canManageProjectWidgetLayout(int $actorId, array $project): bool
    {
        if ($this->canManageProject($actorId, $project)) {
            return true;
        }

        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'project.content.update', 'project', (int) ($project['id'] ?? 0))
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

    private function normalizeProjectStatus(string $status): string
    {
        $value = trim($status);
        if ($value === '') {
            return 'not_started';
        }

        $allowed = [
            'not_started',
            'in_progress',
            'on_track',
            'at_risk',
            'blocked',
            'on_hold',
            'completed',
            'cancelled',
        ];

        return in_array($value, $allowed, true) ? $value : 'not_started';
    }

    /**
     * @return list<array{slug: string, name: string, url: string}>
     */
    private function buildProjectModuleNavigation(int $projectId, int $actorId, ModuleWidgetService $widgetService): array
    {
        $modules = (new ModuleRegistryService())->getEnabledModulesByType('project');

        $navigation = [];
        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $routeSegment = $this->moduleSlugToRouteSegment($slug, 'project');
            if ($routeSegment === '') {
                continue;
            }

            $displayOrder = (int) ($module['display_order'] ?? 0);

            if (! $widgetService->canAccessModuleForActor($actorId, $module, $projectId)) {
                continue;
            }

            $navigation[] = [
                'slug' => $slug,
                'name' => (string) ($module['name'] ?? $slug),
                'url' => site_url('projects/' . $projectId . '/modules/' . $routeSegment),
                'display_order' => $displayOrder,
            ];
        }

        usort($navigation, static function (array $a, array $b): int {
            $orderCompare = ((int) $a['display_order']) <=> ((int) $b['display_order']);

            if ($orderCompare !== 0) {
                return $orderCompare;
            }

            return strcasecmp((string) $a['name'], (string) $b['name']);
        });

        return array_values(array_map(static function (array $item): array {
            return [
                'slug' => (string) $item['slug'],
                'name' => (string) $item['name'],
                'url' => (string) $item['url'],
            ];
        }, $navigation));
    }

    /**
     * @return list<array{slug: string, name: string, is_visible: bool, display_order: int}>
     */
    private function buildProjectWidgetLayoutOptions(int $projectId, int $actorId, ModuleWidgetService $widgetService): array
    {
        $modules = (new ModuleRegistryService())->getEnabledModulesByType('project');
        $layoutService = new ModuleWidgetLayoutService();
        $defaults = $layoutService->getDefaultByScope('project');
        $scoped = $layoutService->getScoped('project', $projectId);

        $options = [];
        foreach ($modules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '' || ! $widgetService->canAccessModuleForActor($actorId, $module, $projectId)) {
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

    private function moduleSlugToRouteSegment(string $moduleSlug, string $scopeType): string
    {
        $suffix = '_' . $scopeType;

        if (! str_ends_with($moduleSlug, $suffix)) {
            return '';
        }

        $base = substr($moduleSlug, 0, -strlen($suffix));
        if ($base === false || $base === '') {
            return '';
        }

        return str_replace('_', '-', $base);
    }
}