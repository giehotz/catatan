<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Savings Goals Planner
$routes->group('savings', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Savings::index');
    $routes->post('create', 'Savings::create');
    $routes->post('update/(:num)', 'Savings::update/$1');
    $routes->post('delete/(:num)', 'Savings::delete/$1');
    $routes->post('allocate/(:num)', 'Savings::allocate/$1');
});
