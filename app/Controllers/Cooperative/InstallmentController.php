<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopAngsuranModel;
use App\Models\KopPinjamanModel;
use App\Models\KopKasInternalModel;
use App\Models\KopAnggotaModel;
use App\Models\AuditLogModel;
use App\Models\KopAngsuranSubmissionModel;
use App\Services\InstallmentService;

class InstallmentController extends BaseController
{
    protected KopAngsuranModel $angsuranModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopKasInternalModel $kasInternalModel;
    protected KopAnggotaModel $anggotaModel;
    protected KopAngsuranSubmissionModel $submissionModel;
    protected InstallmentService $installmentService;

    public function __construct()
    {
        $this->angsuranModel = new KopAngsuranModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->kasInternalModel = new KopKasInternalModel();
        $this->anggotaModel = new KopAnggotaModel();
        $this->submissionModel = new KopAngsuranSubmissionModel();
        $this->installmentService = new InstallmentService();
    }

    /**
     * Manage installments verifications.
     */
    public function installments()
    {
        // Fetch from submissions instead of angsuran
        $installments = $this->submissionModel->select('kop_angsuran_submissions.*, users.username, kop_anggota.nomor_anggota, kop_pinjaman.nominal_pinjaman, kop_pinjaman.nominal_total as limit_amount, kop_pinjaman.tenor_bulan')
            ->join('kop_pinjaman', 'kop_pinjaman.id = kop_angsuran_submissions.pinjaman_id')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->orderBy('kop_angsuran_submissions.created_at', 'DESC')
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
            'nominal_bayar' => 'required|numeric|greater_than[0]',
            'tujuan_dana'   => 'required|in_list[kas_utama,dana_talangan]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $pinjamanId = intval($this->request->getPost('pinjaman_id'));
        $nominalBayar = floatval($this->request->getPost('nominal_bayar'));
        $tujuanDana = $this->request->getPost('tujuan_dana');

        // Check if loan exists and is active (approved)
        $loan = $this->pinjamanModel->find($pinjamanId);
        if (!$loan || $loan['status'] !== 'approved') {
            return redirect()->back()->withInput()->with('error', 'Pinjaman aktif tidak ditemukan.');
        }

        try {
            $this->installmentService->processAdminPayment($loan, $nominalBayar, $tujuanDana);
            
            $anggota = $this->anggotaModel->find($loan['anggota_id']);
            $user = auth()->getProvider()->find($anggota['user_id']);
            $username = $user ? $user->username : 'Unknown';
            
            return redirect()->to(base_url('admin/cooperative/installments'))->with('message', "Angsuran senilai Rp " . number_format($nominalBayar, 2, ',', '.') . " untuk anggota {$username} berhasil dicatat dan disahkan.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Approve a payment installment (with reconciliation sync to Debt & Receivable payments).
     */
    public function approveInstallment(int $id)
    {
        $tujuanDana = $this->request->getPost('tujuan_dana');
        if (!in_array($tujuanDana, ['kas_utama', 'dana_talangan'])) {
            return redirect()->back()->with('error', 'Tujuan dana kas tidak valid.');
        }

        try {
            $this->installmentService->approveSubmission($id, $tujuanDana);
            return redirect()->back()->with('message', 'Pengajuan cicilan berhasil disetujui dan didistribusikan ke ledger otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject a payment installment with reason.
     */
    public function rejectInstallment(int $id)
    {
        $submission = $this->submissionModel->find($id);
        if (!$submission || $submission['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Data pengajuan cicilan tidak ditemukan atau sudah diproses.');
        }

        $catatanTolak = trim($this->request->getPost('catatan_tolak') ?? '');
        if (empty($catatanTolak)) {
            return redirect()->back()->with('error', 'Catatan alasan penolakan wajib diisi.');
        }

        $this->submissionModel->update($id, [
            'status'        => 'rejected',
            'catatan_tolak' => $catatanTolak,
            'approved_by'   => auth()->id(),
            'approved_at'   => date('Y-m-d H:i:s'),
        ]);

        $loan = $this->pinjamanModel->find($submission['pinjaman_id']);
        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        AuditLogModel::log('coop_installment_rejected', "Menolak Pengajuan Angsuran Anggota '{$username}' senilai Rp " . number_format($submission['nominal_pengajuan'], 2) . ". Alasan: {$catatanTolak}");

        return redirect()->back()->with('message', 'Bukti angsuran ditolak. Alasan: ' . esc($catatanTolak));
    }

    public function printReceipt(int $submissionId)
    {
        $submission = $this->submissionModel->find($submissionId);
        if (!$submission || $submission['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Kuitansi tidak ditemukan atau belum disetujui.');
        }

        $loan = $this->pinjamanModel->find($submission['pinjaman_id']);
        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        // Fetch User logic inside ReceiptHelper can just use the loan data

        return \App\Helpers\ReceiptHelper::generateInstallmentReceiptPdf($submission, $loan, $anggota);
    }
}
