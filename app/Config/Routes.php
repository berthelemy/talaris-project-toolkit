<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->get('install', 'InstallController::index');
$routes->get('install/admin', 'InstallController::adminForm');
$routes->post('install/admin', 'InstallController::createAdmin');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->post('logout', 'AuthController::logout', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::sendResetLink');
$routes->get('reset-password/(:segment)', 'AuthController::resetPasswordForm/$1');
$routes->post('reset-password/(:segment)', 'AuthController::resetPassword/$1');

$routes->get('dashboard', 'DashboardController::index', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('profile', 'ProfileController::edit', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('profile', 'ProfileController::update', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('profile/password', 'ProfileController::changePassword', ['filter' => ['auth', 'sessiontimeout']]);
