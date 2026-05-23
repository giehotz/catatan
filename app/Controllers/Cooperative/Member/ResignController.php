<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopPengunduranDiriModel;
use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\AuditLogModel;
use App\Models\UserMessageModel;
use App\Traits\MemberTrait;

class ResignController extends BaseController
{
    use MemberTrait;

    protected KopPengunduranDiriModel $resignModel;
    protected KopAnggotaModel $anggotaModel;
    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected UserMessageModel $messageModel;

    public function __construct()
    {
        $this->resignModel = new KopPengunduranDiriModel();
        $this->anggotaModel = new KopAnggotaModel();
        $this->simpananModel = new KopSimpananModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
        $this->messageModel = new UserMessageModel();
    }

    /**
     * Resignation portal index page.
     */
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        // Get member and enforce they are active
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member; // RedirectResponse
        }

        $pendingRequest = $this->resignModel
            ->where('anggota_id', $member['id'])
            ->where('status', 'pending')
            ->first();

        // Calculate member savings balances
        $savings = $this->simpananModel->where('anggota_id', $member['id'])->where('status', 'approved')->findAll();
        $saldoPokok = 0;
        $saldoWajib = 0;
        $saldoSukarela = 0;

        foreach ($savings as $s) {
            $nominal = floatval($s['nominal']);
            if ($s['tipe_transaksi'] === 'setoran') {
                if ($s['jenis_simpanan'] === 'pokok') $saldoPokok += $nominal;
                if ($s['jenis_simpanan'] === 'wajib') $saldoWajib += $nominal;
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela += $nominal;
            } else {
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela -= $nominal;
                if ($s['jenis_simpanan'] === 'pokok') $saldoPokok -= $nominal;
                if ($s['jenis_simpanan'] === 'wajib') $saldoWajib -= $nominal;
            }
        }
        $totalSimpanan = $saldoPokok + $saldoWajib + $saldoSukarela;

        // Calculate member debts
        $activeLoans = $this->pinjamanModel->where('anggota_id', $member['id'])->whereIn('status', ['approved', 'paid'])->findAll();
        $totalDebtLimit = 0;
        $totalPaidInstallments = 0;

        foreach ($activeLoans as $l) {
            $totalDebtLimit += floatval($l['nominal_total']);
            $paid = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
            $totalPaidInstallments += floatval($paid);
        }
        $sisaPinjaman = $totalDebtLimit - $totalPaidInstallments;

        $history = $this->resignModel
            ->where('anggota_id', $member['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if ($pendingRequest) {
            return view('user/cooperative/resign/status', [
                'title'          => 'Status Pengajuan Keluar Keanggotaan',
                'member'         => $member,
                'pendingRequest' => $pendingRequest,
                'saldoPokok'     => $saldoPokok,
                'saldoWajib'     => $saldoWajib,
                'saldoSukarela'  => $saldoSukarela,
                'totalSimpanan'  => $totalSimpanan,
                'sisaPinjaman'   => $sisaPinjaman,
                'history'        => $history,
                'is_member'      => true,
            ]);
        }

        return view('user/cooperative/resign/form', [
            'title'          => 'Form Pengajuan Keluar Keanggotaan',
            'member'         => $member,
            'saldoPokok'     => $saldoPokok,
            'saldoWajib'     => $saldoWajib,
            'saldoSukarela'  => $saldoSukarela,
            'totalSimpanan'  => $totalSimpanan,
            'sisaPinjaman'   => $sisaPinjaman,
            'history'        => $history,
            'is_member'      => true,
        ]);
    }

    /**
     * Submit resignation request.
     */
    public function submit()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        // 1. Prevent duplicate pending requests
        $existing = $this->resignModel
            ->where('anggota_id', $member['id'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return redirect()->to(base_url('cooperative/resign'))->with('error', 'Anda sudah memiliki permohonan pengunduran diri yang berstatus pending.');
        }

        // Validate reason
        $rules = [
            'alasan_keluar' => 'required|min_length[10]|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $alasanKeluar = esc(trim($this->request->getPost('alasan_keluar')));

        // Insert new request
        $insertData = [
            'anggota_id'    => $member['id'],
            'status'        => 'pending',
            'alasan_keluar' => $alasanKeluar,
        ];

        if (!$this->resignModel->insert($insertData)) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengirimkan pengajuan pengunduran diri. Silakan coba lagi.');
        }

        $insertId = $this->resignModel->getInsertID();

        // 2. Notify all Admin/Managers
        $db = \Config\Database::connect();
        $adminUsers = $db->table('users')
            ->select('users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->whereIn('auth_groups_users.group', ['admin', 'manager', 'superadmin'])
            ->groupBy('users.id')
            ->get()
            ->getResultArray();

        $senderUsername = auth()->user()->username;
        $subject = 'Pengajuan Pengunduran Diri Baru: ' . $senderUsername;
        $message = "Yth. Pengurus,<br/><br/>Anggota atas nama <strong>" . esc($senderUsername) . "</strong> (No. Anggota: " . esc($member['nomor_anggota']) . ") telah mengajukan permohonan pengunduran diri dari keanggotaan koperasi.<br/><br/><strong>Alasan Pengunduran Diri:</strong><br/>" . nl2br(esc($alasanKeluar)) . "<br/><br/>Silakan masuk ke Panel Admin untuk meninjau status keuangan anggota ini dan memproses persetujuan atau penolakan pengajuan tersebut.<br/><br/>Salam,<br/>Sistem Koperasi";

        foreach ($adminUsers as $admin) {
            try {
                $this->messageModel->insert([
                    'user_id'             => $admin['id'],
                    'sender_id'           => auth()->id(),
                    'invitation_id'       => null,
                    'subject'             => $subject,
                    'message'             => $message,
                    'type'                => 'general',
                    'is_read'             => 0,
                    'action_taken'        => 0,
                    'deleted_by_sender'   => 0,
                    'deleted_by_receiver' => 0,
                ]);
            } catch (\Throwable $e) {
                log_message('error', "Gagal mengirimkan notifikasi resign admin {$admin['id']}: " . $e->getMessage());
            }
        }

        AuditLogModel::log('coop_resign_submitted', "Mengajukan pengunduran diri dari keanggotaan. ID Pengajuan: {$insertId}", auth()->id());

        return redirect()->to(base_url('cooperative/resign'))->with('message', 'Permohonan pengunduran diri Anda berhasil dikirimkan dan sedang menunggu peninjauan pengurus.');
    }

    /**
     * Cancel pending resignation request (Self-Ownership).
     */
    public function cancel(int $id)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $request = $this->resignModel->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Permohonan pengunduran diri tidak ditemukan.');
        }

        // Enforce strict ownership validation
        if ($request['anggota_id'] != $member['id']) {
            return redirect()->back()->with('error', 'Akses ditolak: Anda tidak memiliki otoritas untuk membatalkan pengajuan ini.');
        }

        if ($request['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Hanya permohonan dengan status PENDING yang dapat dibatalkan.');
        }

        if (!$this->resignModel->update($id, ['status' => 'cancelled'])) {
            return redirect()->back()->with('error', 'Gagal membatalkan permohonan. Silakan coba lagi.');
        }

        AuditLogModel::log('coop_resign_cancelled', "Membatalkan pengajuan pengunduran diri ID {$id}.", auth()->id());

        return redirect()->to(base_url('cooperative/resign'))->with('message', 'Permohonan pengunduran diri Anda berhasil dibatalkan secara mandiri.');
    }

    /**
     * Download formal letter (Approved Only).
     */
    public function downloadLetter(int $id)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $userId = auth()->id();
        $isAdmin = auth()->user()->inGroup('admin') || auth()->user()->inGroup('manager') || auth()->user()->inGroup('superadmin');

        $request = $this->resignModel->find($id);
        if (!$request) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Surat pengunduran diri tidak ditemukan.');
        }

        $anggotaModel = new KopAnggotaModel();
        $member = $anggotaModel->find($request['anggota_id']);
        if (!$member) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Anggota tidak valid.');
        }

        // Validate authorization: must be the owner OR an administrator
        if (!$isAdmin && $member['user_id'] != $userId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Anda tidak diizinkan untuk melihat dokumen ini.');
        }

        if ($request['status'] !== 'approved') {
            return redirect()->to(base_url('cooperative/resign'))->with('error', 'Surat pernyataan hanya tersedia untuk pengajuan yang telah disetujui.');
        }

        // Fetch User and Admin Info
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $member['user_id'])->get()->getRowArray();
        $admin = $request['processed_by'] ? $db->table('users')->where('id', $request['processed_by'])->get()->getRowArray() : null;

        return view('user/cooperative/resign/letter_template', [
            'request' => $request,
            'member'  => $member,
            'user'    => $user,
            'admin'   => $admin,
        ]);
    }

    /**
     * Public verification page (No auth required).
     */
    public function verifyPublic(string $hash)
    {
        $request = $this->resignModel->where('hash_verifikasi', $hash)->first();

        if (!$request || $request['status'] !== 'approved') {
            return view('user/cooperative/resign/verify_failed', [
                'title' => 'Verifikasi Gagal - Dokumen Tidak Valid',
                'hash'  => $hash
            ]);
        }

        $member = $this->anggotaModel->find($request['anggota_id']);
        if (!$member) {
            return view('user/cooperative/resign/verify_failed', [
                'title' => 'Verifikasi Gagal - Anggota Tidak Ditemukan',
                'hash'  => $hash
            ]);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $member['user_id'])->get()->getRowArray();
        $admin = $request['processed_by'] ? $db->table('users')->where('id', $request['processed_by'])->get()->getRowArray() : null;

        return view('user/cooperative/resign/verify_success', [
            'title'   => 'Verifikasi Keaslian Surat Pengunduran Diri',
            'request' => $request,
            'member'  => $member,
            'user'    => $user,
            'admin'   => $admin,
        ]);
    }
}
