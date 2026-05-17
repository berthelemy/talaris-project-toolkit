<?php

/**
 * Routing definitions for DependenciesRegisterProject module endpoints.
 */

declare(strict_types=1);

namespace App\Modules\DependenciesRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/dependencies-register', 'DependenciesRegisterController::index/$1', ['namespace' => 'App\Modules\DependenciesRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/dependencies-register', 'DependenciesRegisterController::create/$1', ['namespace' => 'App\Modules\DependenciesRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/dependencies-register/(:num)/update', 'DependenciesRegisterController::update/$1/$2', ['namespace' => 'App\Modules\DependenciesRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/dependencies-register/(:num)/close', 'DependenciesRegisterController::close/$1/$2', ['namespace' => 'App\Modules\DependenciesRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/dependencies-register/(:num)/delete', 'DependenciesRegisterController::delete/$1/$2', ['namespace' => 'App\Modules\DependenciesRegisterProject\Controllers']);
