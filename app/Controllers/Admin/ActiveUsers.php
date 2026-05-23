<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Controller ActiveUsers
 * 
 * Memantau aktivitas pengguna secara real-time berbasis kolom last_active.
 * Prasyarat: Konfigurasi $recordActiveDate = true di app/Config/Auth.php sudah aktif.
 */
class ActiveUsers extends BaseController
{
    /**
     * Tampilkan halaman utama monitor pengguna aktif.
     */
    public function index()
    {
        // Double-check otorisasi
        if (!auth()->user()->inGroup('admin', 'superadmin')) {
            return redirect()->to(base_url('admin'))->with('error', 'Akses Ditolak.');
        }

        $usersData = $this->getActiveUsersData();

        return view('admin/active_users', array_merge([
            'title' => 'Monitor Pengguna Aktif',
        ], $usersData));
    }

    /**
     * Endpoint API AJAX untuk mendapatkan data real-time dalam format JSON.
     */
    public function data()
    {
        // Double-check otorisasi
        if (!auth()->user()->inGroup('admin', 'superadmin')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses Ditolak.']);
        }

        $usersData = $this->getActiveUsersData();

        return $this->response->setJSON($usersData);
    }

    /**
     * Mengambil dan memproses data pengguna serta menghitung statistik mutually exclusive.
     */
    private function getActiveUsersData()
    {
        $userModel = auth()->getProvider();
        
        // Ambil semua pengguna dari database
        $users = $userModel->orderBy('last_active', 'DESC')->findAll();

        $now = time();
        $onlineThreshold = 15 * 60; // 15 menit
        $todayStart = strtotime('today midnight');
        $weekStart  = $now - (7 * 24 * 3600);

        $onlineCount = 0;
        $todayCount = 0;
        $weekCount = 0;
        $inactiveCount = 0;

        $processedUsers = [];

        foreach ($users as $u) {
            $lastActiveStr = $u->last_active ? (string)$u->last_active : null;
            $la = $lastActiveStr ? strtotime($lastActiveStr) : null;
            
            // Hitung status secara mutually exclusive dan bertingkat
            if ($la === null) {
                $status = 'never';
                $inactiveCount++;
            } elseif (($now - $la) <= $onlineThreshold) {
                $status = 'online';
                $onlineCount++;
            } elseif ($la >= $todayStart) {
                $status = 'today';
                $todayCount++;
            } elseif ($la >= $weekStart) {
                $status = 'week';
                $weekCount++;
            } else {
                $status = 'inactive';
                $inactiveCount++;
            }

            // Tentukan inisial dan ketersediaan avatar
            $avatarUrl = null;
            if (!empty($u->avatar) && file_exists(FCPATH . 'uploads/avatars/' . $u->avatar)) {
                $avatarUrl = base_url('uploads/avatars/' . $u->avatar);
            }

            $processedUsers[] = [
                'id'            => $u->id,
                'username'      => (string) $u->username,
                'email'         => (string) $u->email,
                'role'          => esc($u->getRoleString()),
                'initials'      => esc($u->getInitials()),
                'avatar'        => $avatarUrl,
                'last_active'   => $lastActiveStr,
                'status'        => $status,
                'is_me'         => auth()->id() === $u->id,
            ];
        }

        return [
            'users'         => $processedUsers,
            'onlineCount'   => $onlineCount,
            'todayCount'    => $todayCount,
            'weekCount'     => $weekCount,
            'inactiveCount' => $inactiveCount,
            'totalCount'    => count($users),
        ];
    }
}
