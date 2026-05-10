<?php

namespace App\Modules\HelloWorldProject\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProjectModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Project-scope Hello World module endpoints.
 */
class HelloWorldController extends BaseController
{
    /**
     * Render the module page with all project-scoped entries.
     *
     * @param int $projectId Project identifier.
     * @return string|RedirectResponse
     */
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

        if (! (new ModuleRegistryService())->isEnabled(ModuleRegistryService::HELLO_WORLD_PROJECT, 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        $entries = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROJECT)
            ->where('scope_type', 'project')
            ->where('scope_id', $projectId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('App\Modules\HelloWorldProject\Views\index', [
            'project' => $project,
            'entries' => $entries,
        ]);
    }

    /**
     * Persist a new project-scoped Hello World entry.
     *
     * @param int $projectId Project identifier.
     * @return RedirectResponse
     */
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

        if (! (new ModuleRegistryService())->isEnabled(ModuleRegistryService::HELLO_WORLD_PROJECT, 'project')) {
            return redirect()->to('/projects/' . $projectId)->with('error', lang('Module.disabledForScope'));
        }

        $rules = [
            'message' => 'required|max_length[500]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'message' => trim((string) $this->request->getPost('message')),
            'created_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('module_hello_world_entry_created', 'success', $actorId, [
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
        ]);

        return redirect()->to('/projects/' . $projectId . '/modules/hello-world')
            ->with('success', lang('Module.entryCreatedSuccess'));
    }

    /**
     * Autosave an editable Hello World entry message for project scope.
     */
    public function autosave(int $projectId, int $entryId): ResponseInterface
    {
        $actorId = $this->sessionUserId();
        $project = (new ProjectModel())->find($projectId);

        if ($actorId === null) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'unauthorized']);
        }

        if (! is_array($project) || ! $this->canViewProject($actorId, $project)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $entryModel = new ModuleHelloWorldEntryModel();
        $entry = $entryModel->find($entryId);

        if (! is_array($entry)
            || (string) ($entry['module_slug'] ?? '') !== ModuleRegistryService::HELLO_WORLD_PROJECT
            || (int) ($entry['scope_id'] ?? 0) !== $projectId
        ) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'error' => 'entry_not_found']);
        }

        $message = trim((string) $this->request->getPost('message'));
        if ($message === '' || strlen($message) > 500) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'error' => 'invalid_message']);
        }

        $lastUpdatedAt = (string) $this->request->getPost('last_updated_at');
        if ($lastUpdatedAt !== '' && $lastUpdatedAt !== (string) ($entry['updated_at'] ?? '')) {
            return $this->response->setStatusCode(409)->setJSON([
                'ok' => false,
                'error' => 'conflict',
                'current' => [
                    'message' => (string) ($entry['message'] ?? ''),
                    'updated_at' => (string) ($entry['updated_at'] ?? ''),
                ],
            ]);
        }

        $entryModel->update($entryId, ['message' => $message]);
        $updated = $entryModel->find($entryId);

        (new AuditLogger())->log('autosave_update', 'success', $actorId, [
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROJECT,
            'scope_type' => 'project',
            'scope_id' => $projectId,
            'entry_id' => $entryId,
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'entry' => $updated,
            'csrf_hash' => csrf_hash(),
        ]);
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
