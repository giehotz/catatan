<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopAnggotaModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopSimpananModel;
use App\Models\KopSettingModel;
use App\Helpers\SuratHelper;
use App\Services\LoanAmortizationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color as SpreadsheetColor;

class ArrearsReportController extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected KopSimpananModel $simpananModel;
    protected LoanAmortizationService $amortizationService;

    /** Maximum months selectable in one report to bound query & PDF complexity. */
    private const MAX_MONTHS = 12;

    /** Default PHP max_execution_time override for heavy export operations (seconds). */
    private const EXPORT_TIMEOUT = 120;

    public function __construct()
    {
        $this->anggotaModel        = new KopAnggotaModel();
        $this->pinjamanModel       = new KopPinjamanModel();
        $this->angsuranModel       = new KopAngsuranModel();
        $this->simpananModel       = new KopSimpananModel();
        $this->amortizationService = new LoanAmortizationService();
    }

    // -------------------------------------------------------------------------
    // INDEX — Filter form + active member list
    // -------------------------------------------------------------------------

    public function index()
    {
        $this->requireAdminAccess();

        $activeMembers = $this->anggotaModel
            ->select('kop_anggota.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->where('kop_anggota.status_keaktifan', 'aktif')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        return view('admin/cooperative/arrears_report', [
            'title'         => 'Laporan Tunggakan Angsuran Anggota',
            'activeMembers' => $activeMembers,
            'currentYear'   => (int) date('Y'),
        ]);
    }

    // -------------------------------------------------------------------------
    // PREVIEW — AJAX endpoint that returns computed arrears as JSON
    // -------------------------------------------------------------------------

    public function preview()
    {
        $this->requireAdminAccess();

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Bad Request.']);
        }

        $params = $this->parseAndValidateParams();
        if (isset($params['error'])) {
            return $this->response->setJSON(['success' => false, 'error' => $params['error']]);
        }

        [$anggotaId, $year, $months] = [$params['anggota_id'], $params['year'], $params['months']];

        $rows = $this->computeArrearsRows($anggotaId, $year, $months);
        $member = $this->getMemberInfo($anggotaId);

        return $this->response->setJSON([
            'success' => true,
            'member'  => $member,
            'rows'    => $rows,
        ]);
    }

    // -------------------------------------------------------------------------
    // EXPORT PDF
    // -------------------------------------------------------------------------

    public function exportPdf()
    {
        $this->requireAdminAccess();
        @set_time_limit(self::EXPORT_TIMEOUT);

        $params = $this->parseAndValidateParams();
        if (isset($params['error'])) {
            return redirect()->back()->with('error', $params['error']);
        }

        [$anggotaId, $year, $months] = [$params['anggota_id'], $params['year'], $params['months']];
        $downloadToken = $this->request->getPost('download_token') ?? '';

        $rows   = $this->computeArrearsRows($anggotaId, $year, $months);
        $member = $this->getMemberInfo($anggotaId);
        $kopData = $this->getKopData();
        $signer  = SuratHelper::getSigner('default');

        $html = view('admin/cooperative/partials/arrears_pdf_template', [
            'member'  => $member,
            'rows'    => $rows,
            'year'    => $year,
            'months'  => $months,
            'kopData' => $kopData,
            'signer'  => $signer,
            'printed' => date('d F Y H:i'),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', true); // needed for logo from public/ path

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Tunggakan_' . ($member['nomor_anggota'] ?? $anggotaId) . '_' . $year . '.pdf';

        // Set download-tracking cookie so the client-side overlay can close
        if (!empty($downloadToken)) {
            $this->response->setCookie([
                'name'    => 'downloadToken',
                'value'   => $downloadToken,
                'expire'  => time() + 60,
                'path'    => '/',
                'samesite' => 'Lax',
            ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($dompdf->output());
    }

    // -------------------------------------------------------------------------
    // EXPORT EXCEL
    // -------------------------------------------------------------------------

    public function exportExcel()
    {
        $this->requireAdminAccess();
        @set_time_limit(self::EXPORT_TIMEOUT);

        $params = $this->parseAndValidateParams();
        if (isset($params['error'])) {
            return redirect()->back()->with('error', $params['error']);
        }

        [$anggotaId, $year, $months] = [$params['anggota_id'], $params['year'], $params['months']];
        $downloadToken = $this->request->getPost('download_token') ?? '';

        $rows   = $this->computeArrearsRows($anggotaId, $year, $months);
        $member = $this->getMemberInfo($anggotaId);
        $kopData = $this->getKopData();
        $signer  = SuratHelper::getSigner('default');

        $excelData = $this->buildExcel($member, $rows, $year, $kopData, $signer);

        $filename = 'Tunggakan_' . ($member['nomor_anggota'] ?? $anggotaId) . '_' . $year . '.xlsx';

        if (!empty($downloadToken)) {
            $this->response->setCookie([
                'name'    => 'downloadToken',
                'value'   => $downloadToken,
                'expire'  => time() + 60,
                'path'    => '/',
                'samesite' => 'Lax',
            ]);
        }

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($excelData);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Gate-check: throws 404 PageNotFoundException if the current user is not
     * in an admin or superadmin group. The parent route is also protected by
     * the coop_auth filter, so this is a defence-in-depth layer.
     */
    private function requireAdminAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->inGroup('admin') && !$user->inGroup('superadmin') && !$user->inGroup('manager'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }
    }

    /**
     * Parse & validate POST params shared across preview, PDF, and Excel.
     *
     * @return array  On success: ['anggota_id', 'year', 'months'].
     *                On failure: ['error' => string].
     */
    private function parseAndValidateParams(): array
    {
        $anggotaId = (int) $this->request->getPost('anggota_id');
        $year      = (int) $this->request->getPost('tahun');
        $bulanRaw  = $this->request->getPost('bulan');

        if ($anggotaId <= 0) {
            return ['error' => 'Silakan pilih anggota terlebih dahulu.'];
        }
        if ($year < 2000 || $year > (int) date('Y') + 1) {
            return ['error' => 'Tahun laporan tidak valid.'];
        }
        if (empty($bulanRaw) || !is_array($bulanRaw)) {
            return ['error' => 'Pilih minimal satu bulan untuk laporan.'];
        }

        // Server-side enforcement of the 12-month cap
        $months = array_unique(array_filter(array_map('intval', $bulanRaw), fn($m) => $m >= 1 && $m <= 12));
        sort($months); // always chronological
        if (count($months) > self::MAX_MONTHS) {
            return ['error' => 'Maksimal ' . self::MAX_MONTHS . ' bulan dapat dipilih dalam satu laporan.'];
        }
        if (count($months) === 0) {
            return ['error' => 'Pilih minimal satu bulan yang valid (1–12).'];
        }

        return ['anggota_id' => $anggotaId, 'year' => $year, 'months' => $months];
    }

    /**
     * Core arrears calculation engine.
     *
     * For each target month computes:
     *   - Tunggakan Simpanan Wajib (Rp kewajiban – Rp sudah disetujui)
     *   - Tunggakan Dana Sosial (Rp kewajiban – Rp sudah disetujui)
     *   - Tunggakan Jasa Pinjaman (sum over all loans with due installments
     *     that are not yet approved, using LoanAmortizationService)
     *
     * A loan with status='paid' is included for historical months where its
     * installment was due and unpaid; it is excluded only if the loan was
     * fully settled before the target month started.
     *
     * @param int   $anggotaId  kop_anggota.id
     * @param int   $year
     * @param int[] $months     Sorted array of 1–12 integers
     * @return array  Per-month rows with keys: bulan, bulan_nama, tunggakan_wajib,
     *                tunggakan_sosial, tunggakan_jasa, jumlah, record_wajib, record_sosial
     */
    private function computeArrearsRows(int $anggotaId, int $year, array $months): array
    {
        $wajibNominal   = floatval(KopSettingModel::getSetting('kop_simpanan_wajib_nominal', '50000'));
        $wajibBatasHari = intval(KopSettingModel::getSetting('kop_simpanan_wajib_batas_hari', '7'));
        $sosialNominal  = floatval(KopSettingModel::getSetting('kop_dana_sosial_nominal', '20000'));
        $sosialBatasHari = intval(KopSettingModel::getSetting('kop_dana_sosial_batas_hari', '7'));

        $todayYear  = (int) date('Y');
        $todayMonth = (int) date('n');
        $todayDay   = (int) date('j');

        // Fetch all simpanan records for this anggota (wajib + sosial, approved)
        $simpananRaw = $this->simpananModel
            ->where('anggota_id', $anggotaId)
            ->whereIn('jenis_simpanan', ['wajib', 'sosial'])
            ->where('tipe_transaksi', 'setoran')
            ->where('tahun', $year)
            ->findAll();

        // Index by jenis → month → list of records
        $simpananMap = [];
        foreach ($simpananRaw as $s) {
            $jenis = $s['jenis_simpanan'];
            $bulan = (int) $s['bulan'];
            $simpananMap[$jenis][$bulan][] = $s;
        }

        // Fetch all loans (approved OR paid) for this anggota
        $loans = $this->pinjamanModel
            ->where('anggota_id', $anggotaId)
            ->whereIn('status', ['approved', 'paid'])
            ->findAll();

        // Pre-fetch all installment records for each loan, keyed by angsuran_ke
        $installmentMaps = [];
        foreach ($loans as $loan) {
            $insts = $this->angsuranModel
                ->where('pinjaman_id', $loan['id'])
                ->where('status', 'approved')
                ->findAll();
            $map = [];
            foreach ($insts as $inst) {
                $map[(int) $inst['angsuran_ke']] = $inst;
            }
            $installmentMaps[$loan['id']] = $map;
        }

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $rows = [];

        foreach ($months as $month) {
            // ----------------------------------------------------------------
            // 1. Simpanan Wajib
            // ----------------------------------------------------------------
            $wajibRecords = $simpananMap['wajib'][$month] ?? [];
            $wajibPaidOk  = false;
            $wajibRecord  = null;

            // Sum up approved payments for this month
            $totalWajibApproved = 0.0;
            foreach ($wajibRecords as $r) {
                if ($r['status'] === 'approved') {
                    $totalWajibApproved += floatval($r['nominal']);
                    $wajibRecord = $r; // keep last for reference
                }
            }

            // Check if there's a pending record (for display)
            if ($wajibRecord === null) {
                foreach ($wajibRecords as $r) {
                    if ($r['status'] === 'pending') {
                        $wajibRecord = $r;
                        break;
                    }
                }
            }

            if ($totalWajibApproved >= $wajibNominal) {
                $wajibPaidOk = true;
            }

            // Grace period: only count as arrears if the deadline has passed
            $wajibIsOverdue = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $wajibBatasHari);
            $tunggakanWajib = (!$wajibPaidOk && $wajibIsOverdue)
                ? max(0.0, $wajibNominal - $totalWajibApproved)
                : 0.0;

            // ----------------------------------------------------------------
            // 2. Dana Sosial
            // ----------------------------------------------------------------
            $sosialRecords = $simpananMap['sosial'][$month] ?? [];
            $sosialRecord  = null;
            $totalSosialApproved = 0.0;
            foreach ($sosialRecords as $r) {
                if ($r['status'] === 'approved') {
                    $totalSosialApproved += floatval($r['nominal']);
                    $sosialRecord = $r;
                }
            }
            if ($sosialRecord === null) {
                foreach ($sosialRecords as $r) {
                    if ($r['status'] === 'pending') {
                        $sosialRecord = $r;
                        break;
                    }
                }
            }

            $sosialIsOverdue    = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $sosialBatasHari);
            $tunggakanSosial    = (($totalSosialApproved < $sosialNominal) && $sosialIsOverdue)
                ? max(0.0, $sosialNominal - $totalSosialApproved)
                : 0.0;

            // ----------------------------------------------------------------
            // 3. Jasa Pinjaman (accumulated across all active loans)
            // ----------------------------------------------------------------
            $tunggakanJasa = 0.0;
            foreach ($loans as $loan) {
                // Determine if this loan was fully paid before this month began
                $loanWasPaidBeforeMonth = false;
                if ($loan['status'] === 'paid') {
                    // Check how many installments exist; if the last installment's
                    // approved_at is before the start of this target month, it's settled.
                    $lastApproved = end($installmentMaps[$loan['id']]);
                    if ($lastApproved !== false) {
                        $lastApprovedDate = new \DateTime($lastApproved['approved_at'] ?? '');
                        $targetMonthStart = new \DateTime("{$year}-{$month}-01");
                        if ($lastApprovedDate < $targetMonthStart) {
                            $loanWasPaidBeforeMonth = true;
                        }
                    }
                }

                $arrears = $this->amortizationService->getArrearsForMonth(
                    $loan,
                    $month,
                    $year,
                    $installmentMaps[$loan['id']],
                    $loanWasPaidBeforeMonth
                );

                $tunggakanJasa += $arrears['bunga'] + $arrears['jasa'];
            }

            // ----------------------------------------------------------------
            // 4. Summary row
            // ----------------------------------------------------------------
            $jumlah = $tunggakanWajib + $tunggakanSosial + $tunggakanJasa;

            $rows[] = [
                'bulan'            => $month,
                'bulan_nama'       => $monthNames[$month] ?? "Bulan {$month}",
                'tunggakan_wajib'  => round($tunggakanWajib, 2),
                'tunggakan_sosial' => round($tunggakanSosial, 2),
                'tunggakan_jasa'   => round($tunggakanJasa, 2),
                'jumlah'           => round($jumlah, 2),
                'record_wajib'     => $wajibRecord,
                'record_sosial'    => $sosialRecord,
            ];
        }

        return $rows;
    }

    /**
     * Check whether a given month is overdue (the deadline day has passed).
     */
    private function isMonthOverdue(
        int $month, int $year,
        int $todayYear, int $todayMonth, int $todayDay,
        int $batasHari
    ): bool {
        if ($year < $todayYear) {
            return true; // past year → always overdue
        }
        if ($year === $todayYear) {
            if ($month < $todayMonth) {
                return true; // past month in current year
            }
            if ($month === $todayMonth) {
                return $todayDay > $batasHari; // current month: check day
            }
        }
        return false; // future month
    }

    /**
     * Fetch member info merged with user info for display in reports.
     */
    private function getMemberInfo(int $anggotaId): array
    {
        $member = $this->anggotaModel
            ->select('kop_anggota.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->find($anggotaId);

        return $member ?? ['id' => $anggotaId, 'username' => 'Tidak Ditemukan', 'nomor_anggota' => '-'];
    }

    /**
     * Fetch KOP data from active settings (no document snapshot needed for reports).
     */
    private function getKopData(): array
    {
        return [
            'cooperative_name' => KopSettingModel::getSetting('kop_nama_koperasi', 'Koperasi Simpan Pinjam'),
            'legal_id'         => KopSettingModel::getSetting('kop_badan_hukum', ''),
            'work_region'      => KopSettingModel::getSetting('kop_wilayah_kerja', ''),
            'address'          => KopSettingModel::getSetting('kop_alamat', ''),
            'phone'            => KopSettingModel::getSetting('kop_telepon', ''),
            'email'            => KopSettingModel::getSetting('kop_email', ''),
            'logo_path'        => KopSettingModel::getSetting('kop_logo_path', ''),
        ];
    }

    /**
     * Build Excel workbook from computed arrears data.
     */
    private function buildExcel(array $member, array $rows, int $year, array $kopData, array $signer): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tunggakan ' . $year);

        $tealDark   = '0F766E'; // emerald-700
        $tealMid    = '14B8A6'; // teal-500
        $grayLight  = 'F8FAFC';
        $grayBorder = 'E2E8F0';
        $red        = 'EF4444';
        $white      = 'FFFFFF';

        $colCount = 7; // A–G
        $colRange = 'A:G';

        // Row 1 — Cooperative name (KOP)
        $sheet->setCellValue('A1', strtoupper($kopData['cooperative_name']));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 2 — Legal ID & address
        $kop2 = implode(' | ', array_filter([
            $kopData['legal_id'] ?? '',
            $kopData['address'] ?? '',
            $kopData['phone'] ?? '',
        ]));
        $sheet->setCellValue('A2', $kop2);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 3 — Report title
        $sheet->setCellValue('A3', 'LAPORAN TUNGGAKAN ANGSURAN ANGGOTA KSP — TAHUN ' . $year);
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDFA');

        // Row 4 — Member info
        $memberLine = 'Nama: ' . ($member['username'] ?? '-') . '   |   No. Anggota: ' . ($member['nomor_anggota'] ?? '-') . '   |   Email: ' . ($member['email'] ?? '-');
        $sheet->setCellValue('A4', $memberLine);
        $sheet->mergeCells('A4:G4');
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true)->setColor(new SpreadsheetColor('475569'));
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 5 — print date
        $sheet->setCellValue('A5', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->mergeCells('A5:G5');
        $sheet->getStyle('A5')->getFont()->setSize(8)->setColor(new SpreadsheetColor('94A3B8'));
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Row 6 blank separator
        $sheet->getRowDimension(6)->setRowHeight(6);

        // Row 7 — Header
        $headers = ['No', 'Bulan', 'Tunggakan Wajib', 'Tunggakan Sosial', 'Tunggakan Jasa', 'Jumlah', 'Keterangan'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '7', $h);
            $col++;
        }
        $sheet->getStyle('A7:G7')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(22);

        // Data rows
        $rowNum = 8;
        $no = 1;
        $totalWajib = 0.0;
        $totalSosial = 0.0;
        $totalJasa = 0.0;
        $grandTotal = 0.0;

        $currencyFormat = '"Rp"#,##0';

        foreach ($rows as $r) {
            $isEven     = ($no % 2 === 0);
            $rowBg      = $isEven ? 'F8FAFC' : 'FFFFFF';
            $rowHasTunggakan = $r['jumlah'] > 0;

            $keterangan = '';
            if ($r['record_wajib'] && $r['record_wajib']['status'] === 'pending') {
                $keterangan = 'Wajib: Menunggu Konfirmasi';
            }
            if ($r['record_sosial'] && $r['record_sosial']['status'] === 'pending') {
                $keterangan .= ($keterangan ? '; ' : '') . 'Sosial: Menunggu Konfirmasi';
            }

            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, $r['bulan_nama']);
            $sheet->setCellValue('C' . $rowNum, $r['tunggakan_wajib']);
            $sheet->setCellValue('D' . $rowNum, $r['tunggakan_sosial']);
            $sheet->setCellValue('E' . $rowNum, $r['tunggakan_jasa']);
            $sheet->setCellValue('F' . $rowNum, $r['jumlah']);
            $sheet->setCellValue('G' . $rowNum, $keterangan ?: ($rowHasTunggakan ? 'Belum Dibayar' : 'Lunas / Tidak Ada Tagihan'));

            // Number formats
            foreach (['C', 'D', 'E', 'F'] as $c) {
                $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
            }

            // Highlight rows with arrears in red
            if ($rowHasTunggakan) {
                $sheet->getStyle('F' . $rowNum)->getFont()->setBold(true)->setColor(new SpreadsheetColor($red));
            }

            $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $grayBorder]]],
            ]);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setWrapText(true);

            $totalWajib  += $r['tunggakan_wajib'];
            $totalSosial += $r['tunggakan_sosial'];
            $totalJasa   += $r['tunggakan_jasa'];
            $grandTotal  += $r['jumlah'];

            $no++;
            $rowNum++;
        }

        // Total row
        $sheet->setCellValue('A' . $rowNum, 'TOTAL TUNGGAKAN');
        $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
        $sheet->setCellValue('C' . $rowNum, $totalWajib);
        $sheet->setCellValue('D' . $rowNum, $totalSosial);
        $sheet->setCellValue('E' . $rowNum, $totalJasa);
        $sheet->setCellValue('F' . $rowNum, $grandTotal);
        $sheet->setCellValue('G' . $rowNum, '');

        foreach (['C', 'D', 'E', 'F'] as $c) {
            $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
        }
        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => $white]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowNum += 2;

        // Signature block
        $sheet->setCellValue('E' . $rowNum, 'Mengetahui,');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('E' . $rowNum, date('d F Y'));
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum += 4; // signature space

        $sheet->setCellValue('E' . $rowNum, $signer['name'] ?? 'Pengurus KSP');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('E' . $rowNum, $signer['role'] ?? 'Pengurus');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }
}
