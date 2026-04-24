<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        if (session()->has('user_id')) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login');
    }
}
