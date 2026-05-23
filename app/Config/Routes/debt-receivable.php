<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Debt and Receivable Management
$routes->group('debt-receivable', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'DebtReceivable::index');
    $routes->post('create-debt', 'DebtReceivable::createDebt');
    $routes->post('create-receivable', 'DebtReceivable::createReceivable');
    $routes->post('update-status/(:any)/(:num)', 'DebtReceivable::updateStatus/$1/$2');
    $routes->post('delete/(:any)/(:num)', 'DebtReceivable::delete/$1/$2');
});
