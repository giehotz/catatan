<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopInvitationModel;
use App\Models\AuditLogModel;

class InvitationController extends BaseController
{
    protected KopInvitationModel $invitationModel;

    public function __construct()
    {
        $this->invitationModel = new KopInvitationModel();
    }

    /**
     * Invitation code generator and list.
     */
    public function invitations()
    {
        $invitations = $this->invitationModel->select('kop_invitations.*, creator.username as creator_name, user.username as user_name')
            ->join('users creator', 'creator.id = kop_invitations.created_by')
            ->join('users user', 'user.id = kop_invitations.used_by', 'left')
            ->orderBy('kop_invitations.created_at', 'DESC')
            ->findAll();

        return view('admin/cooperative/invitations', [
            'title'       => 'Panel Koperasi - Kode Undangan',
            'invitations' => $invitations,
        ]);
    }

    /**
     * Generate a new Invitation Code.
     */
    public function generateInvitation()
    {
        $email = $this->request->getPost('email') ?: null;
        $customCode = $this->request->getPost('code');

        if (!empty($customCode)) {
            $code = strtoupper(trim($customCode));
            // Validate code length/format
            if (strlen($code) < 4 || strlen($code) > 20) {
                return redirect()->back()->withInput()->with('error', 'Kode undangan harus terdiri dari 4 hingga 20 karakter.');
            }
            // Check uniqueness
            if ($this->invitationModel->where('code', $code)->countAllResults() > 0) {
                return redirect()->back()->withInput()->with('error', "Kode undangan '{$code}' sudah terdaftar.");
            }
        } else {
            // Generate unique invitation code: KOP-[6-RANDOM-CHARACTERS]
            $unique = false;
            $code = '';
            while (!$unique) {
                $code = 'KOP-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
                if ($this->invitationModel->where('code', $code)->countAllResults() === 0) {
                    $unique = true;
                }
            }
        }

        $this->invitationModel->insert([
            'code'       => $code,
            'email'      => $email,
            'status'     => 'unused',
            'created_by' => auth()->id(),
        ]);

        AuditLogModel::log('coop_invitation_created', "Membuat kode undangan baru '{$code}'" . ($email ? " untuk {$email}" : ""));

        return redirect()->to(base_url('admin/cooperative/invitations'))->with('message', "Kode undangan '{$code}' berhasil dibuat.");
    }

    /**
     * Disable/delete an unused invitation code.
     */
    public function deleteInvitation(int $id)
    {
        $invitation = $this->invitationModel->find($id);
        if (!$invitation || $invitation['status'] !== 'unused') {
            return redirect()->back()->with('error', 'Kode undangan tidak ditemukan atau sudah digunakan.');
        }

        $this->invitationModel->delete($id);

        AuditLogModel::log('coop_invitation_deleted', "Menghapus kode undangan '{$invitation['code']}'");

        return redirect()->back()->with('message', 'Kode undangan berhasil dihapus.');
    }
}
