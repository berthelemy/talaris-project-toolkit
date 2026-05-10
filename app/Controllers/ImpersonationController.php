<?php

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\RbacService;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class ImpersonationController extends BaseController
{
    public function start(int $targetUserId): RedirectResponse
    {
        $actorId = $this->sessionUserId();

        if ($actorId === null) {
            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        if (session('impersonator_user_id') !== null) {
            return redirect()->to('/dashboard')->with('error', lang('Auth.impersonationAlreadyActive'));
        }

        $rbac = new RbacService();
        if (! $rbac->hasPermission($actorId, 'system.users.impersonate', 'system', null)) {
            (new AuditLogger())->log('impersonation_denied', 'failed', $actorId, [
                'reason' => 'missing_permission',
                'target_user_id' => $targetUserId,
            ]);

            return redirect()->to('/dashboard')->with('error', lang('Auth.impersonationNotAllowed'));
        }

        if ($targetUserId === $actorId) {
            return redirect()->to('/dashboard')->with('error', lang('Auth.impersonationTargetInvalid'));
        }

        $targetUser = (new UserModel())->find($targetUserId);

        if ($targetUser === null || ! (bool) ($targetUser['is_active'] ?? false)) {
            (new AuditLogger())->log('impersonation_denied', 'failed', $actorId, [
                'reason' => 'target_invalid',
                'target_user_id' => $targetUserId,
            ]);

            return redirect()->to('/dashboard')->with('error', lang('Auth.impersonationTargetInvalid'));
        }

        $session = session();
        $session->set([
            'impersonator_user_id' => $actorId,
            'impersonator_username' => (string) session('username'),
            'user_id' => (int) $targetUser['id'],
            'username' => (string) $targetUser['username'],
            'is_impersonating' => true,
            'last_activity_at' => time(),
        ]);

        (new AuditLogger())->log('impersonation_started', 'success', $actorId, [
            'target_user_id' => (int) $targetUser['id'],
        ]);

        return redirect()->to('/dashboard')->with('success', lang('Auth.impersonationStartedSuccess', [
            'username' => (string) $targetUser['username'],
        ]));
    }

    public function stop(): RedirectResponse
    {
        $currentUserId = $this->sessionUserId();
        $impersonatorId = session('impersonator_user_id');

        if ($currentUserId === null || (! is_int($impersonatorId) && ! ctype_digit((string) $impersonatorId))) {
            return redirect()->to('/dashboard')->with('error', lang('Auth.impersonationNotActive'));
        }

        $impersonator = (new UserModel())->find((int) $impersonatorId);

        if ($impersonator === null) {
            session()->destroy();

            return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
        }

        $session = session();
        $session->set([
            'user_id' => (int) $impersonator['id'],
            'username' => (string) $impersonator['username'],
            'last_activity_at' => time(),
        ]);
        $session->remove(['impersonator_user_id', 'impersonator_username', 'is_impersonating']);

        (new AuditLogger())->log('impersonation_stopped', 'success', (int) $impersonator['id'], [
            'impersonated_user_id' => $currentUserId,
        ]);

        return redirect()->to('/dashboard')->with('success', lang('Auth.impersonationStoppedSuccess'));
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