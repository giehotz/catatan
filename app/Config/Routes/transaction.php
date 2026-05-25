<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Transaction Management
$routes->group('transaction', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Transaction::index');
    $routes->post('create', 'Transaction::create');
    $routes->post('update/(:num)', 'Transaction::update/$1');
    $routes->post('delete/(:num)', 'Transaction::delete/$1');
    $routes->post('adjust-balance', 'Transaction::adjustBalance');
    $routes->post('data', 'Transaction::getTransactionsData');
});
