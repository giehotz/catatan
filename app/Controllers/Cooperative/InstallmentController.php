<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopAngsuranModel;
use App\Models\KopPinjamanModel;
use App\Models\KopKasInternalModel;
use App\Models\KopAnggotaModel;
use App\Models\AuditLogModel;

class InstallmentController extends BaseController
{
    protected KopAngsuranModel $angsuranModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopKasInternalModel $kasInternalModel;
    protected KopAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->angsuranModel = new KopAngsuranModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->kasInternalModel = new KopKasInternalModel();
        $this->anggotaModel = new KopAnggotaModel();
    }

    /**
     * Manage installments verifications.
     */
    public function installments()
    {
        $installments = $this->angsuranModel->select('kop_angsuran.*, users.username, kop_anggota.nomor_anggota, kop_pinjaman.nominal_pinjaman, kop_pinjaman.nominal_total as limit_amount, kop_pinjaman.tenor_bulan')
            ->join('kop_pinjaman', 'kop_pinjaman.id = kop_angsuran.pinjaman_id')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->orderBy('kop_angsuran.tanggal_bayar', 'DESC')
            ->findAll();

        $activeLoans = $this->pinjamanModel->select('kop_pinjaman.*, users.username, kop_anggota.nomor_anggota')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('kop_pinjaman.status', 'approved')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view('admin/cooperative/installments', [
            'title'        => 'Panel Koperasi - Bukti Angsuran',
            'installments' => $installments,
            'activeLoans'  => $activeLoans,
        ]);
    }

    /**
     * Store a manually entered installment from manager/admin.
     */
    public function storeInstallment()
    {
        $rules = [
            'pinjaman_id'   => 'required|numeric',
            'angsuran_ke'   => 'required|integer|greater_than[0]',
            'nominal_bayar' => 'required|numeric|greater_than[0]',
            'tanggal_bayar' => 'required|valid_date[Y-m-d]',
            'bukti_bayar'   => 'permit_empty|is_image[bukti_bayar]|max_size[bukti_bayar,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pinjamanId = intval($this->request->getPost('pinjaman_id'));
        $angsuranKe = intval($this->request->getPost('angsuran_ke'));
        $nominalBayar = floatval($this->request->getPost('nominal_bayar'));
        $tanggalBayar = $this->request->getPost('tanggal_bayar');

        // Check if loan exists and is active (approved)
        $loan = $this->pinjamanModel->find($pinjamanId);
        if (!$loan || $loan['status'] !== 'approved') {
            return redirect()->back()->withInput()->with('error', 'Pinjaman aktif tidak ditemukan.');
        }

        // Prevent double submit for the exact same installment number if pending/approved
        $exist = $this->angsuranModel->where('pinjaman_id', $pinjamanId)->where('angsuran_ke', $angsuranKe)->whereIn('status', ['pending', 'approved'])->countAllResults();
        if ($exist > 0) {
            return redirect()->back()->withInput()->with('error', "Pembayaran untuk Angsuran ke-{$angsuranKe} sudah tercatat sebelumnya.");
        }

        $file = $this->request->getFile('bukti_bayar');
        $newName = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/bukti_cicilan')) {
                mkdir(FCPATH . 'uploads/bukti_cicilan', 0777, true);
            }
            $file->move(FCPATH . 'uploads/bukti_cicilan', $newName);
        }

        $this->angsuranModel->insert([
            'pinjaman_id'   => $pinjamanId,
            'angsuran_ke'   => $angsuranKe,
            'nominal_bayar' => $nominalBayar,
            'bukti_bayar'   => $newName,
            'status'        => 'pending',
            'tanggal_bayar' => date('Y-m-d H:i:s', strtotime($tanggalBayar . ' ' . date('H:i:s'))),
        ]);

        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        AuditLogModel::log('coop_installment_logged', "Mencatat manual Angsuran ke-{$angsuranKe} Anggota '{$username}' senilai Rp " . number_format($nominalBayar, 2) . " (Status: Pending)");

        return redirect()->to(base_url('admin/cooperative/installments'))->with('message', "Angsuran ke-{$angsuranKe} untuk anggota {$username} berhasil dicatat dengan status pending. Silakan sahkan di tabel antrean untuk menyelesaikan pembukuan.");
    }

    /**
     * Approve a payment installment (with reconciliation sync to Debt & Receivable payments).
     */
    public function approveInstallment(int $id)
    {
        $installment = $this->angsuranModel->find($id);
        if (!$installment || $installment['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Data cicilan tidak ditemukan atau sudah diproses.');
        }

        $tujuanDana = $this->request->getPost('tujuan_dana');
        if (!in_array($tujuanDana, ['kas_utama', 'dana_talangan'])) {
            return redirect()->back()->with('error', 'Tujuan dana kas tidak valid.');
        }

        // Approve installment row
        $this->angsuranModel->update($id, [
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $loan = $this->pinjamanModel->find($installment['pinjaman_id']);
        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        // 0. Catat Pemasukan Dana ke Kas Koperasi
        $this->kasInternalModel->insert([
            'kategori_dana'   => $tujuanDana,
            'jenis_transaksi' => 'pemasukan',
            'nominal'         => $installment['nominal_bayar'],
            'reference_type'  => 'angsuran',
            'reference_id'    => $id,
            'keterangan'      => "Penerimaan Angsuran ke-{$installment['angsuran_ke']} dari '{$username}'",
            'created_by'      => auth()->id(),
        ]);

        // 1. Double Sync: Log payment inside standard DEBT PAYMENTS (Utang Anggota)
        $db = \Config\Database::connect();
        if (!empty($loan['debt_id_fk'])) {
            $db->table('debt_payments')->insert([
                'debt_id'      => $loan['debt_id_fk'],
                'amount'       => $installment['nominal_bayar'],
                'payment_date' => date('Y-m-d', strtotime($installment['tanggal_bayar'])),
                'note'         => "Angsuran Koperasi ke-{$installment['angsuran_ke']}",
            ]);
        }

        // 2. Double Sync: Log payment inside standard RECEIVABLE PAYMENTS (Piutang Koperasi)
        if (!empty($loan['receivable_id_fk'])) {
            $db->table('receivable_payments')->insert([
                'receivable_id' => $loan['receivable_id_fk'],
                'amount'        => $installment['nominal_bayar'],
                'payment_date'  => date('Y-m-d', strtotime($installment['tanggal_bayar'])),
                'note'          => "Terima Angsuran Koperasi ke-{$installment['angsuran_ke']} (Anggota: {$username})",
            ]);
        }

        // 3. Balance Reconciliation & status updates for debts & receivables
        $totalApprovedInstallments = $this->angsuranModel->where('pinjaman_id', $loan['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
        
        $newStatus = 'unpaid';
        if ($totalApprovedInstallments >= $loan['nominal_total']) {
            $newStatus = 'paid';
            $this->pinjamanModel->update($loan['id'], ['status' => 'paid']);
        } elseif ($totalApprovedInstallments > 0) {
            $newStatus = 'partial';
        }

        // Update corresponding records in main tables
        if (!empty($loan['debt_id_fk'])) {
            $db->table('debts')->where('id', $loan['debt_id_fk'])->update(['status' => $newStatus]);
        }
        if (!empty($loan['receivable_id_fk'])) {
            $db->table('receivables')->where('id', $loan['receivable_id_fk'])->update(['status' => $newStatus]);
        }

        AuditLogModel::log('coop_installment_approved', "Menyetujui Angsuran ke-{$installment['angsuran_ke']} Anggota '{$username}' senilai Rp " . number_format($installment['nominal_bayar'], 2) . ". Rekonsiliasi ledger status: " . strtoupper($newStatus));

        return redirect()->back()->with('message', 'Cicilan angsuran berhasil disetujui. Nominal sisa utang & piutang ter-rekon otomatis.');
    }

    /**
     * Reject a payment installment with reason.
     */
    public function rejectInstallment(int $id)
    {
        $installment = $this->angsuranModel->find($id);
        if (!$installment || $installment['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Data cicilan tidak ditemukan atau sudah diproses.');
        }

        $catatanTolak = trim($this->request->getPost('catatan_tolak') ?? '');
        if (empty($catatanTolak)) {
            return redirect()->back()->with('error', 'Catatan alasan penolakan wajib diisi.');
        }

        $this->angsuranModel->update($id, [
            'status'        => 'rejected',
            'catatan_tolak' => $catatanTolak,
            'approved_by'   => auth()->id(),
            'approved_at'   => date('Y-m-d H:i:s'),
        ]);

        $loan = $this->pinjamanModel->find($installment['pinjaman_id']);
        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        AuditLogModel::log('coop_installment_rejected', "Menolak Angsuran ke-{$installment['angsuran_ke']} Anggota '{$username}' senilai Rp " . number_format($installment['nominal_bayar'], 2) . ". Alasan: {$catatanTolak}");

        return redirect()->back()->with('message', 'Bukti angsuran cicilan ditolak. Alasan: ' . esc($catatanTolak));
    }
}
