<?php

declare(strict_types=1);

namespace App\Modules\RiskRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/risk-register', 'RiskRegisterController::index/$1', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/risk-register', 'RiskRegisterController::create/$1', ['namespace' => 'App\Modules\RiskRegisterProject\Controllers']);
