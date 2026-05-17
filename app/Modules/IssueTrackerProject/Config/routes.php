<?php

/**
 * File documentation for app/Modules/IssueTrackerProject/Config/routes.php.
 */

declare(strict_types=1);

namespace App\Modules\IssueTrackerProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/issue-tracker', 'IssueTrackerController::index/$1', ['namespace' => 'App\Modules\IssueTrackerProject\Controllers']);
$routes->post('projects/(:num)/modules/issue-tracker', 'IssueTrackerController::create/$1', ['namespace' => 'App\Modules\IssueTrackerProject\Controllers']);
$routes->post('projects/(:num)/modules/issue-tracker/(:num)/update', 'IssueTrackerController::update/$1/$2', ['namespace' => 'App\Modules\IssueTrackerProject\Controllers']);
$routes->post('projects/(:num)/modules/issue-tracker/(:num)/close', 'IssueTrackerController::close/$1/$2', ['namespace' => 'App\Modules\IssueTrackerProject\Controllers']);
$routes->post('projects/(:num)/modules/issue-tracker/(:num)/delete', 'IssueTrackerController::delete/$1/$2', ['namespace' => 'App\Modules\IssueTrackerProject\Controllers']);
