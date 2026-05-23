<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class Impersonation extends BaseController
{
    /**
     * Start impersonating a regular user.
     */
    public function impersonate(int $userId)
    {
        // Prevent self impersonation
        if (auth()->id() === $userId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menyamar sebagai diri Anda sendiri.');
        }

        $userModel = auth()->getProvider();
        $targetUser = $userModel->find($userId);

        if (!$targetUser) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Prevent impersonating another Admin or Superadmin
        if ($targetUser->inGroup('admin') || $targetUser->inGroup('superadmin')) {
            return redirect()->back()->with('error', 'Keamanan Tinggi: Anda tidak dapat menyamar sebagai sesama Administrator.');
        }

        // Log who is doing the impersonation
        $adminUser = auth()->user();
        AuditLogModel::log('impersonation_start', "Administrator '{$adminUser->username}' mulai menyamar sebagai pengguna '{$targetUser->username}' (ID: {$userId})");

        // Save original admin ID
        $adminId = $adminUser->id;

        // Logout the admin user first to clear active session credentials before target login
        auth()->logout();

        // Save original admin ID back in session
        session()->set('impersonator_user_id', $adminId);

        // Login as the target user
        auth()->login($targetUser);

        return redirect()->to(base_url('/'))->with('message', "Anda sekarang menyamar sebagai '{$targetUser->username}'.");
    }

    /**
     * Stop current user impersonation and return to Admin session.
     */
    public function stopImpersonate()
    {
        if (!session()->has('impersonator_user_id')) {
            return redirect()->to(base_url('/'));
        }

        $adminId = session()->get('impersonator_user_id');
        session()->remove('impersonator_user_id');

        $userModel = auth()->getProvider();
        $adminUser = $userModel->find($adminId);

        if (!$adminUser) {
            auth()->logout();
            return redirect()->to(base_url('admin/login'))->with('error', 'Sesi Administrator tidak ditemukan.');
        }

        // Log stop impersonation while still under the target user's context
        AuditLogModel::log('impersonation_stop', "Sesi penyamaran dihentikan. Kembali ke Administrator '{$adminUser->username}'", auth()->id());

        // Logout target user first to clear current active session credentials before admin login
        auth()->logout();

        // Login back as Admin
        auth()->login($adminUser);

        return redirect()->to(base_url('admin'))->with('message', 'Sesi penyamaran dihentikan. Selamat datang kembali, Administrator.');
    }
}
