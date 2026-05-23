<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Profile Management
$routes->group('profile', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'Profile::index');
    $routes->post('update', 'Profile::update');
    $routes->post('delete-avatar', 'Profile::deleteAvatar');
    $routes->post('update-theme', 'Profile::updateTheme');
});
