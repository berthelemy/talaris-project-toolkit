<?php

/**
 * Routing definitions for RiskRegisterProject module endpoints.
 */

declare(strict_types=1);

namespace App\Modules\RiskRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/risk-register', 'RiskRegisterController::index/$1', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/risk-register', 'RiskRegisterController::create/$1', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/risk-register/(:num)/update', 'RiskRegisterController::update/$1/$2', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/risk-register/(:num)/close', 'RiskRegisterController::close/$1/$2', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
