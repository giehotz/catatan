<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopPinjamanModel;
use App\Models\KopAnggotaModel;
use App\Models\KopAngsuranModel;
use App\Models\ReceivableModel;
use App\Models\KopKasInternalModel;
use App\Models\DebtModel;
use App\Models\AuditLogModel;
use App\Models\KopSettingModel;
use App\Services\LoanAmortizationService;
use CodeIgniter\Exceptions\PageNotFoundException;

class LoanController extends BaseController
{
    protected KopPinjamanModel $pinjamanModel;
    protected KopAnggotaModel $anggotaModel;
    protected ReceivableModel $receivableModel;
    protected KopKasInternalModel $kasInternalModel;
    protected DebtModel $debtModel;
    protected KopAngsuranModel $angsuranModel;
    protected LoanAmortizationService $amortizationService;

    public function __construct()
    {
        $this->pinjamanModel       = new KopPinjamanModel();
        $this->anggotaModel        = new KopAnggotaModel();
        $this->receivableModel     = new ReceivableModel();
        $this->kasInternalModel    = new KopKasInternalModel();
        $this->debtModel           = new DebtModel();
        $this->angsuranModel       = new KopAngsuranModel();
        $this->amortizationService = new LoanAmortizationService();
    }

    /**
     * Manage loans verifications.
     */
    public function loans()
    {
        $loans = $this->pinjamanModel->select('kop_pinjaman.*, users.username, kop_anggota.nomor_anggota')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->orderBy('kop_pinjaman.created_at', 'DESC')
            ->findAll();

        return view('admin/cooperative/loans', [
            'title' => 'Panel Koperasi - Pinjaman & Kelayakan',
            'loans' => $loans,
        ]);
    }

    /**
     * Approve a Loan Application (with double sync logic to Debt and Receivable ledgers).
     */
    public function approveLoan(int $id)
    {
        $loan = $this->pinjamanModel->find($id);
        if (!$loan || $loan['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan pinjaman tidak ditemukan atau sudah diproses.');
        }

        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        if (!$anggota || $anggota['status_keaktifan'] !== 'aktif') {
            return redirect()->back()->with('error', 'Keanggotaan pemohon tidak aktif atau sedang ditangguhkan.');
        }

        $sumberDana = $this->request->getPost('sumber_dana');
        if (!in_array($sumberDana, ['kas_utama', 'dana_talangan'])) {
            return redirect()->back()->with('error', 'Sumber dana pencairan tidak valid.');
        }

        $saldoKas = $this->kasInternalModel->getSaldo($sumberDana);
        if ($saldoKas < $loan['nominal_pinjaman']) {
            return redirect()->back()->with('error', "Pencairan gagal. Saldo {$sumberDana} (Rp " . number_format($saldoKas, 0, ',', '.') . ") tidak mencukupi untuk pinjaman ini.");
        }

        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Create entry in User's DEBTS table
            $dueDate = date('Y-m-d', strtotime("+{$loan['tenor_bulan']} months"));
            $bungaDesc = floatval($loan['bunga_persen']) . "% " . ($loan['jenis_bunga'] ?? 'flat');
            $debtId = $this->debtModel->insert([
                'user_id'       => $anggota['user_id'],
                'creditor_name' => 'Koperasi Simpan Pinjam',
                'total_amount'  => $loan['nominal_total'],
                'description'   => "Pinjaman Koperasi Khas (Tenor {$loan['tenor_bulan']} Bulan, Bunga {$bungaDesc})",
                'due_date'      => $dueDate,
                'status'        => 'unpaid',
            ]);

            // 2. Create entry in Cooperative/Admin's RECEIVABLES table
            $receivableId = $this->receivableModel->insert([
                'user_id'       => auth()->id(),
                'borrower_name' => esc($username),
                'total_amount'  => $loan['nominal_total'],
                'description'   => "Piutang Penyaluran Pinjaman Anggota '{$username}' (ID Koperasi: {$id})",
                'due_date'      => $dueDate,
                'status'        => 'unpaid',
            ]);

            // 3. Update the Loan Application status and save references
            $this->pinjamanModel->update($id, [
                'status'           => 'approved',
                'debt_id_fk'       => $debtId,
                'receivable_id_fk' => $receivableId,
                'approved_by'      => auth()->id(),
                'approved_at'      => date('Y-m-d H:i:s'),
            ]);

            // 4. Record disbursement from cooperative cash
            $this->kasInternalModel->insert([
                'kategori_dana'   => $sumberDana,
                'jenis_transaksi' => 'pengeluaran',
                'nominal'         => $loan['nominal_pinjaman'],
                'reference_type'  => 'pinjaman',
                'reference_id'    => $id,
                'keterangan'      => "Pencairan Pinjaman Koperasi ID: {$id} untuk Anggota '{$username}'",
                'created_by'      => auth()->id(),
            ]);

            // 5. Record upfront bunga / jasa if paid at inception
            $bungaPersen = floatval($loan['bunga_persen'] ?? KopSettingModel::getSetting('kop_bunga_pinjaman_persen', '1.50'));
            $jenisBunga = $loan['jenis_bunga'] ?? KopSettingModel::getSetting('kop_bunga_pinjaman_jenis', 'flat');
            $bungaOpsiBayar = $loan['bunga_opsi_bayar'] ?? KopSettingModel::getSetting('kop_bunga_pinjaman_opsi_bayar', 'cicil');

            $jasaNominal = floatval($loan['jasa_nominal'] ?? '0');
            $metodeBayarJasa = $loan['metode_bayar_jasa'] ?? KopSettingModel::getSetting('kop_jasa_pinjaman_cara_bayar', 'cicil');

            $upfrontBunga = 0.00;
            $upfrontJasa = 0.00;

            if ($bungaOpsiBayar === 'di_awal') {
                $bungaPeriode = KopSettingModel::getSetting('kop_bunga_pinjaman_periode', 'bulanan');
                $monthlyRate = ($bungaPeriode === 'tahunan') ? ($bungaPersen / 12) : $bungaPersen;
                if ($jenisBunga === 'flat') {
                    $bungaTotal = $loan['nominal_pinjaman'] * ($monthlyRate / 100) * $loan['tenor_bulan'];
                } else {
                    $bungaTotal = 0;
                    $monthlyPrincipal = $loan['nominal_pinjaman'] / $loan['tenor_bulan'];
                    for ($i = 0; $i < $loan['tenor_bulan']; $i++) {
                        $remaining = $loan['nominal_pinjaman'] - ($i * $monthlyPrincipal);
                        $bungaTotal += $remaining * ($monthlyRate / 100);
                    }
                }
                $upfrontBunga = $bungaTotal;
            }

            if ($metodeBayarJasa === 'di_awal') {
                $upfrontJasa = $jasaNominal;
            }

            if ($upfrontBunga > 0) {
                $this->kasInternalModel->insert([
                    'kategori_dana'   => $sumberDana,
                    'jenis_transaksi' => 'pemasukan',
                    'nominal'         => $upfrontBunga,
                    'reference_type'  => 'pinjaman',
                    'reference_id'    => $id,
                    'keterangan'      => "Potongan Bunga di Awal Pinjaman ID: {$id} untuk Anggota '{$username}'",
                    'created_by'      => auth()->id(),
                ]);
            }

            if ($upfrontJasa > 0) {
                $this->kasInternalModel->insert([
                    'kategori_dana'   => $sumberDana,
                    'jenis_transaksi' => 'pemasukan',
                    'nominal'         => $upfrontJasa,
                    'reference_type'  => 'pinjaman',
                    'reference_id'    => $id,
                    'keterangan'      => "Potongan Jasa di Awal Pinjaman ID: {$id} untuk Anggota '{$username}'",
                    'created_by'      => auth()->id(),
                ]);
            }

            AuditLogModel::log('coop_loan_approved', "Mencairkan pinjaman Anggota '{$username}' Rp " . number_format($loan['nominal_pinjaman'], 2) . " dari {$sumberDana}. Utang/Piutang otomatis.");

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Pencairan gagal karena kesalahan sistem.');
            }

            return redirect()->back()->with('message', 'Pinjaman berhasil dicairkan. Dana telah dipotong dari kas dan Piutang disinkronkan otomatis.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Reject a Loan Application.
     */
    public function rejectLoan(int $id)
    {
        $loan = $this->pinjamanModel->find($id);
        if (!$loan || $loan['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Pengajuan pinjaman tidak ditemukan atau sudah diproses.');
        }

        $this->pinjamanModel->update($id, [
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $anggota = $this->anggotaModel->find($loan['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        AuditLogModel::log('coop_loan_rejected', "Menolak pengajuan pinjaman Anggota '{$username}' senilai Rp " . number_format($loan['nominal_pinjaman'], 2));

        return redirect()->back()->with('message', 'Pengajuan pinjaman ditolak.');
    }

    // ══════════════════════════════════════════════════════════════════════════
    //  DAFTAR PINJAMAN (DIRECTORY)
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Display a paginated, searchable directory of all active loans.
     *
     * Supports server-side search (case-insensitive via LOWER()) and
     * status filtering (approved, paid, all).
     */
    public function directory()
    {
        $search = trim($this->request->getGet('q') ?? '');
        $statusFilter = $this->request->getGet('status') ?? 'all';
        $perPage = 15;

        $builder = $this->pinjamanModel
            ->select('kop_pinjaman.*, users.username, kop_anggota.nomor_anggota, kop_anggota.status_keaktifan')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id');

        // Only show approved & paid loans (not pending/rejected)
        if ($statusFilter === 'approved') {
            $builder->where('kop_pinjaman.status', 'approved');
        } elseif ($statusFilter === 'paid') {
            $builder->where('kop_pinjaman.status', 'paid');
        } else {
            $builder->whereIn('kop_pinjaman.status', ['approved', 'paid']);
        }

        // Case-insensitive search on member name or member number
        if ($search !== '') {
            $searchLower = strtolower($search);
            $builder->groupStart()
                ->like('LOWER(users.username)', $searchLower)
                ->orLike('LOWER(kop_anggota.nomor_anggota)', $searchLower)
                ->groupEnd();
        }

        $builder->orderBy('kop_pinjaman.approved_at', 'DESC');

        $loans = $builder->paginate($perPage, 'directory');
        $pager = $this->pinjamanModel->pager;

        // Calculate quick summary for each loan (progress bar)
        foreach ($loans as &$loan) {
            $paidCount = $this->angsuranModel
                ->where('pinjaman_id', $loan['id'])
                ->where('status', 'approved')
                ->countAllResults();
            $tenor = intval($loan['tenor_bulan']);
            $loan['paid_count'] = $paidCount;
            $loan['progress_persen'] = $tenor > 0 ? round(($paidCount / $tenor) * 100, 1) : 0;
        }
        unset($loan);

        return view('admin/cooperative/loans_directory', [
            'title'        => 'Panel Koperasi - Daftar Pinjaman',
            'loans'        => $loans,
            'pager'        => $pager,
            'search'       => $search,
            'statusFilter' => $statusFilter,
        ]);
    }

    /**
     * Display detailed loan information with full amortization schedule.
     *
     * Uses a 24-hour cache for the amortization schedule to avoid
     * recalculation on every page load. Cache is automatically invalidated
     * by Model hooks when installment data changes.
     */
    public function directoryDetails(int $id)
    {
        // Ownership validation: loan must exist and belong to an active member
        $loan = $this->pinjamanModel
            ->select('kop_pinjaman.*, users.username, kop_anggota.nomor_anggota, kop_anggota.status_keaktifan, kop_anggota.user_id as member_user_id')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('kop_pinjaman.id', $id)
            ->whereIn('kop_pinjaman.status', ['approved', 'paid'])
            ->first();

        if (!$loan) {
            throw PageNotFoundException::forPageNotFound("Pinjaman dengan ID #{$id} tidak ditemukan atau belum disetujui.");
        }

        // Fetch all installment records for this loan
        $installmentRecords = $this->angsuranModel
            ->where('pinjaman_id', $id)
            ->orderBy('angsuran_ke', 'ASC')
            ->findAll();

        // Key by angsuran_ke for quick lookup
        $installmentMap = [];
        foreach ($installmentRecords as $inst) {
            $installmentMap[(int) $inst['angsuran_ke']] = $inst;
        }

        // Approved-only map for summary calculation
        $paidMap = array_filter($installmentMap, fn($inst) => $inst['status'] === 'approved');

        // Cached amortization schedule (TTL 24h, invalidated by model hooks)
        $cacheKey = "loan_schedule_{$id}";
        $schedule = cache($cacheKey);
        if ($schedule === null) {
            $schedule = $this->amortizationService->buildScheduleWithRecords($loan, $installmentMap);
            cache()->save($cacheKey, $schedule, 86400); // 24 hours
        }

        // Calculate loan summary metrics
        $summary = $this->amortizationService->calculateLoanSummary($loan, $paidMap);

        // Role-based view permissions
        $isAdmin = auth()->user()->inGroup('admin') || auth()->user()->inGroup('superadmin');

        return view('admin/cooperative/loan_directory_details', [
            'title'    => 'Detail Pinjaman - ' . ($loan['username'] ?? 'N/A'),
            'loan'     => $loan,
            'schedule' => $schedule,
            'summary'  => $summary,
            'isAdmin'  => $isAdmin,
        ]);
    }

    /**
     * Export loan details and amortization schedule to Excel.
     *
     * Protected by rate limiting (max 5 downloads per minute per IP)
     * and ownership validation (loan must belong to active member).
     */
    public function exportDirectoryDetailsExcel(int $id)
    {
        // Rate limiting: max 5 requests per minute per IP
        $throttler = \Config\Services::throttler();
        if (!$throttler->check(md5('excel_export_' . $this->request->getIPAddress()), 5, MINUTE)) {
            return $this->response->setStatusCode(429)->setBody('Terlalu banyak permintaan ekspor. Silakan coba lagi dalam 1 menit.');
        }

        // Ownership validation
        $loan = $this->pinjamanModel
            ->select('kop_pinjaman.*, users.username, kop_anggota.nomor_anggota')
            ->join('kop_anggota', 'kop_anggota.id = kop_pinjaman.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('kop_pinjaman.id', $id)
            ->whereIn('kop_pinjaman.status', ['approved', 'paid'])
            ->first();

        if (!$loan) {
            throw PageNotFoundException::forPageNotFound("Pinjaman tidak ditemukan.");
        }

        // Fetch installment records
        $installmentRecords = $this->angsuranModel
            ->where('pinjaman_id', $id)
            ->orderBy('angsuran_ke', 'ASC')
            ->findAll();

        $installmentMap = [];
        foreach ($installmentRecords as $inst) {
            $installmentMap[(int) $inst['angsuran_ke']] = $inst;
        }

        $paidMap = array_filter($installmentMap, fn($inst) => $inst['status'] === 'approved');

        $schedule = $this->amortizationService->buildScheduleWithRecords($loan, $installmentMap);
        $summary  = $this->amortizationService->calculateLoanSummary($loan, $paidMap);

        // Build Excel using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Amortisasi Pinjaman');

        // Header info
        $sheet->setCellValue('A1', 'DETAIL PINJAMAN KOPERASI');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Nama Anggota');
        $sheet->setCellValue('B3', $loan['username'] ?? '-');
        $sheet->setCellValue('A4', 'No. Anggota');
        $sheet->setCellValue('B4', $loan['nomor_anggota'] ?? '-');
        $sheet->setCellValue('A5', 'Nominal Pinjaman');
        $sheet->setCellValue('B5', floatval($loan['nominal_pinjaman']));
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('A6', 'Tenor');
        $sheet->setCellValue('B6', $loan['tenor_bulan'] . ' bulan');
        $sheet->setCellValue('A7', 'Jenis Bunga');
        $sheet->setCellValue('B7', ucfirst($loan['jenis_bunga'] ?? 'flat') . ' (' . $loan['bunga_persen'] . '%)');
        $sheet->setCellValue('A8', 'Status Pinjaman');
        $sheet->setCellValue('B8', strtoupper($loan['status']));
        $sheet->setCellValue('A9', 'Tanggal Cair');
        $sheet->setCellValue('B9', $loan['approved_at'] ? date('d/m/Y', strtotime($loan['approved_at'])) : '-');

        // Summary
        $sheet->setCellValue('D3', 'Total Kewajiban');
        $sheet->setCellValue('E3', $summary['grand_total']);
        $sheet->getStyle('E3')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('D4', 'Sudah Diangsur');
        $sheet->setCellValue('E4', $summary['paid_total']);
        $sheet->getStyle('E4')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('D5', 'Sisa Tagihan');
        $sheet->setCellValue('E5', $summary['sisa_tagihan']);
        $sheet->getStyle('E5')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('D6', 'Tunggakan Jatuh Tempo');
        $sheet->setCellValue('E6', $summary['overdue_total']);
        $sheet->getStyle('E6')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->setCellValue('D7', 'Progress');
        $sheet->setCellValue('E7', $summary['progress_persen'] . '%');

        $sheet->getStyle('A3:A9')->getFont()->setBold(true);
        $sheet->getStyle('D3:D7')->getFont()->setBold(true);

        // Amortization schedule table
        $row = 11;
        $headers = ['No.', 'Tgl Jatuh Tempo', 'Pokok', 'Bunga', 'Jasa', 'Total', 'Status', 'Tgl Bayar'];
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . $row;
            $sheet->setCellValue($cell, $header);
        }
        $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:H{$row}")->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1F2937');
        $sheet->getStyle("A{$row}:H{$row}")->getFont()->getColor()->setRGB('FFFFFF');

        $row++;
        foreach ($schedule as $entry) {
            $statusLabel = $entry['status'] ?? 'Belum Dibayar';
            $tanggalBayar = '';
            if (!empty($entry['record']) && !empty($entry['record']['tanggal_bayar'])) {
                $tanggalBayar = date('d/m/Y', strtotime($entry['record']['tanggal_bayar']));
            }

            $sheet->setCellValue("A{$row}", $entry['angsuran_ke']);
            $sheet->setCellValue("B{$row}", !empty($entry['due_date']) ? date('d/m/Y', strtotime($entry['due_date'])) : '-');
            $sheet->setCellValue("C{$row}", $entry['pokok']);
            $sheet->setCellValue("D{$row}", $entry['bunga']);
            $sheet->setCellValue("E{$row}", $entry['jasa']);
            $sheet->setCellValue("F{$row}", $entry['total']);
            $sheet->setCellValue("G{$row}", ucfirst($statusLabel));
            $sheet->setCellValue("H{$row}", $tanggalBayar);

            $sheet->getStyle("C{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generate and send file
        $filename = 'Pinjaman_' . ($loan['username'] ?? 'anggota') . '_' . date('Ymd_His') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $response = $this->response;
        $response->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->setHeader('Cache-Control', 'max-age=0');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $response->setBody($content);
    }
}

