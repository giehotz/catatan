<?php

namespace App\Traits;

use App\Models\KopAnggotaModel;

trait MemberTrait
{
    /**
     * Helper to verify if the user is a registered member.
     * Returns member array if valid, or a RedirectResponse if not.
     */
    protected function getMemberOrRedirect()
    {
        $userId = auth()->id();
        $anggotaModel = new KopAnggotaModel();
        $member = $anggotaModel->where('user_id', $userId)->first();
        
        if (!$member) {
            // Redirect to dashboard where activation form is rendered
            return redirect()->to(base_url('cooperative/join'))->with('error', 'Anda belum terdaftar sebagai anggota koperasi. Silakan gunakan kode undangan.');
        }
        
        if ($member['status_keaktifan'] !== 'aktif') {
            return redirect()->to(base_url('cooperative/join'))->with('error', 'Keanggotaan Anda sedang ditangguhkan. Hubungi pengelola.');
        }
        
        return $member;
    }
}
