<?php

// Inbox System
$routes->group('inbox', ['filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'InboxController::index');
    $routes->get('(:num)', 'InboxController::show/$1');
    $routes->post('(:num)/delete', 'InboxController::delete/$1');
});
