<?php

/**
 * Application configuration for Routes.
 */

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
$routes->post('impersonate/(:num)', 'ImpersonationController::start/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('impersonate/stop', 'ImpersonationController::stop', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('profile', 'ProfileController::edit', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('profile', 'ProfileController::update', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('profile/password', 'ProfileController::changePassword', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('language', 'LanguageController::switch', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('site-settings', 'SiteSettingsController::edit', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('site-settings', 'SiteSettingsController::update', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('theme', 'ThemeSettingsController::edit', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('theme', 'ThemeSettingsController::update', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('modules', 'ModuleManagementController::index', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('modules/(:segment)/toggle', 'ModuleManagementController::toggle/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('modules/(:segment)/ordering', 'ModuleManagementController::updateOrdering/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('modules/(:segment)/widget-config', 'ModuleManagementController::updateWidgetConfig/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('modules/(:segment)/widget-layout-default', 'ModuleManagementController::updateDefaultWidgetLayout/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('modules/locks/(:num)/release', 'ModuleManagementController::releaseLock/$1', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('users', 'UserManagementController::index', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('users', 'UserManagementController::create', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('users/(:num)/edit', 'UserManagementController::edit/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('users/(:num)', 'UserManagementController::update/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('users/(:num)/deactivate', 'UserManagementController::deactivate/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('users/(:num)/roles', 'UserManagementController::assignRole/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('users/(:num)/roles/revoke', 'UserManagementController::revokeRole/$1', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('programmes', 'ProgrammeController::index', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('programmes/(:num)', 'ProgrammeController::show/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes', 'ProgrammeController::create', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('programmes/(:num)/edit', 'ProgrammeController::edit/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)', 'ProgrammeController::update/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)/delete', 'ProgrammeController::delete/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)/projects/(:num)/link', 'ProgrammeController::linkProject/$1/$2', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)/projects/(:num)/unlink', 'ProgrammeController::unlinkProject/$1/$2', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)/managers', 'ProgrammeController::assignManager/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('programmes/(:num)/widgets/layout', 'ProgrammeController::editWidgetLayout/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('programmes/(:num)/widgets/layout', 'ProgrammeController::updateWidgetLayout/$1', ['filter' => ['auth', 'sessiontimeout']]);

$routes->get('projects', 'ProjectController::index', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('projects/(:num)', 'ProjectController::show/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('projects', 'ProjectController::create', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('projects/(:num)/edit', 'ProjectController::edit/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('projects/(:num)', 'ProjectController::update/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('projects/(:num)/delete', 'ProjectController::delete/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('projects/(:num)/managers', 'ProjectController::assignManager/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->get('projects/(:num)/widgets/layout', 'ProjectController::editWidgetLayout/$1', ['filter' => ['auth', 'sessiontimeout']]);
$routes->post('projects/(:num)/widgets/layout', 'ProjectController::updateWidgetLayout/$1', ['filter' => ['auth', 'sessiontimeout']]);

// Load module routes from each module's Config/routes.php
$moduleDir = APPPATH . 'Modules';
if (is_dir($moduleDir)) {
    $modules = array_diff(scandir($moduleDir), ['.', '..']);
    foreach ($modules as $module) {
        $moduleRoutesFile = $moduleDir . '/' . $module . '/Config/routes.php';
        if (is_file($moduleRoutesFile) && is_dir($moduleDir . '/' . $module)) {
            include $moduleRoutesFile;
        }
    }
}
