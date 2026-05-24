<?php

namespace App\Services;

use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopAngsuranSubmissionModel;
use App\Models\KopKasInternalModel;
use App\Models\KopAnggotaModel;
use App\Models\DebtModel;
use App\Models\ReceivableModel;
use App\Models\AuditLogModel;

class InstallmentService
{
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected KopAngsuranSubmissionModel $submissionModel;
    protected KopKasInternalModel $kasModel;
    protected LoanAmortizationService $amortizationService;

    public function __construct()
    {
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
        $this->submissionModel = new KopAngsuranSubmissionModel();
        $this->kasModel = new KopKasInternalModel();
        $this->amortizationService = new LoanAmortizationService();
    }

    /**
     * Submit payment from User (Creates a pending submission)
     */
    public function submitUserPayment(array $loan, float $amount, string $buktiName): void
    {
        $this->submissionModel->insert([
            'pinjaman_id'       => $loan['id'],
            'nominal_pengajuan' => $amount,
            'bukti_bayar'       => $buktiName,
            'status'            => 'pending'
        ]);
    }

    /**
     * Process Admin direct payment
     */
    public function processAdminPayment(array $loan, float $amount, string $tujuanDana): void
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Create a dummy submission so the user can see it in their history and print receipts
            $submissionId = $this->submissionModel->insert([
                'pinjaman_id'       => $loan['id'],
                'nominal_pengajuan' => $amount,
                'bukti_bayar'       => null,
                'source'            => 'admin',
                'status'            => 'approved',
                'approved_by'       => auth()->id(),
                'approved_at'       => date('Y-m-d H:i:s'),
                'catatan_tolak'     => 'Disetor langsung via Admin',
            ], true); // return ID

            $this->distributePaymentToLedger($loan, $amount, $tujuanDana, $submissionId, 'Pembayaran Manual Admin');
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception("Gagal menyimpan transaksi ke dalam database.");
            }
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Approve pending submission from User
     */
    public function approveSubmission(int $submissionId, string $tujuanDana): void
    {
        $submission = $this->submissionModel->find($submissionId);
        if (!$submission || $submission['status'] !== 'pending') {
            throw new \Exception("Pengajuan cicilan tidak ditemukan atau sudah diproses.");
        }

        $loan = $this->pinjamanModel->find($submission['pinjaman_id']);
        if (!$loan) {
            throw new \Exception("Pinjaman tidak ditemukan.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Update submission status
            $this->submissionModel->update($submissionId, [
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

            // Distribute amount to ledger
            $amount = floatval($submission['nominal_pengajuan']);
            $this->distributePaymentToLedger($loan, $amount, $tujuanDana, $submissionId, 'Validasi Bukti Bayar User');

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception("Gagal menyetujui cicilan. Rollback dieksekusi.");
            }
        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Core logic: Distribute nominal amount into Angsuran Ke-X
     * and sync double entry ledgers (Kas, Debt, Receivable)
     */
    protected function distributePaymentToLedger(array $loan, float $amount, string $tujuanDana, ?int $submissionId, string $context): void
    {
        if ($amount <= 0) {
            throw new \Exception("Nominal pembayaran harus lebih dari 0.");
        }

        // 1. Dapatkan daftar cicilan
        $installmentRecords = $this->angsuranModel
            ->where('pinjaman_id', $loan['id'])
            ->orderBy('angsuran_ke', 'ASC')
            ->findAll();

        $installmentMap = [];
        foreach ($installmentRecords as $inst) {
            $installmentMap[(int)$inst['angsuran_ke']] = $inst;
        }
        $paidMap = array_filter($installmentMap, fn($i) => $i['status'] === 'approved');

        // Gunakan LoanAmortizationService untuk mendapatkan target per bulan
        $schedule = $this->amortizationService->buildScheduleWithRecords($loan, $installmentMap);
        $summary = $this->amortizationService->calculateLoanSummary($loan, $paidMap);

        if ($amount > floatval($summary['sisa_tagihan'])) {
            throw new \Exception("Nominal pembayaran (Rp " . number_format($amount, 2) . ") melebihi sisa tagihan pinjaman (Rp " . number_format($summary['sisa_tagihan'], 2) . ").");
        }

        $remainingAmount = $amount;
        $insertedCount = 0;

        foreach ($schedule as $entry) {
            if ($remainingAmount <= 0) {
                break;
            }

            $angsuranKe = (int)$entry['angsuran_ke'];
            $targetBulanan = floatval($entry['total']);
            
            // Cek apakah bulan ini sudah ada pembayaran sah
            $sudahDibayarBulanIni = 0;
            if (isset($paidMap[$angsuranKe])) {
                // Di sini kita simplifikasi: kalau di paidMap ada baris untuk angsuran_ke,
                // kita harusnya sum(nominal_bayar) untuk angsuran_ke tersebut.
                // Tapi model lama hanya 1 baris = 1 angsuran.
                // Dengan sistem baru, 1 angsuran_ke bisa punya banyak baris di kop_angsuran.
            }
            
            // Aggregate all approved nominal_bayar for this angsuran_ke
            $totalSudahBayarBulanIni = 0;
            foreach ($paidMap as $paidRecord) {
                if ($paidRecord['angsuran_ke'] == $angsuranKe) {
                    $totalSudahBayarBulanIni += floatval($paidRecord['nominal_bayar']);
                }
            }

            $sisaTunggakanBulanIni = $targetBulanan - $totalSudahBayarBulanIni;

            if ($sisaTunggakanBulanIni > 0) {
                $allocate = min($sisaTunggakanBulanIni, $remainingAmount);

                $this->angsuranModel->insert([
                    'pinjaman_id'      => $loan['id'],
                    'submission_id_fk' => $submissionId,
                    'angsuran_ke'      => $angsuranKe,
                    'nominal_bayar'    => $allocate,
                    'status'           => 'approved',
                    'tanggal_bayar'    => date('Y-m-d H:i:s'),
                    'approved_by'      => auth()->id(),
                    'approved_at'      => date('Y-m-d H:i:s')
                ]);

                $remainingAmount -= $allocate;
                $insertedCount++;
            }
        }

        // 2. Catat Kas Masuk Koperasi
        $anggotaModel = new KopAnggotaModel();
        $anggota = $anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        $this->kasModel->insert([
            'kategori_dana'   => $tujuanDana,
            'jenis_transaksi' => 'pemasukan',
            'nominal'         => $amount,
            'reference_type'  => 'angsuran',
            'reference_id'    => $loan['id'],
            'keterangan'      => "Penerimaan Angsuran dari '{$username}' ({$context})",
            'created_by'      => auth()->id(),
        ]);

        // 3. Update Utang/Piutang
        $db = \Config\Database::connect();
        
        if (!empty($loan['debt_id_fk'])) {
            $db->table('debt_payments')->insert([
                'debt_id'      => $loan['debt_id_fk'],
                'amount'       => $amount,
                'payment_date' => date('Y-m-d'),
                'note'         => "Angsuran Koperasi",
            ]);
        }

        if (!empty($loan['receivable_id_fk'])) {
            $db->table('receivable_payments')->insert([
                'receivable_id' => $loan['receivable_id_fk'],
                'amount'        => $amount,
                'payment_date'  => date('Y-m-d'),
                'note'          => "Terima Angsuran Koperasi (Anggota: {$username})",
            ]);
        }

        // 4. Update Status Pinjaman
        $totalApproved = $this->angsuranModel
            ->where('pinjaman_id', $loan['id'])
            ->where('status', 'approved')
            ->selectSum('nominal_bayar')
            ->first()['nominal_bayar'] ?? 0;
            
        $newStatus = 'unpaid';
        if ($totalApproved >= $loan['nominal_total']) {
            $newStatus = 'paid';
            $this->pinjamanModel->update($loan['id'], ['status' => 'paid']);
        } elseif ($totalApproved > 0) {
            $newStatus = 'partial';
        }

        if (!empty($loan['debt_id_fk'])) {
            $db->table('debts')->where('id', $loan['debt_id_fk'])->update(['status' => $newStatus]);
        }
        if (!empty($loan['receivable_id_fk'])) {
            $db->table('receivables')->where('id', $loan['receivable_id_fk'])->update(['status' => $newStatus]);
        }

        AuditLogModel::log('coop_installment_batch', "Menerima total angsuran Rp " . number_format($amount, 2) . " dari Anggota '{$username}'. Terdistribusi ke {$insertedCount} cicilan bulanan. Status: " . strtoupper($newStatus));
    }
}
