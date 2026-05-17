<?php

/**
 * File documentation for app/Controllers/InstallController.php.
 */

namespace App\Controllers;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\PasswordPolicyService;
use App\Libraries\Auth\RbacService;
use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;
use Throwable;

/**
 * InstallController component.
 */
class InstallController extends BaseController
{
    /**
     * Index operation.
     *
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        return redirect()->to('/install/admin');
    }

    /**
     * AdminForm operation.
     *
     * @return string|RedirectResponse
     */
    public function adminForm(): string|RedirectResponse
    {
        if ($this->hasAnyUser()) {
            return redirect()->to('/login')->with('error', lang('Auth.setupAlreadyCompleted'));
        }

        return view('install/admin', [
            'canInstall' => $this->hasUsersTable(),
        ]);
    }

    /**
     * CreateAdmin operation.
     *
     * @return RedirectResponse
     */
    public function createAdmin(): RedirectResponse
    {
        if ($this->hasAnyUser()) {
            return redirect()->to('/login')->with('error', lang('Auth.setupAlreadyCompleted'));
        }

        if (! $this->hasUsersTable()) {
            return redirect()->back()->with('error', lang('Auth.setupMigrationsRequired'));
        }

        $rules = [
            'username'         => 'required|min_length[3]|max_length[100]|alpha_dash|is_unique[users.username]',
            'email'            => 'required|valid_email|max_length[255]|is_unique[users.email]',
            'password'         => 'required',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validateData($this->request->getPost(), $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $password     = (string) $this->request->getPost('password');
        $policyErrors = (new PasswordPolicyService())->validate($password);

        if ($policyErrors !== []) {
            return redirect()->back()->withInput()->with('errors', $policyErrors);
        }

        $userModel = new UserModel();
        $userModel->insert([
            'username'      => (string) $this->request->getPost('username'),
            'email'         => (string) $this->request->getPost('email'),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'is_active'     => 1,
        ]);

        $userId   = (int) $userModel->getInsertID();
        $username = (string) $this->request->getPost('username');

        // Backfill bootstrap administrator RBAC assignment when role tables exist.
        if ($this->hasRoleTables()) {
            try {
                (new RbacService())->assignRoleToUser($userId, 'administrator', 'system', null, $userId);
            } catch (Throwable) {
                // Keep installation resilient in case RBAC schema is not yet available.
            }
        }

        (new AuditLogger())->log('bootstrap_admin_created', 'success', $userId);

        session()->regenerate();
        session()->set([
            'user_id'          => $userId,
            'username'         => $username,
            'last_activity_at' => time(),
        ]);

        return redirect()->to('/dashboard')->with('success', lang('Auth.setupCompleted'));
    }

    private function hasAnyUser(): bool
    {
        if (! $this->hasUsersTable()) {
            return false;
        }

        return (new UserModel())->countAllResults() > 0;
    }

    private function hasUsersTable(): bool
    {
        try {
            return db_connect()->tableExists('users');
        } catch (Throwable) {
            return false;
        }
    }

    private function hasRoleTables(): bool
    {
        try {
            $db = db_connect();

            return $db->tableExists('roles') && $db->tableExists('user_role_assignments');
        } catch (Throwable) {
            return false;
        }
    }
}
