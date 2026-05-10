<?php

namespace App\Modules\RiskRegisterProject\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProjectModel;
use CodeIgniter\HTTP\RedirectResponse;

class RiskRegisterController extends BaseController
{
    public function index(int $projectId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($project) || ! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('risk_register_project', 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        $entries = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', 'risk_register_project')
            ->where('scope_type', 'project')
            ->where('scope_id', $projectId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('App\Modules\RiskRegisterProject\Views\index', [
            'project' => $project,
            'entries' => $entries,
        ]);
    }

    public function create(int $projectId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($project) || ! $this->canViewProject($actorId, $project)) {
            return redirect()->to('/projects')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled('risk_register_project', 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        if (! $this->validateData($this->request->getPost(), ['message' => 'required|max_length[500]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => trim((string) $this->request->getPost('message')),
            'created_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('module_reference_entry_created', 'success', $actorId, [
            'module_slug' => 'risk_register_project',
            'scope_type' => 'project',
            'scope_id' => $projectId,
        ]);

        return redirect()->to('/projects/' . $projectId . '/modules/risk-register')
            ->with('success', lang('Module.entryCreatedSuccess'));
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
            || $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null);
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
