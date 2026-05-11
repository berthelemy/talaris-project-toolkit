<?php

namespace App\Modules\RaidShared\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Auth\AuditLogger;
use App\Libraries\Modules\ModuleApiAuthorizationService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleRaidEntryModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

abstract class BaseProjectRaidController extends BaseController
{
    /**
     * @return string
     */
    abstract protected function moduleSlug(): string;

    /**
     * @return string
     */
    abstract protected function moduleRouteSegment(): string;

    /**
     * @return string
     */
    abstract protected function moduleTitleLangKey(): string;

    /**
     * @return string
     */
    abstract protected function moduleDescriptionLangKey(): string;

    public function index(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($project) || ! (new ModuleApiAuthorizationService())->canRead($actorId, 'project', $projectId)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled($this->moduleSlug(), 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        $entryModel = new ModuleRaidEntryModel();
        $entries = $this->queryEntries($entryModel, $projectId);
        $isReadOnly = ! (new ModuleApiAuthorizationService())->canWrite($actorId, 'project', $projectId);

        return view('modules/raid_project', [
            'project' => $project,
            'moduleSlug' => $this->moduleSlug(),
            'moduleRouteSegment' => $this->moduleRouteSegment(),
            'moduleTitleKey' => $this->moduleTitleLangKey(),
            'moduleDescriptionKey' => $this->moduleDescriptionLangKey(),
            'entries' => $entries,
            'owners' => $this->ownerOptions(),
            'statusOptions' => $this->statusOptions(),
            'priorityOptions' => $this->priorityOptions(),
            'filters' => [
                'q' => trim((string) $this->request->getGet('q')),
                'status' => trim((string) $this->request->getGet('status')),
                'owner_user_id' => (int) ($this->request->getGet('owner_user_id') ?? 0),
                'sort' => trim((string) $this->request->getGet('sort')),
            ],
            'isReadOnly' => $isReadOnly,
        ]);
    }

    public function create(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        $rules = $this->entryValidationRules();
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $ownerId = (int) $this->request->getPost('owner_user_id');
        if (! $this->isActiveUser($ownerId)) {
            return redirect()->back()->withInput()->with('error', lang('Domain.ownerInvalid'));
        }

        $entryModel = new ModuleRaidEntryModel();
        $entryId = $entryModel->insert([
            'module_slug' => $this->moduleSlug(),
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'title' => trim((string) $this->request->getPost('title')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'owner_user_id' => $ownerId,
            'status' => trim((string) $this->request->getPost('status')),
            'priority' => trim((string) $this->request->getPost('priority')),
            'target_date' => $this->nullableDate((string) $this->request->getPost('target_date')),
            'review_date' => $this->nullableDate((string) $this->request->getPost('review_date')),
            'closed_at' => trim((string) $this->request->getPost('status')) === 'closed' ? date('Y-m-d H:i:s') : null,
            'created_by_user_id' => $actorId,
            'updated_by_user_id' => $actorId,
        ], true);

        (new AuditLogger())->log('raid_entry_created', 'success', $actorId, [
            'module_slug' => $this->moduleSlug(),
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'entry_id' => $entryId,
        ]);

        return $this->redirectModule($projectId)->with('success', lang('Module.raidEntryCreatedSuccess'));
    }

    public function update(int $projectId, int $entryId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        $rules = $this->entryValidationRules();
        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $entryModel = new ModuleRaidEntryModel();
        $entry = $entryModel->find($entryId);

        if (! is_array($entry) || ! $this->matchesModuleScope($entry, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Module.raidEntryNotFound'));
        }

        $ownerId = (int) $this->request->getPost('owner_user_id');
        if (! $this->isActiveUser($ownerId)) {
            return redirect()->back()->withInput()->with('error', lang('Domain.ownerInvalid'));
        }

        $status = trim((string) $this->request->getPost('status'));

        $entryModel->update($entryId, [
            'title' => trim((string) $this->request->getPost('title')),
            'description' => $this->nullableString((string) $this->request->getPost('description')),
            'owner_user_id' => $ownerId,
            'status' => $status,
            'priority' => trim((string) $this->request->getPost('priority')),
            'target_date' => $this->nullableDate((string) $this->request->getPost('target_date')),
            'review_date' => $this->nullableDate((string) $this->request->getPost('review_date')),
            'closed_at' => $status === 'closed' ? (($entry['closed_at'] ?? null) ?: date('Y-m-d H:i:s')) : null,
            'updated_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('raid_entry_updated', 'success', $actorId, [
            'module_slug' => $this->moduleSlug(),
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'entry_id' => $entryId,
        ]);

        return $this->redirectModule($projectId)->with('success', lang('Module.raidEntryUpdatedSuccess'));
    }

    public function close(int $projectId, int $entryId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if (! $this->canWrite($actorId, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Domain.notAuthorized'));
        }

        $entryModel = new ModuleRaidEntryModel();
        $entry = $entryModel->find($entryId);

        if (! is_array($entry) || ! $this->matchesModuleScope($entry, $projectId)) {
            return $this->redirectModule($projectId)->with('error', lang('Module.raidEntryNotFound'));
        }

        $entryModel->update($entryId, [
            'status' => 'closed',
            'closed_at' => date('Y-m-d H:i:s'),
            'updated_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('raid_entry_closed', 'success', $actorId, [
            'module_slug' => $this->moduleSlug(),
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'entry_id' => $entryId,
        ]);

        return $this->redirectModule($projectId)->with('success', lang('Module.raidEntryClosedSuccess'));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function queryEntries(ModuleRaidEntryModel $entryModel, int $projectId): array
    {
        $builder = $entryModel
            ->select('module_raid_entries.*, users.username as owner_username')
            ->join('users', 'users.id = module_raid_entries.owner_user_id', 'left')
            ->where('module_raid_entries.module_slug', $this->moduleSlug())
            ->where('module_raid_entries.scope_type', 'project')
            ->where('module_raid_entries.scope_id', $projectId);

        $q = trim((string) $this->request->getGet('q'));
        if ($q !== '') {
            $builder->groupStart()
                ->like('module_raid_entries.title', $q)
                ->orLike('module_raid_entries.description', $q)
                ->groupEnd();
        }

        $status = trim((string) $this->request->getGet('status'));
        if (in_array($status, $this->statusOptions(), true)) {
            $builder->where('module_raid_entries.status', $status);
        }

        $ownerUserId = (int) ($this->request->getGet('owner_user_id') ?? 0);
        if ($ownerUserId > 0) {
            $builder->where('module_raid_entries.owner_user_id', $ownerUserId);
        }

        $sort = trim((string) $this->request->getGet('sort'));
        if ($sort === 'target_asc') {
            $builder->orderBy('target_date', 'ASC');
            $builder->orderBy('updated_at', 'DESC');
        } elseif ($sort === 'priority_desc') {
            $builder->orderBy("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END", 'ASC', false);
            $builder->orderBy('updated_at', 'DESC');
        } elseif ($sort === 'status_asc') {
            $builder->orderBy("CASE status WHEN 'open' THEN 1 WHEN 'in_review' THEN 2 ELSE 3 END", 'ASC', false);
            $builder->orderBy('updated_at', 'DESC');
        } else {
            $builder->orderBy('updated_at', 'DESC');
        }

        return $builder->findAll();
    }

    /**
     * @return list<string>
     */
    private function statusOptions(): array
    {
        return ['open', 'in_review', 'closed'];
    }

    /**
     * @return list<string>
     */
    private function priorityOptions(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    /**
     * @return list<array{id:int,username:string}>
     */
    private function ownerOptions(): array
    {
        $rows = (new UserModel())
            ->select('id, username')
            ->where('is_active', 1)
            ->orderBy('username', 'ASC')
            ->findAll();

        return array_map(static fn (array $row): array => [
            'id' => (int) ($row['id'] ?? 0),
            'username' => (string) ($row['username'] ?? ''),
        ], $rows);
    }

    /**
     * @return array<string, string>
     */
    private function entryValidationRules(): array
    {
        return [
            'title' => 'required|max_length[200]',
            'description' => 'permit_empty|max_length[5000]',
            'owner_user_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[open,in_review,closed]',
            'priority' => 'required|in_list[low,medium,high,critical]',
            'target_date' => 'permit_empty|valid_date[Y-m-d]',
            'review_date' => 'permit_empty|valid_date[Y-m-d]',
        ];
    }

    private function canWrite(?int $actorId, int $projectId): bool
    {
        if ($actorId === null) {
            return false;
        }

        if (! (new ModuleRegistryService())->isEnabled($this->moduleSlug(), 'project')) {
            return false;
        }

        return (new ModuleApiAuthorizationService())->canWrite($actorId, 'project', $projectId);
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function matchesModuleScope(array $entry, int $projectId): bool
    {
        return (string) ($entry['module_slug'] ?? '') === $this->moduleSlug()
            && (string) ($entry['scope_type'] ?? '') === 'project'
            && (int) ($entry['scope_id'] ?? 0) === $projectId;
    }

    private function isActiveUser(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        $user = (new UserModel())->find($userId);

        return is_array($user) && (bool) ($user['is_active'] ?? false);
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

    private function nullableDate(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function redirectModule(int $projectId): RedirectResponse
    {
        return redirect()->to('/projects/' . $projectId . '/modules/' . $this->moduleRouteSegment());
    }
}
