<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\ModuleHelloWorldEntryModel;
use App\Models\ProgrammeModel;
use CodeIgniter\HTTP\RedirectResponse;

class ProgrammeHelloWorldController extends BaseController
{
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

        return view('modules/programme_hello_world', [
            'programme' => $programme,
            'entries' => $entries,
        ]);
    }

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
