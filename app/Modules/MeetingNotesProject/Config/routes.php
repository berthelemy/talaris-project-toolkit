<?php
declare(strict_types=1);

namespace App\Modules\MeetingNotesProject\Config;

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('projects/(:num)/modules/meeting-notes', 'MeetingNotesController::index/$1', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes', 'MeetingNotesController::create/$1', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/decisions', 'MeetingNotesController::createRelatedDecision/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/actions', 'MeetingNotesController::createRelatedAction/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/update', 'MeetingNotesController::update/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/risks', 'MeetingNotesController::createRelatedRisk/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/assumptions', 'MeetingNotesController::createRelatedAssumption/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/issues', 'MeetingNotesController::createRelatedIssue/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/dependencies', 'MeetingNotesController::createRelatedDependency/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/close', 'MeetingNotesController::close/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);
$routes->post('projects/(:num)/modules/meeting-notes/(:num)/delete', 'MeetingNotesController::delete/$1/$2', ['namespace' => 'App\Modules\MeetingNotesProject\Controllers']);