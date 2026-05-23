<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopInvitationModel;
use App\Models\KopAnggotaModel;
use App\Models\AuditLogModel;
use App\Models\UserMessageModel;

class ActivationController extends BaseController
{
    protected KopInvitationModel $invitationModel;
    protected KopAnggotaModel $anggotaModel;
    protected UserMessageModel $messageModel;

    public function __construct()
    {
        $this->invitationModel = new KopInvitationModel();
        $this->anggotaModel = new KopAnggotaModel();
        $this->messageModel = new UserMessageModel();
    }

    /**
     * Show join form.
     */
    public function joinForm()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $userId = auth()->id();
        $member = $this->anggotaModel->where('user_id', $userId)->first();
        if ($member) {
            return redirect()->to(base_url('cooperative'));
        }

        return view('user/cooperative/join', [
            'title'     => 'Koperasi - Aktivasi Anggota',
            'is_member' => false,
        ]);
    }

    /**
     * Join/activate cooperative membership using Invitation Code.
     */
    public function processJoin()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $rules = [
            'invitation_code' => 'required|min_length[10]|max_length[20]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = esc(trim($this->request->getPost('invitation_code')));

        // Find unused code
        $invitation = $this->invitationModel->where('code', $code)->where('status', 'unused')->first();
        if (!$invitation) {
            return redirect()->back()->withInput()->with('error', 'Kode undangan tidak valid, sudah kadaluarsa, atau telah digunakan.');
        }

        $userId = auth()->id();

        // Prevent double membership
        if ($this->anggotaModel->where('user_id', $userId)->countAllResults() > 0) {
            return redirect()->to(base_url('cooperative'))->with('error', 'Anda sudah menjadi anggota aktif koperasi.');
        }

        // Generate official member number: KOP/[YYYYMM]/[USERID_PADDED]
        $nomorAnggota = 'KOP/' . date('Ym') . '/' . str_pad($userId, 4, '0', STR_PAD_LEFT);

        // Save membership
        $this->anggotaModel->insert([
            'user_id'          => $userId,
            'nomor_anggota'    => $nomorAnggota,
            'status_keaktifan' => 'aktif',
        ]);

        // Invalidasi dan perbarui shared cache secara real-time
        session()->remove('coop_member_active');
        session()->remove('coop_member_active_expires');
        cache()->save("coop_member_active_{$userId}", 1, 300);

        // Consume invitation code
        $this->invitationModel->update($invitation['id'], [
            'status'  => 'used',
            'used_by' => $userId,
            'used_at' => date('Y-m-d H:i:s'),
        ]);

        AuditLogModel::log('coop_member_joined', "Pengguna bergabung ke koperasi dengan kode '{$code}'. Nomor Anggota baru: '{$nomorAnggota}'", $userId);

        return redirect()->to(base_url('cooperative'))->with('message', "Selamat! Keanggotaan Koperasi Anda berhasil diaktifkan dengan Nomor Anggota: {$nomorAnggota}.");
    }

    /**
     * Show magic link join confirmation
     */
    public function magicLink(string $code)
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan login terlebih dahulu untuk mengakses Magic Link.');
        }

        $invitation = $this->invitationModel->where('code', $code)->first();
        if (!$invitation) {
            return redirect()->to(base_url('inbox'))->with('error', 'Kode undangan tidak valid.');
        }

        if ($invitation['status'] !== 'unused') {
            return redirect()->to(base_url('inbox'))->with('error', 'Kode undangan ini sudah kadaluarsa atau pernah digunakan.');
        }

        if ($invitation['target_user_id'] != $userId) {
            return redirect()->to(base_url('inbox'))->with('error', 'Undangan ini tidak ditujukan untuk Anda.');
        }

        return view('user/cooperative/magic_join', [
            'title'      => 'Konfirmasi Undangan Koperasi',
            'invitation' => $invitation,
        ]);
    }

    /**
     * Reject a magic link invitation
     */
    public function rejectInvitation(string $code)
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->to(base_url('login'));
        }

        $invitation = $this->invitationModel->where('code', $code)->first();
        if (!$invitation || $invitation['target_user_id'] != $userId) {
            return redirect()->to(base_url('inbox'))->with('error', 'Akses ditolak.');
        }

        // Update invitation status
        $this->invitationModel->update($invitation['id'], [
            'status' => 'rejected'
        ]);

        // Update message action_taken
        $msg = $this->messageModel->where('invitation_id', $invitation['id'])->where('user_id', $userId)->first();
        if ($msg) {
            $this->messageModel->update($msg['id'], ['action_taken' => 'rejected']);
        }

        AuditLogModel::log('coop_invitation_rejected', "Pengguna menolak undangan koperasi.", $userId);

        return redirect()->to(base_url('inbox'))->with('message', 'Anda telah menolak undangan koperasi tersebut.');
    }
}
