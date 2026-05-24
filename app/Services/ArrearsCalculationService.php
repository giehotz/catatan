<?php

namespace App\Services;

use App\Models\KopAnggotaModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopSimpananModel;
use App\Models\KopSettingModel;

class ArrearsCalculationService
{
    protected KopAnggotaModel $anggotaModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected KopSimpananModel $simpananModel;
    protected LoanAmortizationService $amortizationService;

    // Cached settings to prevent querying them in loops
    private float $wajibNominal;
    private int $wajibBatasHari;
    private float $sosialNominal;
    private int $sosialBatasHari;

    public function __construct(
        KopAnggotaModel $anggotaModel,
        KopPinjamanModel $pinjamanModel,
        KopAngsuranModel $angsuranModel,
        KopSimpananModel $simpananModel,
        LoanAmortizationService $amortizationService
    ) {
        $this->anggotaModel        = $anggotaModel;
        $this->pinjamanModel       = $pinjamanModel;
        $this->angsuranModel       = $angsuranModel;
        $this->simpananModel       = $simpananModel;
        $this->amortizationService = $amortizationService;

        // Cache settings in constructor
        $this->wajibNominal    = floatval(KopSettingModel::getSetting('kop_simpanan_wajib_nominal', '50000'));
        $this->wajibBatasHari  = intval(KopSettingModel::getSetting('kop_simpanan_wajib_batas_hari', '7'));
        $this->sosialNominal   = floatval(KopSettingModel::getSetting('kop_dana_sosial_nominal', '20000'));
        $this->sosialBatasHari = intval(KopSettingModel::getSetting('kop_dana_sosial_batas_hari', '7'));
    }

    /**
     * Core arrears calculation engine for a specific member.
     *
     * @param int|string $anggotaId
     * @param int   $year
     * @param int[] $months     Sorted array of 1–12 integers
     * @return array
     */
    public function computeArrearsRows($anggotaId, int $year, array $months): array
    {
        $todayYear  = (int) date('Y');
        $todayMonth = (int) date('n');
        $todayDay   = (int) date('j');

        $simpananRaw = $this->simpananModel
            ->where('anggota_id', $anggotaId)
            ->whereIn('jenis_simpanan', ['wajib', 'sosial'])
            ->where('tipe_transaksi', 'setoran')
            ->where('tahun', $year)
            ->findAll();

        $simpananMap = [];
        foreach ($simpananRaw as $s) {
            $jenis = $s['jenis_simpanan'];
            $bulan = (int) $s['bulan'];
            $simpananMap[$jenis][$bulan][] = $s;
        }

        $loans = $this->pinjamanModel
            ->where('anggota_id', $anggotaId)
            ->whereIn('status', ['approved', 'paid'])
            ->findAll();

        $loanIds = array_column($loans, 'id');
        $angsuranRaw = [];
        if (!empty($loanIds)) {
            $angsuranRaw = $this->angsuranModel
                ->whereIn('pinjaman_id', $loanIds)
                ->where('status', 'approved')
                ->findAll();
        }

        $installmentMaps = [];
        foreach ($angsuranRaw as $inst) {
            $installmentMaps[(int) $inst['pinjaman_id']][(int) $inst['angsuran_ke']] = $inst;
        }

        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $rows = [];

        foreach ($months as $month) {
            // 1. Wajib
            $wajibRecords = $simpananMap['wajib'][$month] ?? [];
            $wajibPaidOk  = false;
            $wajibRecord  = null;
            $totalWajibApproved = 0.0;
            foreach ($wajibRecords as $r) {
                if ($r['status'] === 'approved') {
                    $totalWajibApproved += floatval($r['nominal']);
                    $wajibRecord = $r;
                }
            }
            if ($wajibRecord === null) {
                foreach ($wajibRecords as $r) {
                    if ($r['status'] === 'pending') {
                        $wajibRecord = $r;
                        break;
                    }
                }
            }
            if ($totalWajibApproved >= $this->wajibNominal) {
                $wajibPaidOk = true;
            }
            $wajibIsOverdue = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $this->wajibBatasHari);
            $tunggakanWajib = (!$wajibPaidOk && $wajibIsOverdue) ? max(0.0, $this->wajibNominal - $totalWajibApproved) : 0.0;

            // 2. Sosial
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
            $sosialIsOverdue = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $this->sosialBatasHari);
            $tunggakanSosial = (($totalSosialApproved < $this->sosialNominal) && $sosialIsOverdue) ? max(0.0, $this->sosialNominal - $totalSosialApproved) : 0.0;

            // 3. Jasa
            $tunggakanJasa = 0.0;
            foreach ($loans as $loan) {
                $loanWasPaidBeforeMonth = false;
                if ($loan['status'] === 'paid') {
                    $instMap = $installmentMaps[$loan['id']] ?? [];
                    $lastApproved = end($instMap);
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
                    $installmentMaps[$loan['id']] ?? [],
                    $loanWasPaidBeforeMonth
                );

                $tunggakanJasa += $arrears['bunga'] + $arrears['jasa'];
            }

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
     * Compute arrears for all active members (recap mode).
     * Calculates the total arrears over the selected months per member.
     * Members with 0 total arrears are excluded.
     * Uses batch loading to mitigate N+1 query performance issues.
     */
    public function computeAllMembersArrearsSummary(int $year, array $months): array
    {
        $todayYear  = (int) date('Y');
        $todayMonth = (int) date('n');
        $todayDay   = (int) date('j');

        $activeMembers = $this->anggotaModel
            ->select('kop_anggota.*, users.username, auth_identities.secret as email')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->where('kop_anggota.status_keaktifan', 'aktif')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        $memberIds = array_column($activeMembers, 'id');
        if (empty($memberIds)) return [];

        $simpananRaw = $this->simpananModel
            ->whereIn('anggota_id', $memberIds)
            ->whereIn('jenis_simpanan', ['wajib', 'sosial'])
            ->where('tipe_transaksi', 'setoran')
            ->where('tahun', $year)
            ->findAll();

        $simpananMap = [];
        foreach ($simpananRaw as $s) {
            $aId = (int) $s['anggota_id'];
            $simpananMap[$aId][$s['jenis_simpanan']][(int) $s['bulan']][] = $s;
        }

        $loansRaw = $this->pinjamanModel
            ->whereIn('anggota_id', $memberIds)
            ->whereIn('status', ['approved', 'paid'])
            ->findAll();

        $loanIds = array_column($loansRaw, 'id');
        $angsuranRaw = [];
        if (!empty($loanIds)) {
            $angsuranRaw = $this->angsuranModel
                ->whereIn('pinjaman_id', $loanIds)
                ->where('status', 'approved')
                ->findAll();
        }

        $loansMap = [];
        foreach ($loansRaw as $loan) $loansMap[(int) $loan['anggota_id']][] = $loan;

        $installmentMaps = [];
        foreach ($angsuranRaw as $inst) {
            $installmentMaps[(int) $inst['pinjaman_id']][(int) $inst['angsuran_ke']] = $inst;
        }

        $rows = [];

        foreach ($activeMembers as $member) {
            $aId = (int) $member['id'];
            $memberSimpMap = $simpananMap[$aId] ?? [];
            $memberLoans   = $loansMap[$aId] ?? [];

            $totalTunggakanWajib = 0.0;
            $totalTunggakanSosial = 0.0;
            $totalTunggakanJasa = 0.0;

            foreach ($months as $month) {
                // Wajib
                $wajibRecords = $memberSimpMap['wajib'][$month] ?? [];
                $totalWajibApproved = array_reduce($wajibRecords, fn($sum, $r) => $sum + ($r['status'] === 'approved' ? floatval($r['nominal']) : 0.0), 0.0);
                $wajibPaidOk = ($totalWajibApproved >= $this->wajibNominal);
                $wajibIsOverdue = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $this->wajibBatasHari);
                if (!$wajibPaidOk && $wajibIsOverdue) {
                    $totalTunggakanWajib += max(0.0, $this->wajibNominal - $totalWajibApproved);
                }

                // Sosial
                $sosialRecords = $memberSimpMap['sosial'][$month] ?? [];
                $totalSosialApproved = array_reduce($sosialRecords, fn($sum, $r) => $sum + ($r['status'] === 'approved' ? floatval($r['nominal']) : 0.0), 0.0);
                $sosialIsOverdue = $this->isMonthOverdue($month, $year, $todayYear, $todayMonth, $todayDay, $this->sosialBatasHari);
                if (($totalSosialApproved < $this->sosialNominal) && $sosialIsOverdue) {
                    $totalTunggakanSosial += max(0.0, $this->sosialNominal - $totalSosialApproved);
                }

                // Jasa
                foreach ($memberLoans as $loan) {
                    $lId = (int) $loan['id'];
                    $instMap = $installmentMaps[$lId] ?? [];

                    $loanWasPaidBeforeMonth = false;
                    if ($loan['status'] === 'paid') {
                        $lastApproved = end($instMap);
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
                        $instMap,
                        $loanWasPaidBeforeMonth
                    );

                    $totalTunggakanJasa += $arrears['bunga'] + $arrears['jasa'];
                }
            }

            $jumlah = $totalTunggakanWajib + $totalTunggakanSosial + $totalTunggakanJasa;

            if ($jumlah > 0) {
                $rows[] = [
                    'anggota_id'       => $aId,
                    'nama_anggota'     => $member['username'],
                    'tunggakan_wajib'  => round($totalTunggakanWajib, 2),
                    'tunggakan_sosial' => round($totalTunggakanSosial, 2),
                    'tunggakan_jasa'   => round($totalTunggakanJasa, 2),
                    'jumlah'           => round($jumlah, 2),
                ];
            }
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
}
