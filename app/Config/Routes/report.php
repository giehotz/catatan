<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Financial Reports & Analytics
$routes->group('reports', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Report::index');
    $routes->get('chart-data', 'Report::chartData');
    $routes->match(['get', 'post'], 'export', 'Report::export');
});
