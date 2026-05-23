<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopSettingModel;
use App\Services\LoanAmortizationService;
use App\Traits\MemberTrait;

class BillsController extends BaseController
{
    use MemberTrait;

    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected LoanAmortizationService $amortizationService;

    public function __construct()
    {
        $this->simpananModel       = new KopSimpananModel();
        $this->pinjamanModel       = new KopPinjamanModel();
        $this->angsuranModel       = new KopAngsuranModel();
        $this->amortizationService = new LoanAmortizationService();
    }

    /**
     * View dynamic, monthly-tracked unpaid bills since registration.
     */
    public function bills()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        // Fetch settings
        $wajibNominal = floatval(KopSettingModel::getSetting('kop_simpanan_wajib_nominal', '50000'));
        $wajibBatasHari = intval(KopSettingModel::getSetting('kop_simpanan_wajib_batas_hari', '7'));
        $sosialNominal = floatval(KopSettingModel::getSetting('kop_dana_sosial_nominal', '20000'));
        $sosialBatasHari = intval(KopSettingModel::getSetting('kop_dana_sosial_batas_hari', '7'));

        // Calculate months since joining
        $joinDate = new \DateTime($member['created_at']);
        $currentDate = new \DateTime();
        
        // Ensure we start from the join month and end at the current month
        $interval = $joinDate->diff($currentDate);
        $totalMonths = ($interval->y * 12) + $interval->m + 1; // +1 to include the current month

        $bills = [];
        $unpaidCount = 0;
        $totalUnpaidAmount = 0;

        // Fetch all member savings to check status
        $savings = $this->simpananModel->where('anggota_id', $member['id'])
            ->whereIn('jenis_simpanan', ['wajib', 'sosial'])
            ->where('tipe_transaksi', 'setoran')
            ->findAll();

        // Index savings by type, year, and month
        $savingsMap = [];
        foreach ($savings as $s) {
            $savingsMap[$s['jenis_simpanan']][$s['tahun']][$s['bulan']] = $s;
        }

        // Loop from join month to current month
        $tempDate = clone $joinDate;
        for ($i = 0; $i < $totalMonths; $i++) {
            $month = intval($tempDate->format('n'));
            $year = intval($tempDate->format('Y'));

            // 1. Simpanan Wajib Status
            $wajibRecord = $savingsMap['wajib'][$year][$month] ?? null;
            $wajibStatus = 'Belum Bayar';
            if ($wajibRecord) {
                if ($wajibRecord['status'] === 'approved') {
                    $wajibStatus = 'Lunas';
                } elseif ($wajibRecord['status'] === 'pending') {
                    $wajibStatus = 'Pending';
                }
            } else {
                if ($year < intval(date('Y')) || ($year === intval(date('Y')) && $month < intval(date('m')))) {
                    $wajibStatus = 'Menunggak';
                } elseif ($year === intval(date('Y')) && $month === intval(date('m'))) {
                    if (intval(date('j')) > $wajibBatasHari) {
                        $wajibStatus = 'Menunggak';
                    }
                }
            }

            if ($wajibStatus === 'Belum Bayar' || $wajibStatus === 'Menunggak') {
                $unpaidCount++;
                $totalUnpaidAmount += $wajibNominal;
            }

            // 2. Dana Sosial Status
            $sosialRecord = $savingsMap['sosial'][$year][$month] ?? null;
            $sosialStatus = 'Belum Bayar';
            if ($sosialRecord) {
                if ($sosialRecord['status'] === 'approved') {
                    $sosialStatus = 'Lunas';
                } elseif ($sosialRecord['status'] === 'pending') {
                    $sosialStatus = 'Pending';
                }
            } else {
                if ($year < intval(date('Y')) || ($year === intval(date('Y')) && $month < intval(date('m')))) {
                    $sosialStatus = 'Menunggak';
                } elseif ($year === intval(date('Y')) && $month === intval(date('m'))) {
                    if (intval(date('j')) > $sosialBatasHari) {
                        $sosialStatus = 'Menunggak';
                    }
                }
            }

            if ($sosialStatus === 'Belum Bayar' || $sosialStatus === 'Menunggak') {
                $unpaidCount++;
                $totalUnpaidAmount += $sosialNominal;
            }

            $bills[] = [
                'bulan' => $month,
                'tahun' => $year,
                'month_name' => $tempDate->format('F'),
                'wajib_nominal' => $wajibNominal,
                'wajib_status' => $wajibStatus,
                'wajib_record' => $wajibRecord,
                'sosial_nominal' => $sosialNominal,
                'sosial_status' => $sosialStatus,
                'sosial_record' => $sosialRecord,
            ];

            $tempDate->modify('+1 month');
        }

        // Fetch active loans & their next installments
        $activeLoans = $this->pinjamanModel->where('anggota_id', $member['id'])
            ->where('status', 'approved')
            ->findAll();

        foreach ($activeLoans as &$loan) {
            // Fetch all existing installment records for this loan keyed by angsuran_ke
            $existingInstallments = $this->angsuranModel
                ->where('pinjaman_id', $loan['id'])
                ->findAll();
            $installmentMap = [];
            foreach ($existingInstallments as $inst) {
                $installmentMap[(int) $inst['angsuran_ke']] = $inst;
            }

            // Build schedule via shared service — identical formula used in ArrearsReportController
            $scheduleRaw = $this->amortizationService->buildScheduleWithRecords($loan, $installmentMap);

            // Attach summary values to loan array for the view
            if (!empty($scheduleRaw)) {
                $firstEntry = reset($scheduleRaw);
                $loan['pokok_per_bulan']    = $firstEntry['pokok'];
                $loan['bunga_per_bulan']    = $firstEntry['bunga'];
                $loan['jasa_per_bulan']     = $firstEntry['jasa'];
                $loan['angsuran_per_bulan'] = $firstEntry['total'];
            } else {
                $loan['pokok_per_bulan']    = 0;
                $loan['bunga_per_bulan']    = 0;
                $loan['jasa_per_bulan']     = 0;
                $loan['angsuran_per_bulan'] = 0;
            }

            // Convert schedule to view-compatible array (1-indexed → 0-indexed list)
            $amortization = array_values($scheduleRaw);
            foreach ($amortization as &$entry) {
                if (!empty($entry['due_date'])) {
                    $entry['due_date'] = date('d M Y', strtotime($entry['due_date']));
                }
            }
            unset($entry);

            $loan['amortization'] = $amortization;
        }

        return view('user/cooperative/bills', [
            'title'             => 'Tagihan Saya',
            'is_member'         => true,
            'member'            => $member,
            'wajibNominal'      => $wajibNominal,
            'sosialNominal'     => $sosialNominal,
            'wajibBatasHari'    => $wajibBatasHari,
            'sosialBatasHari'   => $sosialBatasHari,
            'bills'             => array_reverse($bills),
            'unpaidCount'       => $unpaidCount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'activeLoans'       => $activeLoans,
        ]);
    }

    /**
     * Submit payment for savings bills (monthly or annual prepayments).
     */
    public function paySavingBill()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $rules = [
            'jenis_simpanan' => 'required|in_list[wajib,sosial,tahunan]',
            'bulan'          => 'permit_empty|integer',
            'tahun'          => 'required|integer',
            'nominal'        => 'required|numeric|greater_than[0]',
            'bukti_transfer' => 'uploaded[bukti_transfer]|is_image[bukti_transfer]|max_size[bukti_transfer,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $jenis = $this->request->getPost('jenis_simpanan');
        $bulan = $this->request->getPost('bulan') ?: null;
        $tahun = intval($this->request->getPost('tahun'));
        $nominal = floatval($this->request->getPost('nominal'));

        $file = $this->request->getFile('bukti_transfer');
        $newName = '';
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/bukti_setoran')) {
                mkdir(FCPATH . 'uploads/bukti_setoran', 0777, true);
            }
            $file->move(FCPATH . 'uploads/bukti_setoran', $newName);
        }

        if ($jenis !== 'tahunan') {
            $exist = $this->simpananModel->where('anggota_id', $member['id'])
                ->where('jenis_simpanan', $jenis)
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->whereIn('status', ['pending', 'approved'])
                ->countAllResults();

            if ($exist > 0) {
                return redirect()->back()->with('error', "Pembayaran Simpanan " . ucfirst($jenis) . " untuk bulan {$bulan} tahun {$tahun} sudah diajukan sebelumnya.");
            }
            
            $keterangan = "Pembayaran Simpanan " . ($jenis === 'wajib' ? 'Wajib' : 'Dana Sosial') . " untuk Bulan {$bulan} Tahun {$tahun}";
        } else {
            $exist = $this->simpananModel->where('anggota_id', $member['id'])
                ->where('jenis_simpanan', 'tahunan')
                ->where('tahun', $tahun)
                ->whereIn('status', ['pending', 'approved'])
                ->countAllResults();

            if ($exist > 0) {
                return redirect()->back()->with('error', "Pembayaran Simpanan Tahunan untuk tahun {$tahun} sudah diajukan sebelumnya.");
            }
            
            $keterangan = "Pembayaran Simpanan Wajib & Dana Sosial 1 Tahun Penuh (Tahun {$tahun})";
        }

        $this->simpananModel->insert([
            'anggota_id'     => $member['id'],
            'jenis_simpanan' => $jenis,
            'tipe_transaksi' => 'setoran',
            'nominal'        => $nominal,
            'status'         => 'pending',
            'bukti_transfer' => $newName,
            'bulan'          => $bulan,
            'tahun'          => $tahun,
            'keterangan'     => $keterangan,
        ]);

        return redirect()->to(base_url('cooperative/bills'))->with('message', 'Bukti setoran tagihan simpanan berhasil diunggah. Menunggu persetujuan pengelola.');
    }
}
