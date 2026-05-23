<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    /**
     * Render the admin panel user list dashboard.
     */
    public function index()
    {
        $userModel = auth()->getProvider();
        
        // Fetch all registered users
        $users = $userModel->orderBy('created_at', 'DESC')->findAll();

        // Calculate aggregates for dashboard statistics cards
        $totalUsers = count($users);
        $activeUsers = 0;
        $blockedUsers = 0;

        foreach ($users as $u) {
            // Determine active/blocked based on Shield's 'active' column
            if ($u->active) {
                $activeUsers++;
            } else {
                $blockedUsers++;
            }
            
            // Check impersonation logic (complex business rule preparation for view)
            $u->can_be_impersonated = auth()->id() !== $u->id && !$u->inGroup('admin') && !$u->inGroup('superadmin') && !$u->inGroup('manager');
        }

        return view('admin/dashboard', [
            'title'        => 'Panel Admin - Kelola Pengguna',
            'users'        => $users,
            'totalUsers'   => $totalUsers,
            'activeUsers'  => $activeUsers,
            'blockedUsers' => $blockedUsers,
        ]);
    }
}
