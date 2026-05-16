<?php

declare(strict_types=1);

namespace App\Modules\TasksRegisterProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/tasks-register', 'TasksRegisterController::index/$1', ['namespace' => 'App\Modules\TasksRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/tasks-register', 'TasksRegisterController::create/$1', ['namespace' => 'App\Modules\TasksRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/tasks-register/(:num)/update', 'TasksRegisterController::update/$1/$2', ['namespace' => 'App\Modules\TasksRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/tasks-register/(:num)/close', 'TasksRegisterController::close/$1/$2', ['namespace' => 'App\Modules\TasksRegisterProject\Controllers']);
$routes->post('projects/(:num)/modules/tasks-register/(:num)/delete', 'TasksRegisterController::delete/$1/$2', ['namespace' => 'App\Modules\TasksRegisterProject\Controllers']);
