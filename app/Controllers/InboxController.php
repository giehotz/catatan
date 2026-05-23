<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserMessageModel;
use App\Models\KopInvitationModel;

class InboxController extends BaseController
{
    protected UserMessageModel $messageModel;

    public function __construct()
    {
        $this->messageModel = new UserMessageModel();
    }

    public function index()
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->to(base_url('login'));
        }

        // Pagination setup: 10 per page
        $messages = $this->messageModel->where('user_id', $userId)
            ->where('deleted_by_receiver', 0)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
            
        $pager = $this->messageModel->pager;

        return view('user/inbox/index', [
            'title'    => 'Kotak Masuk',
            'messages' => $messages,
            'pager'    => $pager,
        ]);
    }

    public function show(int $id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->to(base_url('login'));
        }

        $message = $this->messageModel->where('id', $id)
            ->where('user_id', $userId)
            ->where('deleted_by_receiver', 0)
            ->first();

        if (!$message) {
            return redirect()->to(base_url('inbox'))->with('error', 'Pesan tidak ditemukan.');
        }

        // Mark as read
        if (!$message['is_read']) {
            $this->messageModel->update($id, ['is_read' => 1]);
        }

        return view('user/inbox/show', [
            'title'   => $message['subject'],
            'message' => $message,
        ]);
    }

    public function delete(int $id)
    {
        $userId = auth()->id();
        if (!$userId) {
            return redirect()->to(base_url('login'));
        }

        $message = $this->messageModel->where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$message) {
            return redirect()->to(base_url('inbox'))->with('error', 'Pesan tidak ditemukan.');
        }

        $this->messageModel->update($id, ['deleted_by_receiver' => 1]);

        return redirect()->to(base_url('inbox'))->with('message', 'Pesan berhasil dihapus.');
    }
}
