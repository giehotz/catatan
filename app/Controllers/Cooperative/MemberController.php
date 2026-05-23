<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopAnggotaModel;
use App\Models\AuditLogModel;

class MemberController extends BaseController
{
    protected KopAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->anggotaModel = new KopAnggotaModel();
    }

    /**
     * Members directory management page.
     */
    public function members()
    {
        $members = $this->anggotaModel->select('kop_anggota.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->orderBy('kop_anggota.created_at', 'DESC')
            ->findAll();

        return view('admin/cooperative/members', [
            'title'   => 'Panel Koperasi - Anggota',
            'members' => $members,
        ]);
    }

    /**
     * Toggle a member's active status.
     */
    public function toggleMemberStatus(int $id)
    {
        $member = $this->anggotaModel->find($id);
        if (!$member) {
            return redirect()->back()->with('error', 'Anggota tidak ditemukan.');
        }

        $newStatus = $member['status_keaktifan'] === 'aktif' ? 'ditangguhkan' : 'aktif';
        $this->anggotaModel->update($id, ['status_keaktifan' => $newStatus]);

        // Invalidasi shared cache milik anggota secara instan untuk sinkronisasi UI seketika
        cache()->delete("coop_member_active_{$member['user_id']}");

        // Audit Log
        $userModel = auth()->getProvider();
        $user = $userModel->find($member['user_id']);
        $username = $user ? $user->username : 'Unknown';
        AuditLogModel::log('coop_member_status', "Mengubah status anggota '{$username}' (ID Koperasi: {$id}) menjadi: " . strtoupper($newStatus));

        return redirect()->back()->with('message', "Status keanggotaan '{$username}' berhasil diubah menjadi: " . ucfirst($newStatus) . ".");
    }
}
