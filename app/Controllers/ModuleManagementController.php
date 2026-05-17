<?php

/**
 * System module-management controller for enablement, ordering, and default widget layout administration.
 */

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Libraries\Modules\ModuleLockService;
use App\Libraries\Modules\ModuleRegistryService;
use App\Libraries\Modules\ModuleWidgetLayoutService;
use App\Models\ModuleRegistryModel;
use App\Models\ModuleWidgetFailureModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Provide module administration actions scoped to authorized system users.
 */
class ModuleManagementController extends BaseController
{
    /**
     * Display module registry, lock telemetry, and default widget layout settings.
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
            'recentFailures' => $this->recentFailuresByModule(),
            'activeLocks' => (new ModuleLockService())->activeLocks(),
            'defaultLayoutPreferences' => (new ModuleWidgetLayoutService())->getDefaultByScope('project')
                + (new ModuleWidgetLayoutService())->getDefaultByScope('programme'),
        ]);
    }

    /**
     * Enable or disable a module by slug.
     *
     * @param string $slug Module identifier.
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

    /**
        * Update a module display ordering value.
        *
        * @param string $slug Module identifier.
     * @return RedirectResponse
     */
    public function updateOrdering(string $slug): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $displayOrder = (int) $this->request->getPost('display_order');

        (new ModuleRegistryModel())
            ->where('slug', $slug)
            ->set([
                'display_order' => max(0, $displayOrder),
            ])
            ->update();

        return redirect()->to('/modules')->with('success', lang('Module.orderUpdatedSuccess'));
    }

    /**
        * Persist widget configuration settings for a module.
        *
        * @param string $slug Module identifier.
     * @return RedirectResponse
     */
    public function updateWidgetConfig(string $slug): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $maxEntries = max(1, min(25, (int) $this->request->getPost('max_entries')));

        (new ModuleRegistryModel())
            ->where('slug', $slug)
            ->set([
                'widget_config_json' => json_encode([
                    'max_entries' => $maxEntries,
                ]),
            ])
            ->update();

        return redirect()->to('/modules')->with('success', lang('Module.configUpdatedSuccess'));
    }

    /**
     * Update a module's default widget layout for its declared scope type.
     *
     * @param string $slug Module identifier.
     * @return RedirectResponse
     */
    public function updateDefaultWidgetLayout(string $slug): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $module = (new ModuleRegistryModel())->where('slug', $slug)->first();
        if (! is_array($module)) {
            return redirect()->to('/modules')->with('error', lang('Module.notFound'));
        }

        $scopeType = (string) ($module['scope_type'] ?? '');
        if (! in_array($scopeType, ['programme', 'project'], true)) {
            return redirect()->to('/modules')->with('error', lang('Module.notFound'));
        }

        $isVisible = (string) $this->request->getPost('is_visible') === '1';
        $displayOrder = max(0, (int) $this->request->getPost('display_order'));

        (new ModuleWidgetLayoutService())->upsert($scopeType, 0, $slug, $isVisible, $displayOrder, $actorId);

        (new AuditLogger())->log('module_widget_default_layout_updated', 'success', $actorId, [
            'module_slug' => $slug,
            'scope_type' => $scopeType,
            'scope_id' => 0,
            'is_visible' => $isVisible,
            'display_order' => $displayOrder,
        ]);

        return redirect()->to('/modules')->with('success', lang('Module.defaultLayoutUpdatedSuccess'));
    }

    /**
     * Release an active module lock as an authorized administrator.
     *
     * @param int $lockId Lock identifier.
     * @return RedirectResponse
     */
    public function releaseLock(int $lockId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (! $this->canManageModules($actorId)) {
            return redirect()->to('/dashboard')->with('error', lang('Domain.notAuthorized'));
        }

        $released = (new ModuleLockService())->releaseByIdAsAdmin($lockId, $actorId);

        return redirect()->to('/modules')->with($released ? 'success' : 'error', lang($released ? 'Module.lockReleaseSuccess' : 'Module.lockReleaseError'));
    }

    /**
     * Determine whether a user can manage modules at system scope.
     *
     * @param int $actorId User identifier.
     * @return bool
     */
    private function canManageModules(int $actorId): bool
    {
        $rbac = new RbacService();

        return $rbac->hasPermission($actorId, 'system.modules.manage', 'system', null)
            || $rbac->hasPermission($actorId, 'system.modules.add', 'system', null);
    }

    /**
     * Resolve and validate the current session user id.
     *
     * @return int|null
     */
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

    /**
        * Count widget failures per module for the trailing 24-hour window.
        *
     * @return array<string, int>
     */
    private function recentFailuresByModule(): array
    {
        $rows = (new ModuleWidgetFailureModel())
            ->select('module_slug, COUNT(*) as failure_count')
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->groupBy('module_slug')
            ->findAll();

        $counts = [];

        foreach ($rows as $row) {
            $slug = (string) ($row['module_slug'] ?? '');
            if ($slug === '') {
                continue;
            }

            $counts[$slug] = (int) ($row['failure_count'] ?? 0);
        }

        return $counts;
    }
}
