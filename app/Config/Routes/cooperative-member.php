<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Portal Anggota Koperasi (Registered User)
$routes->group('cooperative', ['namespace' => 'App\Controllers\Cooperative\Member', 'filter' => 'user_auth'], function($routes) {
    $routes->get('/', 'DashboardController::index');
    $routes->get('join', 'ActivationController::joinForm');
    $routes->post('join', 'ActivationController::processJoin');
    $routes->get('magic-join/(:any)', 'ActivationController::magicLink/$1');
    $routes->post('reject-join/(:any)', 'ActivationController::rejectInvitation/$1');
    
    $routes->get('savings', 'SavingsController::savings');
    $routes->post('deposit', 'SavingsController::deposit');
    $routes->post('withdraw', 'SavingsController::withdraw');
    
    $routes->get('loans', 'LoansController::loans');
    $routes->post('request-loan', 'LoansController::requestLoan');
    $routes->post('pay-installment/(:num)', 'LoansController::payInstallment/$1');
    
    $routes->get('bills', 'BillsController::bills');
    $routes->post('pay-saving-bill', 'BillsController::paySavingBill');
    
    $routes->get('shu', '\App\Controllers\CooperativeShu::memberIndex');

    // Membership Resignation System
    $routes->get('resign', 'ResignController::index');
    $routes->post('resign/submit', 'ResignController::submit');
    $routes->post('resign/cancel/(:num)', 'ResignController::cancel/$1');
    $routes->get('resign/letter/(:num)', 'ResignController::downloadLetter/$1');
});

// Public Resignation Verification (No auth filter)
$routes->get('cooperative/verify-resign/(:any)', '\App\Controllers\Cooperative\Member\ResignController::verifyPublic/$1');

