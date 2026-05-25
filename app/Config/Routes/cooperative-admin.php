<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Panel Pengelola Koperasi (Admin & Manager)
$routes->group('admin/cooperative', ['namespace' => 'App\Controllers\Cooperative', 'filter' => 'coop_auth'], function($routes) {
    $routes->get('/', 'DashboardController::index');
    
    $routes->get('members', 'MemberController::members');
    $routes->post('toggle-member/(:num)', 'MemberController::toggleMemberStatus/$1');
    
    $routes->get('invitations', 'InvitationController::invitations');
    $routes->post('generate-invitation', 'InvitationController::generateInvitation');
    $routes->post('delete-invitation/(:num)', 'InvitationController::deleteInvitation/$1');
    
    $routes->get('savings', 'SavingController::savings');
    $routes->post('approve-saving/(:num)', 'SavingController::approveSaving/$1');
    $routes->post('reject-saving/(:num)', 'SavingController::rejectSaving/$1');
    
    $routes->get('loans', 'LoanController::loans');
    $routes->get('loans/direct', 'DirectLoanController::directLoanForm');
    $routes->post('loans/direct/store', 'DirectLoanController::storeDirectLoan');
    $routes->post('approve-loan/(:num)', 'LoanController::approveLoan/$1');
    $routes->post('reject-loan/(:num)', 'LoanController::rejectLoan/$1');
    
    $routes->get('settings', 'SettingController::settings');
    $routes->post('settings/update', 'SettingController::updateSettings');
    $routes->post('settings/preview-number', 'SettingController::previewNumber');
    $routes->get('settings/clear-cache', 'SettingController::clearCache');
    
    $routes->get('messages', 'MessageController::index');
    $routes->post('messages/broadcast', 'MessageController::broadcast');
    
    $routes->get('installments', 'InstallmentController::installments');
    $routes->post('installments/store', 'InstallmentController::storeInstallment');
    $routes->get('installments/receipt/(:num)', 'InstallmentController::printReceipt/$1');
    $routes->post('approve-installment/(:num)', 'InstallmentController::approveInstallment/$1');
    $routes->post('reject-installment/(:num)', 'InstallmentController::rejectInstallment/$1');
    
    $routes->get('funds', 'CashController::funds');
    $routes->post('funds/store', 'CashController::storeFund');
    $routes->post('funds/pdf', 'CashController::exportPdf');
    $routes->post('funds/excel', 'CashController::exportExcel');
    
    // (Modul SHU menggunakan controller CooperativeShu di luar Cooperative folder)
    $routes->get('shu', '\App\Controllers\CooperativeShu::adminIndex');
    $routes->post('shu/distribute', '\App\Controllers\CooperativeShu::distribute');
    $routes->post('shu/save-alokasi', '\App\Controllers\CooperativeShu::saveAlokasi');

    // Membership Resignation System Approval
    $routes->get('resign-requests', 'ResignApprovalController::index');
    $routes->post('approve-resign/(:num)', 'ResignApprovalController::approve/$1');
    $routes->post('reject-resign/(:num)', 'ResignApprovalController::reject/$1');

    // Laporan Tunggakan Angsuran Anggota
    $routes->get('reports/arrears', 'ArrearsReportController::index');
    $routes->post('reports/arrears/preview', 'ArrearsReportController::preview');
    $routes->post('reports/arrears/pdf', 'ArrearsReportController::exportPdf');
    $routes->post('reports/arrears/excel', 'ArrearsReportController::exportExcel');

    // Daftar Pinjaman (Direktori Pinjaman Aktif)
    $routes->get('loans/directory', 'LoanController::directory');
    $routes->get('loans/directory/(:num)', 'LoanController::directoryDetails/$1');
    $routes->get('loans/directory/(:num)/excel', 'LoanController::exportDirectoryDetailsExcel/$1');
});

