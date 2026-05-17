<?php

/**
 * File documentation for app/Controllers/DashboardController.php.
 */

namespace App\Controllers;

use App\Libraries\Auth\RbacService;
use App\Models\UserModel;

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
}
