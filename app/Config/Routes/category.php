<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Category Management
$routes->group('category', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Category::index');
    $routes->post('create', 'Category::create');
    $routes->post('delete/(:any)/(:num)', 'Category::delete/$1/$2');
});
