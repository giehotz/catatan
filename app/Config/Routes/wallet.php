<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Wallets Management
$routes->group('wallets', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Wallet::index');
    $routes->post('create', 'Wallet::create');
    $routes->post('update/(:num)', 'Wallet::update/$1');
    $routes->post('delete/(:num)', 'Wallet::delete/$1');
    $routes->get('transfer', 'Wallet::transferIndex');
    $routes->post('transfer', 'Wallet::processTransfer');
});
