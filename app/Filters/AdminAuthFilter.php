<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    /**
     * Inspect incoming requests before hitting Admin Controller actions.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $path = trim($request->getUri()->getPath(), '/');

        // 1. Bypass filter if we are already accessing the admin login URL to prevent redirection loops
        if ($path === 'admin/login' || str_starts_with($path, 'admin/login/')) {
            return;
        }

        // 2. Bypass filter for stop-impersonate (route is outside admin group, no filter needed)
        if ($path === 'admin/stop-impersonate' || str_starts_with($path, 'admin/stop-impersonate/')) {
            return;
        }

        // 2. If not logged in, redirect specifically to /admin/login (constructed correctly with index.php if needed)
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('admin/login'))->with('error', 'Silakan masuk terlebih dahulu untuk mengakses Panel Admin.');
        }

        // 3. If logged in but lacks admin/superadmin privileges, check if they are a Manager accessing cooperative routes
        $user = auth()->user();
        
        // 4. Record last active date
        if (setting('Auth.recordActiveDate')) {
            auth()->recordActiveDate();
        }
        if (!$user->inGroup('admin') && !$user->inGroup('superadmin')) {
            // Allow Managers to access cooperative management routes under /admin/cooperative
            if ($user->inGroup('manager') && strpos(current_url(), 'admin/cooperative') !== false) {
                return;
            }
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }
    }

    /**
     * Inspect response after completing the request.
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
