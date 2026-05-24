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
use App\Services\ArrearsCalculationService;
use App\Services\ArrearsExcelBuilder;
use Dompdf\Dompdf;
use Dompdf\Options;

class ArrearsReportController extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected ArrearsCalculationService $arrearsCalcService;
    protected ArrearsExcelBuilder $arrearsExcelBuilder;

    /** Maximum months selectable in one report to bound query & PDF complexity. */
    private const MAX_MONTHS = 12;

    /** Default PHP max_execution_time override for heavy export operations (seconds). */
    private const EXPORT_TIMEOUT = 120;

    public function __construct()
    {
        $this->anggotaModel        = new KopAnggotaModel();
        
        $this->arrearsCalcService = new ArrearsCalculationService(
            $this->anggotaModel,
            new KopPinjamanModel(),
            new KopAngsuranModel(),
            new KopSimpananModel(),
            new LoanAmortizationService()
        );
        $this->arrearsExcelBuilder = new ArrearsExcelBuilder();
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

        if ($anggotaId === 'all') {
            $rows = $this->arrearsCalcService->computeAllMembersArrearsSummary($year, $months);
            $member = ['id' => 'all', 'username' => 'Rekapitulasi Seluruh Anggota Aktif', 'nomor_anggota' => '-', 'email' => '-'];
        } else {
            $rows = $this->arrearsCalcService->computeArrearsRows($anggotaId, $year, $months);
            $member = $this->getMemberInfo($anggotaId);
        }

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

        $kopData = $this->getKopData();
        $signer  = SuratHelper::getSigner('default');

        if ($anggotaId === 'all') {
            $rows   = $this->arrearsCalcService->computeAllMembersArrearsSummary($year, $months);
            $member = ['id' => 'all', 'username' => 'Rekapitulasi Semua Anggota', 'nomor_anggota' => 'ALL', 'email' => '-'];
            
            $html = view('admin/cooperative/partials/arrears_all_pdf_template', [
                'rows'    => $rows,
                'year'    => $year,
                'months'  => $months,
                'kopData' => $kopData,
                'signer'  => $signer,
                'printed' => date('d F Y H:i'),
            ]);
            $filename = 'Rekap_Tunggakan_Semua_Anggota_' . $year . '.pdf';
        } else {
            $rows   = $this->arrearsCalcService->computeArrearsRows($anggotaId, $year, $months);
            $member = $this->getMemberInfo($anggotaId);

            $html = view('admin/cooperative/partials/arrears_pdf_template', [
                'member'  => $member,
                'rows'    => $rows,
                'year'    => $year,
                'months'  => $months,
                'kopData' => $kopData,
                'signer'  => $signer,
                'printed' => date('d F Y H:i'),
            ]);
            $filename = 'Tunggakan_' . ($member['nomor_anggota'] ?? $anggotaId) . '_' . $year . '.pdf';
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', true); // needed for logo from public/ path

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

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

        $kopData = $this->getKopData();
        $signer  = SuratHelper::getSigner('default');

        if ($anggotaId === 'all') {
            $rows      = $this->arrearsCalcService->computeAllMembersArrearsSummary($year, $months);
            $excelData = $this->arrearsExcelBuilder->buildExcelAllMembers($rows, $year, $months, $kopData, $signer);
            $filename  = 'Rekap_Tunggakan_Semua_Anggota_' . $year . '.xlsx';
        } else {
            $rows      = $this->arrearsCalcService->computeArrearsRows($anggotaId, $year, $months);
            $member    = $this->getMemberInfo($anggotaId);
            $excelData = $this->arrearsExcelBuilder->buildExcel($member, $rows, $year, $kopData, $signer);
            $filename  = 'Tunggakan_' . ($member['nomor_anggota'] ?? $anggotaId) . '_' . $year . '.xlsx';
        }

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
        $anggotaIdRaw = $this->request->getPost('anggota_id');
        $anggotaId = $anggotaIdRaw === 'all' ? 'all' : (int) $anggotaIdRaw;
        $year      = (int) $this->request->getPost('tahun');
        $bulanRaw  = $this->request->getPost('bulan');

        if ($anggotaId !== 'all' && $anggotaId <= 0) {
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
}
