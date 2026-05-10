<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * Home component.
 */
class Home extends BaseController
{
    /**
     * Index operation.
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        if (! $this->hasAnyUser()) {
            return redirect()->to('/install/admin');
        }

        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login');
    }

    private function hasAnyUser(): bool
    {
        try {
            $db = db_connect();

            if (! $db->tableExists('users')) {
                return false;
            }

            return (new UserModel())->countAllResults() > 0;
        } catch (Throwable) {
            return false;
        }
    }
}
