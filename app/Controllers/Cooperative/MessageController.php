<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\KopAnggotaModel;
use App\Models\KopInvitationModel;
use App\Models\UserMessageModel;
use App\Models\AuditLogModel;

class MessageController extends BaseController
{
    protected UserModel $userModel;
    protected KopAnggotaModel $anggotaModel;
    protected KopInvitationModel $invitationModel;
    protected UserMessageModel $messageModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->anggotaModel = new KopAnggotaModel();
        $this->invitationModel = new KopInvitationModel();
        $this->messageModel = new UserMessageModel();
    }

    public function index()
    {
        $users = $this->userModel->orderBy('username', 'ASC')->findAll();
        $members = $this->anggotaModel->findAll();
        
        $memberIds = array_column($members, 'user_id');

        foreach ($users as &$user) {
            $user->is_member = in_array($user->id, $memberIds);
        }

        return view('admin/cooperative/messages', [
            'title' => 'Broadcast & Pesan Anggota',
            'users' => $users,
        ]);
    }

    public function broadcast()
    {
        $rules = [
            'user_ids' => 'required',
            'type'     => 'required|in_list[invitation,billing,system]',
            'subject'  => 'required|min_length[3]|max_length[255]',
            'message'  => 'required|min_length[10]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userIds = $this->request->getPost('user_ids');
        if (!is_array($userIds)) {
            $userIds = explode(',', $userIds);
        }
        
        if (empty($userIds)) {
            return redirect()->back()->withInput()->with('error', 'Pilih setidaknya satu pengguna.');
        }

        $type = $this->request->getPost('type');
        $subject = trim($this->request->getPost('subject'));
        $messageTemplate = trim($this->request->getPost('message'));
        $senderId = auth()->id();

        $db = \Config\Database::connect();
        $db->transStart();

        $successCount = 0;

        foreach ($userIds as $uid) {
            $user = $this->userModel->find($uid);
            if (!$user) continue;

            $invitationId = null;
            $finalMessage = str_replace('{nama}', $user->username, $messageTemplate);

            if ($type === 'invitation') {
                // Generate unique code
                $code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
                
                $invitationId = $this->invitationModel->insert([
                    'code'           => $code,
                    'email'          => $user->email,
                    'target_user_id' => $user->id,
                    'status'         => 'unused',
                    'created_by'     => $senderId,
                ], true);

                $magicLink = base_url('cooperative/magic-join/' . $code);
                $magicButton = '<div style="margin-top:16px;"><a href="' . $magicLink . '" style="display:inline-block;padding:10px 24px;background:#4f46e5;color:#fff;font-weight:700;border-radius:10px;text-decoration:none;">Tinjau Undangan</a></div>';
                
                // Replace placeholders if present
                if (strpos($finalMessage, '{kode_undangan}') !== false) {
                    $finalMessage = str_replace('{kode_undangan}', $code, $finalMessage);
                }

                if (strpos($finalMessage, '{magic_link}') !== false) {
                    $finalMessage = str_replace('{magic_link}', $magicButton, $finalMessage);
                } else {
                    // Auto-append magic link at the end
                    $finalMessage .= '<br><br><p style="font-size:13px;color:#94a3b8;">Kode Undangan: <strong style="color:#fff;">' . $code . '</strong></p>' . $magicButton;
                }
            }

            $this->messageModel->insert([
                'user_id'       => $user->id,
                'sender_id'     => $senderId,
                'invitation_id' => $invitationId,
                'subject'       => $subject,
                'message'       => $finalMessage,
                'type'          => $type,
                'is_read'       => 0,
            ]);

            $successCount++;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengirim pesan massal karena terjadi kesalahan sistem.');
        }

        AuditLogModel::log('coop_broadcast', "Admin mem-broadcast pesan tipe '{$type}' kepada {$successCount} pengguna.", $senderId);

        return redirect()->to(base_url('admin/cooperative/messages'))->with('message', "Berhasil mengirim pesan ke {$successCount} pengguna.");
    }
}
