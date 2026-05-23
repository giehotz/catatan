<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\DebtModel;
use App\Models\ReceivableModel;

class Profile extends BaseController
{
    protected TransactionModel $transactionModel;
    protected DebtModel $debtModel;
    protected ReceivableModel $receivableModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->debtModel = new DebtModel();
        $this->receivableModel = new ReceivableModel();
    }

    public function index()
    {
        $userId = auth()->id();
        $user = auth()->user();

        // Calculate some fun user statistics for a premium profile page
        $totalTransactions = $this->transactionModel->where('user_id', $userId)->countAllResults();
        $totalIncomeTx = $this->transactionModel->where('user_id', $userId)->where('type', 'income')->countAllResults();
        $totalExpenseTx = $this->transactionModel->where('user_id', $userId)->where('type', 'expense')->countAllResults();

        $totalDebts = $this->debtModel->where('user_id', $userId)->countAllResults();
        $totalReceivables = $this->receivableModel->where('user_id', $userId)->countAllResults();

        return view('user/profile/index', [
            'title'             => 'Profil Saya',
            'user'              => $user,
            'totalTransactions' => $totalTransactions,
            'totalIncomeTx'     => $totalIncomeTx,
            'totalExpenseTx'    => $totalExpenseTx,
            'totalDebts'        => $totalDebts,
            'totalReceivables'  => $totalReceivables,
        ]);
    }

    public function update()
    {
        $userId = auth()->id();
        $users = auth()->getProvider();
        $user = auth()->user();

        // 1. Validation Rules
        $rules = [
            'username' => "required|alpha_numeric_space|min_length[3]|max_length[30]|is_unique[users.username,id,{$userId}]",
        ];

        $changePassword = !empty($this->request->getPost('password'));
        if ($changePassword) {
            $rules['password'] = 'required|min_length[8]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        $avatarFile = $this->request->getFile('avatar');
        $uploadedAvatar = $avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved();
        if ($uploadedAvatar) {
            $rules['avatar'] = 'uploaded[avatar]|is_image[avatar]|max_size[avatar,2048]|ext_in[avatar,png,jpg,jpeg,webp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // 2. Handle Avatar File Upload
        $avatarName = $user->avatar;
        if ($uploadedAvatar) {
            // Delete old avatar if exists
            if (!empty($avatarName) && file_exists(FCPATH . 'uploads/avatars/' . $avatarName)) {
                @unlink(FCPATH . 'uploads/avatars/' . $avatarName);
            }

            // Move new avatar
            $avatarName = $avatarFile->getRandomName();
            $avatarFile->move(FCPATH . 'uploads/avatars', $avatarName);
        }

        // 3. Perform Update
        $user->fill([
            'username'         => $this->request->getPost('username'),
            'avatar'           => $avatarName,
            'theme_preference' => $this->request->getPost('theme_preference') ?? $user->theme_preference ?? 'system',
        ]);

        if ($changePassword) {
            $user->password = $this->request->getPost('password');
        }

        if ($users->save($user)) {
            return redirect()->to('profile')->with('message', 'Profil Anda berhasil diperbarui!');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
    }

    public function deleteAvatar()
    {
        $users = auth()->getProvider();
        $user = auth()->user();
        $avatarName = $user->avatar;

        if (!empty($avatarName)) {
            // Delete file if exists
            if (file_exists(FCPATH . 'uploads/avatars/' . $avatarName)) {
                @unlink(FCPATH . 'uploads/avatars/' . $avatarName);
            }

            // Save null to database
            $user->avatar = null;
            if ($users->save($user)) {
                return redirect()->to('profile')->with('message', 'Foto profil berhasil dihapus!');
            }
        }

        return redirect()->to('profile')->with('error', 'Tidak ada foto profil untuk dihapus.');
    }

    public function updateTheme()
    {
        // CSRF/AJAX validation or normal JSON
        if (!$this->request->isAJAX()) {
            // Support fallback to standard form redirect just in case
            $theme = $this->request->getPost('theme_preference');
            if ($theme && in_array($theme, ['light', 'dark', 'system'], true)) {
                $users = auth()->getProvider();
                $user = auth()->user();
                $user->theme_preference = $theme;
                $users->save($user);
            }
            return redirect()->to('profile');
        }

        $json = $this->request->getJSON(true);
        $theme = $json['theme_preference'] ?? $this->request->getPost('theme_preference');

        if (!$theme || !in_array($theme, ['light', 'dark', 'system'], true)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Pilihan tema tidak valid.'
            ])->setStatusCode(400);
        }

        $users = auth()->getProvider();
        $user = auth()->user();
        $user->theme_preference = $theme;

        if ($users->save($user)) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Preferensi tema berhasil disimpan ke server.'
            ]);
        }

        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'Gagal menyimpan preferensi tema ke server.'
        ])->setStatusCode(500);
    }
}
