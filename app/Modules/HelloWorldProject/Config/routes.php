<?php

declare(strict_types=1);

namespace App\Modules\HelloWorldProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/hello-world', 'HelloWorldController::index/$1', ['namespace' => 'App\Modules\HelloWorldProject\Controllers']);
$routes->post('projects/(:num)/modules/hello-world', 'HelloWorldController::create/$1', ['namespace' => 'App\Modules\HelloWorldProject\Controllers']);
