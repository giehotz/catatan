<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AdminAuth extends BaseController
{
    /**
     * Render exclusive high-security Administrator Login Screen.
     */
    public function showLogin()
    {
        if (auth()->loggedIn()) {
            $user = auth()->user();
            if ($user->inGroup('admin') || $user->inGroup('superadmin')) {
                return redirect()->to(base_url('admin'));
            } elseif ($user->inGroup('manager')) {
                return redirect()->to(base_url('admin/cooperative'));
            }
            return redirect()->to(base_url('/'));
        }

        return view('admin/login', [
            'title' => 'Login Administrator'
        ]);
    }

    /**
     * Authenticate and authorize administrator credentials.
     */
    public function login()
    {
        if (auth()->loggedIn()) {
            $user = auth()->user();
            if ($user->inGroup('admin') || $user->inGroup('superadmin')) {
                return redirect()->to(base_url('admin'));
            } elseif ($user->inGroup('manager')) {
                return redirect()->to(base_url('admin/cooperative'));
            }
            return redirect()->to(base_url('/'));
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $credentials = [
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        // 1. Attempt standard credential check via Shield
        $result = auth()->attempt($credentials);
        if (!$result->isOK()) {
            return redirect()->back()->withInput()->with('error', $result->reason());
        }

        // 2. Enforce strict role validation immediately
        $user = auth()->user();
        if (!$user->inGroup('admin') && !$user->inGroup('superadmin') && !$user->inGroup('manager')) {
            auth()->logout(); // Sign out of the session immediately
            return redirect()->back()->withInput()->with('error', 'Akses ditolak. Kata sandi benar, namun akun Anda tidak memiliki hak akses Pengelola/Administrator.');
        }

        if ($user->inGroup('manager')) {
            return redirect()->to(base_url('admin/cooperative'))->with('message', 'Selamat datang kembali di Panel Pengelola Koperasi.');
        }

        return redirect()->to(base_url('admin'))->with('message', 'Selamat datang kembali di Panel Administrasi.');
    }
}
