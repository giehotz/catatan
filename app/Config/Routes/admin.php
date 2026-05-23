<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rute Otentikasi Admin (tanpa filter, karena butuh login)
$routes->get('/admin/login', 'Admin\AdminAuth::showLogin');
$routes->post('/admin/login', 'Admin\AdminAuth::login');

// Fitur Admin Mutlak & Impersonation (Admin Only)
// Route stop impersonate TIDAK menggunakan filter admin_auth
$routes->get('/admin/stop-impersonate', 'Admin\Impersonation::stopImpersonate');

// Group Admin dengan Filter dan Namespace Khusus
$routes->group('admin', ['namespace' => 'App\Controllers\Admin', 'filter' => 'admin_auth'], function($routes) {
    $routes->get('/', 'Dashboard::index');
    
    // User Management
    $routes->post('toggle-status/(:num)', 'UserManagement::toggleStatus/$1');
    $routes->post('reset-password/(:num)', 'UserManagement::resetPassword/$1');
    $routes->post('assign-role/(:num)', 'UserManagement::assignRole/$1');
    
    // Excel Import
    $routes->post('import-excel', 'UserImport::importExcel');
    $routes->get('download-import-template', 'UserImport::downloadImportTemplate');
    
    // Impersonation
    $routes->get('impersonate/(:num)', 'Impersonation::impersonate/$1');
    
    // Audit Logs
    $routes->get('audit-logs', 'AuditLog::auditLogs');
    $routes->post('audit-logs/data', 'AuditLog::getLogsData');
    $routes->get('audit-logs/backup', 'AuditLog::backupLogs');
    $routes->post('audit-logs/clear', 'AuditLog::clearLogs');

    // Active Users Monitor
    $routes->get('active-users', 'ActiveUsers::index');
    $routes->get('active-users/data', 'ActiveUsers::data');
});
