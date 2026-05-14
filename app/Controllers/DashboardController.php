<?php

namespace App\Controllers;

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ModuleRaidEntryModel;
use App\Models\ProgrammeModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * DashboardController component.
 */
class DashboardController extends BaseController
{
    /**
     * Index operation.
     *
     * @return string
     */
    public function index(): string
    {
        $userId = session('user_id');
        $isImpersonating = session('impersonator_user_id') !== null;
        $canImpersonate = false;
        $users = [];

        if ((is_int($userId) || ctype_digit((string) $userId)) && ! $isImpersonating) {
            $canImpersonate = (new RbacService())->hasPermission((int) $userId, 'system.users.impersonate', 'system', null);

            if ($canImpersonate) {
                $users = (new UserModel())
                    ->select('id, username, email, is_active')
                    ->where('is_active', 1)
                    ->where('id !=', (int) $userId)
                    ->orderBy('username', 'ASC')
                    ->findAll();
            }
        }

        return view('dashboard/index', [
            'username' => (string) session('username'),
            'canImpersonate' => $canImpersonate,
            'isImpersonating' => $isImpersonating,
            'impersonatorUsername' => (string) (session('impersonator_username') ?? ''),
            'impersonationCandidates' => $users,
        ]);
    }

    /**
     * Show project dashboard drill-down details with source links.
     *
     * @param int $projectId Project identifier.
     * @return string|RedirectResponse
     */
    public function projectDetails(int $projectId): string|RedirectResponse
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

        $registry = new ModuleRegistryService();
        $enabledModules = $registry->getEnabledModulesByType('project');
        $moduleNames = [];
        foreach ($enabledModules as $module) {
            $slug = (string) ($module['slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $moduleNames[$slug] = (string) ($module['name'] ?? $slug);
        }

        $filters = [
            'module' => trim((string) $this->request->getGet('module')),
            'status' => trim((string) $this->request->getGet('status')),
            'priority' => trim((string) $this->request->getGet('priority')),
            'q' => trim((string) $this->request->getGet('q')),
        ];

        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $db = db_connect();
        $query = $db->table('module_raid_entries')
            ->select('module_raid_entries.id, module_raid_entries.module_slug, module_raid_entries.title, module_raid_entries.description, module_raid_entries.status, module_raid_entries.priority, module_raid_entries.updated_at, users.username AS owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $projectId);

        if ($filters['module'] !== '') {
            $query->where('module_raid_entries.module_slug', $filters['module']);
        }

        if ($filters['status'] !== '') {
            $query->where('module_raid_entries.status', $filters['status']);
        }

        if ($filters['priority'] !== '') {
            $query->where('module_raid_entries.priority', $filters['priority']);
        }

        if ($filters['q'] !== '') {
            $query->groupStart()
                ->like('module_raid_entries.title', $filters['q'])
                ->orLike('module_raid_entries.description', $filters['q'])
                ->groupEnd();
        }

        $countQuery = clone $query;
        $totalRows = $countQuery->countAllResults();

        $rows = $query
            ->orderBy('module_raid_entries.updated_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $records = [];
        foreach ($rows as $row) {
            $slug = (string) ($row['module_slug'] ?? '');
            $segment = $this->moduleSlugToRouteSegment($slug, 'project');
            $sourceUrl = $segment === ''
                ? ''
                : site_url('projects/' . $projectId . '/modules/' . $segment . '#entry-' . (int) ($row['id'] ?? 0));

            $records[] = [
                'id' => (int) ($row['id'] ?? 0),
                'module_slug' => $slug,
                'module_name' => $moduleNames[$slug] ?? $slug,
                'title' => (string) ($row['title'] ?? ''),
                'owner_username' => (string) ($row['owner_username'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'priority' => (string) ($row['priority'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'source_url' => $sourceUrl,
            ];
        }

        return view('dashboard/details', [
            'scopeType' => 'project',
            'scopeId' => $projectId,
            'scopeName' => (string) ($project['name'] ?? ''),
            'records' => $records,
            'filters' => $filters,
            'moduleOptions' => $moduleNames,
            'pagination' => $this->buildPaginationData('projects/' . $projectId . '/dashboard/details', $filters, $page, $perPage, $totalRows),
        ]);
    }

    /**
     * Show programme dashboard drill-down details with source links.
     *
     * @param int $programmeId Programme identifier.
     * @return string|RedirectResponse
     */
    public function programmeDetails(int $programmeId): string|RedirectResponse
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

        $filters = [
            'module' => trim((string) $this->request->getGet('module')),
            'status' => '',
            'priority' => '',
            'q' => trim((string) $this->request->getGet('q')),
        ];

        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $query = (new ModuleHelloWorldEntryModel())
            ->select('id, module_slug, message, created_at, updated_at')
            ->where('scope_type', 'programme')
            ->where('scope_id', $programmeId);

        if ($filters['module'] !== '') {
            $query->where('module_slug', $filters['module']);
        }

        if ($filters['q'] !== '') {
            $query->like('message', $filters['q']);
        }

        $totalRows = $query->countAllResults(false);

        $rows = $query
            ->orderBy('updated_at', 'DESC')
            ->findAll($perPage, $offset);

        $moduleOptions = [
            ModuleRegistryService::HELLO_WORLD_PROGRAMME => 'Hello World Programme',
        ];

        $records = [];
        foreach ($rows as $row) {
            $records[] = [
                'id' => (int) ($row['id'] ?? 0),
                'module_slug' => (string) ($row['module_slug'] ?? ''),
                'module_name' => $moduleOptions[(string) ($row['module_slug'] ?? '')] ?? (string) ($row['module_slug'] ?? ''),
                'title' => (string) ($row['message'] ?? ''),
                'owner_username' => '',
                'status' => '',
                'priority' => '',
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'source_url' => site_url('programmes/' . $programmeId . '/modules/hello-world#entry-' . (int) ($row['id'] ?? 0)),
            ];
        }

        return view('dashboard/details', [
            'scopeType' => 'programme',
            'scopeId' => $programmeId,
            'scopeName' => (string) ($programme['name'] ?? ''),
            'records' => $records,
            'filters' => $filters,
            'moduleOptions' => $moduleOptions,
            'pagination' => $this->buildPaginationData('programmes/' . $programmeId . '/dashboard/details', $filters, $page, $perPage, $totalRows),
        ]);
    }

    /**
     * @param array<string, string> $filters
     * @return array<string, int|string|bool>
     */
    private function buildPaginationData(string $basePath, array $filters, int $page, int $perPage, int $totalRows): array
    {
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $currentPage = min($page, $totalPages);

        $queryParams = [];
        foreach ($filters as $key => $value) {
            if ($value === '') {
                continue;
            }

            $queryParams[$key] = $value;
        }

        $baseUrl = site_url($basePath);
        $queryString = $queryParams === [] ? '' : ('&' . http_build_query($queryParams));

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'per_page' => $perPage,
            'total_rows' => $totalRows,
            'has_prev' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'prev_url' => $baseUrl . '?page=' . max(1, $currentPage - 1) . $queryString,
            'next_url' => $baseUrl . '?page=' . min($totalPages, $currentPage + 1) . $queryString,
        ];
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

    private function isSystemAdministrator(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null);
    }

    private function sessionUserId(): ?int
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        return (int) $userId;
    }
}
