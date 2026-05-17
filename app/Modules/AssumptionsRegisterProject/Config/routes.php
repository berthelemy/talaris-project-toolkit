<?php

/**
 * File documentation for app/Modules/AssumptionsRegisterProject/Config/routes.php.
 */

declare(strict_types=1);

namespace App\Modules\AssumptionsRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/assumptions-register', 'AssumptionsRegisterController::index/$1', ['namespace' => 'App\Modules\AssumptionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/assumptions-register', 'AssumptionsRegisterController::create/$1', ['namespace' => 'App\Modules\AssumptionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/assumptions-register/(:num)/update', 'AssumptionsRegisterController::update/$1/$2', ['namespace' => 'App\Modules\AssumptionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/assumptions-register/(:num)/close', 'AssumptionsRegisterController::close/$1/$2', ['namespace' => 'App\Modules\AssumptionsRegisterProject\Controllers']);
