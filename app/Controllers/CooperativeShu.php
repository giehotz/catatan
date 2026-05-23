<?php

namespace App\Controllers;

use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopShuModel;
use App\Models\AuditLogModel;

class CooperativeShu extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopShuModel $shuModel;

    public function __construct()
    {
        $this->anggotaModel = new KopAnggotaModel();
        $this->simpananModel = new KopSimpananModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->shuModel = new KopShuModel();
    }

    private function checkCoopManage()
    {
        if (!auth()->loggedIn() || (!auth()->user()->inGroup('admin') && !auth()->user()->inGroup('superadmin') && !auth()->user()->inGroup('manager'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }
    }

    /**
     * SHU Panel for Cooperative Managers.
     * Displays a calculator tool to simulate and distribute SHU.
     */
    public function adminIndex()
    {
        $this->checkCoopManage();

        $tahun = intval($this->request->getGet('tahun') ?? date('Y'));
        $totalShu = floatval($this->request->getGet('total_shu') ?? 0);
        $jasaModalPercent = floatval($this->request->getGet('jasa_modal_percent') ?? 50);
        $jasaAnggotaPercent = floatval($this->request->getGet('jasa_anggota_percent') ?? 50);

        // Normalize percentages
        if ($jasaModalPercent + $jasaAnggotaPercent !== 100.0) {
            $totalPercent = $jasaModalPercent + $jasaAnggotaPercent;
            if ($totalPercent > 0) {
                $jasaModalPercent = ($jasaModalPercent / $totalPercent) * 100;
                $jasaAnggotaPercent = ($jasaAnggotaPercent / $totalPercent) * 100;
            } else {
                $jasaModalPercent = 50;
                $jasaAnggotaPercent = 50;
            }
        }

        $allocationModal = $totalShu * ($jasaModalPercent / 100);
        $allocationAnggota = $totalShu * ($jasaAnggotaPercent / 100);

        // 1. Fetch active members
        $members = $this->anggotaModel->select('kop_anggota.*, users.username')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('status_keaktifan', 'aktif')
            ->findAll();

        // 2. Fetch total cooperative metrics for division ratios
        // Total savings of all active members
        $allSavings = [];
        $totalCoopSavings = 0;
        foreach ($members as $m) {
            $setoran = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'setoran')->selectSum('nominal')->first()['nominal'] ?? 0;
            $penarikan = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'penarikan')->selectSum('nominal')->first()['nominal'] ?? 0;
            $netSavings = floatval($setoran) - floatval($penarikan);
            if ($netSavings < 0) $netSavings = 0;
            $allSavings[$m['id']] = $netSavings;
            $totalCoopSavings += $netSavings;
        }

        // Total interest generated from all active members' approved/paid loans
        $allInterest = [];
        $totalCoopInterest = 0;
        foreach ($members as $m) {
            $loans = $this->pinjamanModel->where('anggota_id', $m['id'])->whereIn('status', ['approved', 'paid'])->findAll();
            $memberInterest = 0;
            foreach ($loans as $l) {
                $memberInterest += (floatval($l['nominal_total']) - floatval($l['nominal_pinjaman']));
            }
            $allInterest[$m['id']] = $memberInterest;
            $totalCoopInterest += $memberInterest;
        }

        // 3. Compile simulation results
        $simulation = [];
        foreach ($members as $m) {
            $memberSavings = $allSavings[$m['id']];
            $memberInterest = $allInterest[$m['id']];

            $jasaModal = ($totalCoopSavings > 0) ? ($memberSavings / $totalCoopSavings) * $allocationModal : 0;
            $jasaAnggota = ($totalCoopInterest > 0) ? ($memberInterest / $totalCoopInterest) * $allocationAnggota : 0;
            $totalMemberShu = $jasaModal + $jasaAnggota;

            $simulation[] = [
                'anggota_id'      => $m['id'],
                'nomor_anggota'   => $m['nomor_anggota'],
                'username'        => $m['username'],
                'total_savings'   => $memberSavings,
                'total_interest'  => $memberInterest,
                'jasa_modal'      => $jasaModal,
                'jasa_anggota'    => $jasaAnggota,
                'total_shu'       => $totalMemberShu
            ];
        }

        // History of past SHU distributions
        $shuHistory = $this->shuModel->select('kop_shu_history.*, kop_anggota.nomor_anggota, users.username, distributor.username as distributed_by_name')
            ->join('kop_anggota', 'kop_anggota.id = kop_shu_history.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('users distributor', 'distributor.id = kop_shu_history.distributed_by', 'left')
            ->orderBy('kop_shu_history.tahun', 'DESC')
            ->orderBy('kop_shu_history.id', 'DESC')
            ->findAll();

        return view('admin/cooperative/shu', [
            'title'               => 'Panel Koperasi - Pembagian SHU',
            'tahun'               => $tahun,
            'totalShu'            => $totalShu,
            'jasaModalPercent'    => $jasaModalPercent,
            'jasaAnggotaPercent'  => $jasaAnggotaPercent,
            'simulation'          => $simulation,
            'shuHistory'          => $shuHistory,
            'totalCoopSavings'    => $totalCoopSavings,
            'totalCoopInterest'   => $totalCoopInterest,
        ]);
    }

    /**
     * Handle final distribution of SHU.
     * Inserts records into kop_shu_history and credits simpanan_sukarela.
     */
    public function distribute()
    {
        $this->checkCoopManage();

        $tahun = intval($this->request->getPost('tahun') ?? date('Y'));
        $totalShu = floatval($this->request->getPost('total_shu') ?? 0);
        $jasaModalPercent = floatval($this->request->getPost('jasa_modal_percent') ?? 50);
        $jasaAnggotaPercent = floatval($this->request->getPost('jasa_anggota_percent') ?? 50);

        if ($totalShu <= 0) {
            return redirect()->back()->with('error', 'Nominal Total SHU harus lebih besar dari 0.');
        }

        // Check if SHU for this year has already been distributed to avoid double distribution
        $existing = $this->shuModel->where('tahun', $tahun)->first();
        if ($existing) {
            return redirect()->back()->with('error', "SHU untuk Tahun Buku {$tahun} sudah pernah dibagikan sebelumnya.");
        }

        $allocationModal = $totalShu * ($jasaModalPercent / 100);
        $allocationAnggota = $totalShu * ($jasaAnggotaPercent / 100);

        // Fetch members
        $members = $this->anggotaModel->where('status_keaktifan', 'aktif')->findAll();

        // Calculate division metrics
        $allSavings = [];
        $totalCoopSavings = 0;
        foreach ($members as $m) {
            $setoran = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'setoran')->selectSum('nominal')->first()['nominal'] ?? 0;
            $penarikan = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'penarikan')->selectSum('nominal')->first()['nominal'] ?? 0;
            $netSavings = floatval($setoran) - floatval($penarikan);
            if ($netSavings < 0) $netSavings = 0;
            $allSavings[$m['id']] = $netSavings;
            $totalCoopSavings += $netSavings;
        }

        $allInterest = [];
        $totalCoopInterest = 0;
        foreach ($members as $m) {
            $loans = $this->pinjamanModel->where('anggota_id', $m['id'])->whereIn('status', ['approved', 'paid'])->findAll();
            $memberInterest = 0;
            foreach ($loans as $l) {
                $memberInterest += (floatval($l['nominal_total']) - floatval($l['nominal_pinjaman']));
            }
            $allInterest[$m['id']] = $memberInterest;
            $totalCoopInterest += $memberInterest;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($members as $m) {
                $memberSavings = $allSavings[$m['id']];
                $memberInterest = $allInterest[$m['id']];

                $jasaModal = ($totalCoopSavings > 0) ? ($memberSavings / $totalCoopSavings) * $allocationModal : 0;
                $jasaAnggota = ($totalCoopInterest > 0) ? ($memberInterest / $totalCoopInterest) * $allocationAnggota : 0;
                $totalMemberShu = $jasaModal + $jasaAnggota;

                if ($totalMemberShu <= 0) {
                    continue;
                }

                // 1. Insert history
                $this->shuModel->insert([
                    'anggota_id'         => $m['id'],
                    'tahun'              => $tahun,
                    'jasa_modal'         => $jasaModal,
                    'jasa_anggota'       => $jasaAnggota,
                    'total_shu'          => $totalMemberShu,
                    'tanggal_distribusi' => date('Y-m-d H:i:s'),
                    'distributed_by'     => auth()->id()
                ]);

                // 2. Auto-credit to member's Sukarela savings
                $this->simpananModel->insert([
                    'anggota_id'     => $m['id'],
                    'jenis_simpanan' => 'sukarela',
                    'tipe_transaksi' => 'setoran',
                    'nominal'        => $totalMemberShu,
                    'status'         => 'approved',
                    'keterangan'     => "Pembagian SHU Tahun Buku {$tahun}",
                    'approved_by'    => auth()->id(),
                    'approved_at'    => date('Y-m-d H:i:s')
                ]);
            }

            // Write audit log
            $auditLogModel = new AuditLogModel();
            $auditLogModel->insert([
                'user_id'    => auth()->id(),
                'action'     => 'DISTRIBUTE_SHU',
                'details'    => "Membagikan SHU Tahun Buku {$tahun} sebesar Rp " . number_format($totalShu, 0, ',', '.'),
                'ip_address' => $this->request->getIPAddress()
            ]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal memproses pembagian SHU.');
            }

            return redirect()->to(base_url('admin/cooperative/shu'))->with('message', "SHU Tahun Buku {$tahun} berhasil didistribusikan ke Simpanan Sukarela semua anggota aktif.");
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Personal SHU history portal for Cooperative Members.
     */
    public function memberIndex()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        // Get member info
        $userId = auth()->id();
        $member = $this->anggotaModel->where('user_id', $userId)->first();

        if (!$member) {
            return redirect()->to(base_url('cooperative'))->with('error', 'Anda belum terdaftar sebagai anggota koperasi.');
        }

        // Personal SHU history
        $myShu = $this->shuModel->where('anggota_id', $member['id'])
            ->orderBy('tahun', 'DESC')
            ->findAll();

        $totalReceived = $this->shuModel->where('anggota_id', $member['id'])
            ->selectSum('total_shu')
            ->first()['total_shu'] ?? 0;

        return view('user/cooperative/shu_history', [
            'title'         => 'Koperasi Saya - SHU',
            'myShu'         => $myShu,
            'totalReceived' => floatval($totalReceived)
        ]);
    }
}
