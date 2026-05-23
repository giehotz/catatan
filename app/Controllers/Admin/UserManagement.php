<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class UserManagement extends BaseController
{
    /**
     * Toggle the status of a user (Activate / Block account).
     */
    public function toggleStatus(int $userId)
    {
        // Prevent self-lockout
        if (auth()->id() === $userId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan atau memblokir akun Anda sendiri!');
        }

        $userModel = auth()->getProvider();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Toggle 'active' status
        $newStatus = $user->active ? 0 : 1;
        $user->active = $newStatus;

        // Also update 'status' message if blocked
        if ($newStatus === 0) {
            $user->status = 'banned';
            $user->status_message = 'Akun Anda telah dinonaktifkan oleh Administrator.';
        } else {
            $user->status = null;
            $user->status_message = null;
        }

        if ($userModel->save($user)) {
            $message = $newStatus 
                ? "Akun pengguna '{$user->username}' berhasil diaktifkan kembali." 
                : "Akun pengguna '{$user->username}' berhasil dinonaktifkan (diblokir).";

            // Audit Log
            $action = $newStatus ? 'user_activated' : 'user_blocked';
            AuditLogModel::log($action, "Administrator " . ($newStatus ? "mengaktifkan" : "memblokir") . " akun '{$user->username}' (ID: {$userId})");

            return redirect()->to(base_url('admin'))->with('message', $message);
        }

        return redirect()->back()->with('error', 'Gagal memperbarui status pengguna. Silakan coba lagi.');
    }

    /**
     * Reset password of a specific user.
     */
    public function resetPassword(int $userId)
    {
        $password = $this->request->getPost('password');

        if (empty($password) || strlen($password) < 8) {
            return redirect()->back()->with('error', 'Kata sandi baru tidak boleh kosong dan minimal harus 8 karakter.');
        }

        $userModel = auth()->getProvider();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Set password and save
        $user->password = $password;

        if ($userModel->save($user)) {
            // Audit Log
            AuditLogModel::log('password_reset', "Administrator me-reset kata sandi pengguna '{$user->username}' (ID: {$userId})");

            return redirect()->to(base_url('admin'))->with('message', "Kata sandi untuk pengguna '{$user->username}' berhasil di-reset.");
        }

        return redirect()->back()->with('error', 'Gagal me-reset kata sandi. Silakan coba lagi.');
    }

    /**
     * Assign a specific Shield role to a user.
     */
    public function assignRole(int $userId)
    {
        // Prevent self role change
        if (auth()->id() === $userId) {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah peran/role Anda sendiri!');
        }

        $role = $this->request->getPost('role');
        $validRoles = ['user', 'manager', 'admin'];

        if (!in_array($role, $validRoles)) {
            return redirect()->back()->with('error', 'Peran yang dipilih tidak valid.');
        }

        $userModel = auth()->getProvider();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Shield role assignment: remove previous groups and add new one(s)
        $currentGroups = $user->getGroups();
        foreach ($currentGroups as $group) {
            $user->removeGroup($group);
        }
        
        if ($role === 'manager') {
            $user->addGroup('user');
            $user->addGroup('manager');
        } else {
            $user->addGroup($role);
        }

        // Audit Log
        AuditLogModel::log('role_changed', "Mengubah peran pengguna '{$user->username}' (ID: {$userId}) menjadi: " . ucfirst($role));

        return redirect()->to(base_url('admin'))->with('message', "Peran untuk pengguna '{$user->username}' berhasil diubah menjadi " . ucfirst($role) . ".");
    }
}
