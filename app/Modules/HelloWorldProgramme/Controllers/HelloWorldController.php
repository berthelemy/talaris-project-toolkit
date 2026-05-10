<?php

namespace App\Modules\HelloWorldProgramme\Controllers;

use App\Controllers\BaseController;
use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProgrammeModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Programme-scope Hello World module endpoints.
 */
class HelloWorldController extends BaseController
{
    /**
     * Render the module page with all programme-scoped entries.
     *
     * @param int $programmeId Programme identifier.
     * @return string|RedirectResponse
     */
    public function index(int $programmeId): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($programme) || ! $this->canViewProgramme($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled(ModuleRegistryService::HELLO_WORLD_PROGRAMME, 'programme')) {
            return redirect()->to('/programmes/' . $programmeId)->with('error', lang('Module.disabledForScope'));
        }

        $entries = (new ModuleHelloWorldEntryModel())
            ->where('module_slug', ModuleRegistryService::HELLO_WORLD_PROGRAMME)
            ->where('scope_type', 'programme')
            ->where('scope_id', $programmeId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('App\Modules\HelloWorldProgramme\Views\index', [
            'programme' => $programme,
            'entries' => $entries,
        ]);
    }

    /**
     * Persist a new programme-scoped Hello World entry.
     *
     * @param int $programmeId Programme identifier.
     * @return RedirectResponse
     */
    public function create(int $programmeId): RedirectResponse
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! is_array($programme) || ! $this->canViewProgramme($actorId, $programme)) {
            return redirect()->to('/programmes')->with('error', lang('Domain.notAuthorized'));
        }

        if (! (new ModuleRegistryService())->isEnabled(ModuleRegistryService::HELLO_WORLD_PROGRAMME, 'programme')) {
            return redirect()->to('/programmes/' . $programmeId)->with('error', lang('Module.disabledForScope'));
        }

        $rules = [
            'message' => 'required|max_length[500]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        (new ModuleHelloWorldEntryModel())->insert([
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROGRAMME,
            'scope_type' => 'programme',
            'scope_id' => $programmeId,
            'message' => trim((string) $this->request->getPost('message')),
            'created_by_user_id' => $actorId,
        ]);

        (new AuditLogger())->log('module_hello_world_entry_created', 'success', $actorId, [
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROGRAMME,
            'scope_type' => 'programme',
            'scope_id' => $programmeId,
        ]);

        return redirect()->to('/programmes/' . $programmeId . '/modules/hello-world')
            ->with('success', lang('Module.entryCreatedSuccess'));
    }

    /**
     * Autosave an editable Hello World entry message for programme scope.
     */
    public function autosave(int $programmeId, int $entryId): ResponseInterface
    {
        $actorId = $this->sessionUserId();
        $programme = (new ProgrammeModel())->find($programmeId);

        if ($actorId === null) {
            return $this->response->setStatusCode(401)->setJSON(['ok' => false, 'error' => 'unauthorized']);
        }

        if (! is_array($programme) || ! $this->canViewProgramme($actorId, $programme)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $entryModel = new ModuleHelloWorldEntryModel();
        $entry = $entryModel->find($entryId);

        if (! is_array($entry)
            || (string) ($entry['module_slug'] ?? '') !== ModuleRegistryService::HELLO_WORLD_PROGRAMME
            || (int) ($entry['scope_id'] ?? 0) !== $programmeId
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
            'module_slug' => ModuleRegistryService::HELLO_WORLD_PROGRAMME,
            'scope_type' => 'programme',
            'scope_id' => $programmeId,
            'entry_id' => $entryId,
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'entry' => $updated,
            'csrf_hash' => csrf_hash(),
        ]);
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
