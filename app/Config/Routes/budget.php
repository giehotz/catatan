<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Monthly Budgets
$routes->group('budgets', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Budget::index');
    $routes->post('set', 'Budget::setLimit');
    $routes->post('delete/(:num)', 'Budget::delete/$1');
});
