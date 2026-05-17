<?php

/**
 * Routing definitions for HelloWorldProgramme module endpoints.
 */

declare(strict_types=1);

namespace App\Modules\HelloWorldProgramme\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('programmes/(:num)/modules/hello-world', 'HelloWorldController::index/$1', ['namespace' => 'App\Modules\HelloWorldProgramme\Controllers']);
$routes->post('programmes/(:num)/modules/hello-world', 'HelloWorldController::create/$1', ['namespace' => 'App\Modules\HelloWorldProgramme\Controllers']);
$routes->post('programmes/(:num)/modules/hello-world/entries/(:num)/autosave', 'HelloWorldController::autosave/$1/$2', ['namespace' => 'App\Modules\HelloWorldProgramme\Controllers']);
