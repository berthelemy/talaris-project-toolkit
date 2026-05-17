<?php

/**
 * HTTP filter for Session Inactivity Filter request handling.
 */

namespace App\Filters;

use App\Libraries\Auth\AuditLogger;
use App\Libraries\Auth\AuthSettingsService;
use App\Libraries\Modules\ModuleLockService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * SessionInactivityFilter component.
 */
class SessionInactivityFilter implements FilterInterface
{
    /**
     * Before operation.
     *
     * @param RequestInterface $request
     * @param mixed $arguments
     * @return mixed
     */
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
            $userId = (int) $session->get('user_id');
            (new ModuleLockService())->releaseAllForUser($userId, 'session_timeout');
            (new AuditLogger())->log('session_timeout_logout', 'success', $userId);
            $session->destroy();

            return redirect()->to('/login')->with('error', lang('Auth.sessionTimedOut'));
        }

        $session->set('last_activity_at', $now);

        return null;
    }

    /**
     * After operation.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param mixed $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
