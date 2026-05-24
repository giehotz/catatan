<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserAuthFilter implements FilterInterface
{
    /**
     * Ensure the user is logged in before accessing protected routes.
     * Admin/Superadmin users are also allowed to access user modules.
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // If not logged in, redirect to login page
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        // Record last active date
        if (setting('Auth.recordActiveDate')) {
            auth()->recordActiveDate();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}

