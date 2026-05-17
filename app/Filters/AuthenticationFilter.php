<?php

/**
 * HTTP filter for Authentication Filter request handling.
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthenticationFilter component.
 */
class AuthenticationFilter implements FilterInterface
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
        if (session()->has('user_id')) {
            return null;
        }

        return redirect()->to('/login')->with('error', lang('Auth.loginRequired'));
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
