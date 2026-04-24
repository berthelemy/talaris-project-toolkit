<?php

namespace App\Filters;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\AuthSettingsService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionInactivityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->has('user_id')) {
            return null;
        }

        $settings     = (new AuthSettingsService())->get();
        $lastActivity = (int) ($session->get('last_activity_at') ?? 0);
        $now          = time();
        $threshold    = (int) $settings['inactivity_timeout_seconds'];

        if ($lastActivity > 0 && ($now - $lastActivity) > $threshold) {
            (new AuditLogger())->log('session_timeout_logout', 'success', (int) $session->get('user_id'));
            $session->destroy();

            return redirect()->to('/login')->with('error', lang('Auth.sessionTimedOut'));
        }

        $session->set('last_activity_at', $now);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
