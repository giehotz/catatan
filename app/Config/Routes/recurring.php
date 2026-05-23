<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Recurring Transactions
$routes->group('recurring', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Recurring::index');
    $routes->post('create', 'Recurring::create');
    $routes->post('toggle/(:num)', 'Recurring::toggle/$1');
    $routes->post('delete/(:num)', 'Recurring::delete/$1');
});
