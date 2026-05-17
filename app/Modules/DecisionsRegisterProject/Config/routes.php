<?php

/**
 * File documentation for app/Modules/DecisionsRegisterProject/Config/routes.php.
 */

declare(strict_types=1);

namespace App\Modules\DecisionsRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/decisions-register', 'DecisionsRegisterController::index/$1', ['namespace' => 'App\Modules\DecisionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/decisions-register', 'DecisionsRegisterController::create/$1', ['namespace' => 'App\Modules\DecisionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/decisions-register/(:num)/update', 'DecisionsRegisterController::update/$1/$2', ['namespace' => 'App\Modules\DecisionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/decisions-register/(:num)/close', 'DecisionsRegisterController::close/$1/$2', ['namespace' => 'App\Modules\DecisionsRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/decisions-register/(:num)/delete', 'DecisionsRegisterController::delete/$1/$2', ['namespace' => 'App\Modules\DecisionsRegisterProject\Controllers']);
