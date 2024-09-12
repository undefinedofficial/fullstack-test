<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');
$routes->get('/comment/(:num)', 'HomeController::comment/$1');

$routes->get('/api/comments', 'ApiController::comments');
$routes->get('/api/comments/(:num)', 'ApiController::commentById/$1');

$routes->post('/api/comments', 'ApiController::createComment');
$routes->put('/api/comments/(:num)', 'ApiController::updateComment/$1');
$routes->delete('/api/comments/(:num)', 'ApiController::deleteComment/$1');

