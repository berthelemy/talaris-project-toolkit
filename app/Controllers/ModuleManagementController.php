<?php

namespace App\Controllers;

use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * ModuleManagementController component.
 */
class ModuleManagementController extends BaseController
{
    /**
     * Index operation.
     *
     * @return string|RedirectResponse
     */
    public function index(): string|RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        return view('modules/index', [
            'modules' => (new ModuleRegistryService())->allModules(),
        ]);
    }

    /**
     * Toggle operation.
     *
     * @param string $slug
     * @return RedirectResponse
     */
    public function toggle(string $slug): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $enabled = (string) $this->request->getPost('is_enabled') === '1';
        $result = (new ModuleRegistryService())->setEnabled($slug, $enabled, $actorId);

        return redirect()->to('/modules')
            ->with($result['ok'] ? 'success' : 'error', lang((string) $result['message_key']));
    }

    private function canManageModules(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'system.modules.manage', 'system', null)
            || $rbac->hasPermission($actorId, 'system.modules.add', 'system', null);
    }

    private function sessionUserId(): ?int
    {
        $userId = session('user_id');

        if (! is_int($userId) && ! ctype_digit((string) $userId)) {
            return null;
        }

        $user = (new UserModel())->find((int) $userId);

        if (! is_array($user)) {
            return null;
        }

        return (int) $userId;
    }
}
