<?php

namespace App\Controllers;

use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopShuModel;
use App\Models\KopShuAlokasi;
use App\Models\KopShuConfiguration;
use App\Models\AuditLogModel;

class CooperativeShu extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopShuModel $shuModel;
    protected KopShuAlokasi $alokasiModel;

    public function __construct()
    {
        $this->anggotaModel   = new KopAnggotaModel();
        $this->simpananModel  = new KopSimpananModel();
        $this->pinjamanModel  = new KopPinjamanModel();
        $this->shuModel       = new KopShuModel();
        $this->alokasiModel   = new KopShuAlokasi();
    }

    private function checkCoopManage()
    {
        if (!auth()->loggedIn() || (!auth()->user()->inGroup('admin') && !auth()->user()->inGroup('superadmin') && !auth()->user()->inGroup('manager'))) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }
    }

    private function getMemberSavings(array $members): array
    {
        $allSavings = [];
        $totalCoopSavings = 0;
        foreach ($members as $m) {
            $setoran   = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'setoran')->selectSum('nominal')->first()['nominal'] ?? 0;
            $penarikan = $this->simpananModel->where('anggota_id', $m['id'])->where('status', 'approved')->where('tipe_transaksi', 'penarikan')->selectSum('nominal')->first()['nominal'] ?? 0;
            $netSavings = floatval($setoran) - floatval($penarikan);
            if ($netSavings < 0) $netSavings = 0;
            $allSavings[$m['id']] = $netSavings;
            $totalCoopSavings += $netSavings;
        }
        return [$allSavings, $totalCoopSavings];
    }

    private function getMemberLoanVolume(array $members): array
    {
        $allVolume = [];
        $totalCoopVolume = 0;
        foreach ($members as $m) {
            $loans = $this->pinjamanModel->where('anggota_id', $m['id'])->whereIn('status', ['approved', 'paid'])->findAll();
            $memberVolume = 0;
            foreach ($loans as $l) {
                $memberVolume += floatval($l['nominal_pinjaman']);
            }
            $allVolume[$m['id']] = $memberVolume;
            $totalCoopVolume += $memberVolume;
        }
        return [$allVolume, $totalCoopVolume];
    }

    /**
     * SHU Panel — 3-step form + simulation.
     */
    public function adminIndex()
    {
        $this->checkCoopManage();

        $tahun = intval($this->request->getGet('tahun') ?? date('Y'));
        $totalShu = floatval($this->request->getGet('total_shu') ?? 0);

        // Load existing allocation from DB for this year
        $existingAlokasi = $this->alokasiModel->where('tahun', $tahun)->first();

        // Determine percentages
        if ($existingAlokasi) {
            $cadangan_persen        = (float) $existingAlokasi['cadangan_persen'];
            $jasa_modal_persen      = (float) $existingAlokasi['jasa_modal_persen'];
            $jasa_usaha_persen      = (float) $existingAlokasi['jasa_usaha_persen'];
            $dana_pengurus_persen   = (float) $existingAlokasi['dana_pengurus_persen'];
            $dana_pendidikan_persen = (float) $existingAlokasi['dana_pendidikan_persen'];
        } else {
            $useDefault = $this->request->getGet('use_default') === '1';

            if ($useDefault) {
                $defaults = KopShuAlokasi::getDefaults();
                $cadangan_persen        = $defaults['cadangan_persen'];
                $jasa_modal_persen      = $defaults['jasa_modal_persen'];
                $jasa_usaha_persen      = $defaults['jasa_usaha_persen'];
                $dana_pengurus_persen   = $defaults['dana_pengurus_persen'];
                $dana_pendidikan_persen = $defaults['dana_pendidikan_persen'];
            } else {
                $cadangan_persen        = (float) ($this->request->getGet('cadangan_persen') ?? 40);
                $jasa_modal_persen      = (float) ($this->request->getGet('jasa_modal_persen') ?? 20);
                $jasa_usaha_persen      = (float) ($this->request->getGet('jasa_usaha_persen') ?? 25);
                $dana_pengurus_persen   = (float) ($this->request->getGet('dana_pengurus_persen') ?? 10);
                $dana_pendidikan_persen = (float) ($this->request->getGet('dana_pendidikan_persen') ?? 5);
            }
        }

        $allocationCadangan   = $totalShu * ($cadangan_persen / 100);
        $allocationModal      = $totalShu * ($jasa_modal_persen / 100);
        $allocationUsaha      = $totalShu * ($jasa_usaha_persen / 100);
        $allocationPengurus   = $totalShu * ($dana_pengurus_persen / 100);
        $allocationPendidikan = $totalShu * ($dana_pendidikan_persen / 100);

        // Fetch active members
        $members = $this->anggotaModel->select('kop_anggota.*, users.username')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('status_keaktifan', 'aktif')
            ->findAll();

        // Total savings
        [$allSavings, $totalCoopSavings] = $this->getMemberSavings($members);

        // Total loan volume (nominal_pinjaman, NOT bunga)
        [$allVolume, $totalCoopVolume] = $this->getMemberLoanVolume($members);

        // Compile simulation
        $simulation = [];
        foreach ($members as $m) {
            $memberSavings = $allSavings[$m['id']] ?? 0;
            $memberVolume  = $allVolume[$m['id']] ?? 0;

            $jasaModal = ($totalCoopSavings > 0) ? ($memberSavings / $totalCoopSavings) * $allocationModal : 0;
            $jasaUsaha = ($totalCoopVolume > 0)   ? ($memberVolume / $totalCoopVolume)   * $allocationUsaha : 0;
            $totalMemberShu = $jasaModal + $jasaUsaha;

            $simulation[] = [
                'anggota_id'         => $m['id'],
                'nomor_anggota'      => $m['nomor_anggota'],
                'username'           => $m['username'],
                'total_savings'      => $memberSavings,
                'total_loan_volume'  => $memberVolume,
                'jasa_modal'         => $jasaModal,
                'jasa_usaha'         => $jasaUsaha,
                'total_shu'          => $totalMemberShu,
            ];
        }

        // Past SHU distribution history
        $shuHistory = $this->shuModel->select('kop_shu_history.*, kop_anggota.nomor_anggota, users.username, distributor.username as distributed_by_name')
            ->join('kop_anggota', 'kop_anggota.id = kop_shu_history.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->join('users distributor', 'distributor.id = kop_shu_history.distributed_by', 'left')
            ->orderBy('kop_shu_history.tahun', 'DESC')
            ->orderBy('kop_shu_history.id', 'DESC')
            ->findAll();

        return view('admin/cooperative/shu', [
            'title'                   => 'Panel Koperasi - Pembagian SHU',
            'tahun'                   => $tahun,
            'totalShu'                => $totalShu,
            'cadangan_persen'         => $cadangan_persen,
            'jasa_modal_persen'       => $jasa_modal_persen,
            'jasa_usaha_persen'       => $jasa_usaha_persen,
            'dana_pengurus_persen'    => $dana_pengurus_persen,
            'dana_pendidikan_persen'  => $dana_pendidikan_persen,
            'simulation'              => $simulation,
            'shuHistory'              => $shuHistory,
            'totalCoopSavings'        => $totalCoopSavings,
            'totalCoopVolume'         => $totalCoopVolume,
            'existingAlokasi'         => $existingAlokasi,
            'useDefault'              => $this->request->getGet('use_default') === '1',
        ]);
    }

    /**
     * Save RAT allocation percentages (POST).
     */
    public function saveAlokasi()
    {
        $this->checkCoopManage();

        $tahun = intval($this->request->getPost('tahun') ?? date('Y'));
        $totalShu = floatval($this->request->getPost('total_shu') ?? 0);

        if ($totalShu <= 0) {
            return redirect()->back()->with('error', 'Total SHU Bersih harus lebih besar dari 0.');
        }

        $data = [
            'cadangan_persen'        => (float) ($this->request->getPost('cadangan_persen') ?? 40),
            'jasa_modal_persen'      => (float) ($this->request->getPost('jasa_modal_persen') ?? 20),
            'jasa_usaha_persen'      => (float) ($this->request->getPost('jasa_usaha_persen') ?? 25),
            'dana_pengurus_persen'   => (float) ($this->request->getPost('dana_pengurus_persen') ?? 10),
            'dana_pendidikan_persen' => (float) ($this->request->getPost('dana_pendidikan_persen') ?? 5),
        ];

        if (!$this->alokasiModel->validatePercentageTotal($data)) {
            $total = array_sum($data);
            return redirect()->back()->with('error', "Total persentase harus 100%, sekarang {$total}%.");
        }

        // Check if already distributed
        $existing = $this->alokasiModel->where('tahun', $tahun)->first();
        if ($existing && $existing['status'] === 'distributed') {
            return redirect()->back()->with('error', "Alokasi SHU Tahun Buku {$tahun} sudah didistribusikan dan tidak dapat diubah.");
        }

        $payload = [
            'tahun'              => $tahun,
            'total_shu_bersih'   => $totalShu,
            'cadangan_persen'    => $data['cadangan_persen'],
            'jasa_modal_persen'  => $data['jasa_modal_persen'],
            'jasa_usaha_persen'  => $data['jasa_usaha_persen'],
            'dana_pengurus_persen'   => $data['dana_pengurus_persen'],
            'dana_pendidikan_persen' => $data['dana_pendidikan_persen'],
            'status'             => 'approved',
            'approval_date'      => date('Y-m-d H:i:s'),
            'approved_by'        => auth()->id(),
            'created_by'         => auth()->id(),
            'updated_by'         => auth()->id(),
        ];

        if ($existing) {
            $this->alokasiModel->update($existing['id'], $payload);
        } else {
            $this->alokasiModel->insert($payload);
        }

        // Audit log
        $auditLogModel = new AuditLogModel();
        $auditLogModel->insert([
            'user_id'    => auth()->id(),
            'action'     => 'coop_shu_alokasi',
            'details'    => "Menyimpan alokasi SHU Tahun Buku {$tahun} sebesar Rp " . number_format($totalShu, 0, ',', '.'),
            'ip_address' => $this->request->getIPAddress(),
        ]);

        return redirect()->to(base_url("admin/cooperative/shu?tahun={$tahun}&total_shu={$totalShu}"))->with('message', 'Alokasi SHU berhasil disimpan.');
    }

    /**
     * Distribute SHU to all active members.
     */
    public function distribute()
    {
        $this->checkCoopManage();

        $tahun = intval($this->request->getPost('tahun') ?? date('Y'));
        $totalShu = floatval($this->request->getPost('total_shu') ?? 0);

        if ($totalShu <= 0) {
            return redirect()->back()->with('error', 'Total SHU Bersih harus lebih besar dari 0.');
        }

        // Load allocation — prefer from DB, fallback to POST params
        $alokasi = $this->alokasiModel->where('tahun', $tahun)->where('status !=', 'draft')->first();

        if ($alokasi) {
            $cadangan_persen        = (float) $alokasi['cadangan_persen'];
            $jasa_modal_persen      = (float) $alokasi['jasa_modal_persen'];
            $jasa_usaha_persen      = (float) $alokasi['jasa_usaha_persen'];
            $dana_pengurus_persen   = (float) $alokasi['dana_pengurus_persen'];
            $dana_pendidikan_persen = (float) $alokasi['dana_pendidikan_persen'];
            $allocationId           = (int) $alokasi['id'];
        } else {
            $cadangan_persen        = (float) ($this->request->getPost('cadangan_persen') ?? 40);
            $jasa_modal_persen      = (float) ($this->request->getPost('jasa_modal_persen') ?? 20);
            $jasa_usaha_persen      = (float) ($this->request->getPost('jasa_usaha_persen') ?? 25);
            $dana_pengurus_persen   = (float) ($this->request->getPost('dana_pengurus_persen') ?? 10);
            $dana_pendidikan_persen = (float) ($this->request->getPost('dana_pendidikan_persen') ?? 5);
            $allocationId           = null;
        }

        $totalPercent = $cadangan_persen + $jasa_modal_persen + $jasa_usaha_persen + $dana_pengurus_persen + $dana_pendidikan_persen;
        if (abs($totalPercent - 100.0) >= 0.01) {
            return redirect()->back()->with('error', "Total persentase alokasi harus 100%, sekarang {$totalPercent}%.");
        }

        // Check for duplicate distribution
        $existingDist = $this->shuModel->where('tahun', $tahun)->first();
        if ($existingDist) {
            return redirect()->back()->with('error', "SHU untuk Tahun Buku {$tahun} sudah pernah dibagikan sebelumnya.");
        }

        $allocationModal = $totalShu * ($jasa_modal_persen / 100);
        $allocationUsaha = $totalShu * ($jasa_usaha_persen / 100);

        // Fetch active members
        $members = $this->anggotaModel->where('status_keaktifan', 'aktif')->findAll();

        // Calculate savings & loan volume
        [$allSavings, $totalCoopSavings] = $this->getMemberSavings($members);
        [$allVolume, $totalCoopVolume]   = $this->getMemberLoanVolume($members);

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            foreach ($members as $m) {
                $memberSavings = $allSavings[$m['id']] ?? 0;
                $memberVolume  = $allVolume[$m['id']] ?? 0;

                $jasaModal = ($totalCoopSavings > 0) ? ($memberSavings / $totalCoopSavings) * $allocationModal : 0;
                $jasaUsaha = ($totalCoopVolume > 0)   ? ($memberVolume / $totalCoopVolume)   * $allocationUsaha : 0;
                $totalMemberShu = $jasaModal + $jasaUsaha;

                if ($totalMemberShu <= 0) {
                    continue;
                }

                // Insert SHU history with new fields
                $this->shuModel->insert([
                    'anggota_id'            => $m['id'],
                    'tahun'                 => $tahun,
                    'jasa_modal'            => $jasaModal,
                    'jasa_anggota'          => 0,
                    'jasa_usaha'            => $jasaUsaha,
                    'volume_pinjaman'       => $memberVolume,
                    'total_volume_pinjaman' => $totalCoopVolume,
                    'allocation_id'         => $allocationId,
                    'total_shu'             => $totalMemberShu,
                    'tanggal_distribusi'    => date('Y-m-d H:i:s'),
                    'distributed_by'        => auth()->id(),
                ]);

                // Auto-credit to Sukarela savings
                $this->simpananModel->insert([
                    'anggota_id'     => $m['id'],
                    'jenis_simpanan' => 'sukarela',
                    'tipe_transaksi' => 'setoran',
                    'nominal'        => $totalMemberShu,
                    'status'         => 'approved',
                    'keterangan'     => "Pembagian SHU Tahun Buku {$tahun}",
                    'approved_by'    => auth()->id(),
                    'approved_at'    => date('Y-m-d H:i:s'),
                ]);
            }

            // Lock allocation as distributed
            if ($allocationId) {
                $this->alokasiModel->update($allocationId, [
                    'status'     => 'distributed',
                    'updated_by' => auth()->id(),
                ]);
            }

            // Audit log
            $auditLogModel = new AuditLogModel();
            $auditLogModel->insert([
                'user_id'    => auth()->id(),
                'action'     => 'coop_shu_distributed',
                'details'    => "Membagikan SHU Tahun Buku {$tahun} sebesar Rp " . number_format($totalShu, 0, ',', '.'),
                'ip_address' => $this->request->getIPAddress(),
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
     * Personal SHU history for members.
     */
    public function memberIndex()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $userId = auth()->id();
        $member = $this->anggotaModel->where('user_id', $userId)->first();

        if (!$member) {
            return redirect()->to(base_url('cooperative'))->with('error', 'Anda belum terdaftar sebagai anggota koperasi.');
        }

        $myShu = $this->shuModel->where('anggota_id', $member['id'])
            ->orderBy('tahun', 'DESC')
            ->findAll();

        $totalReceived = $this->shuModel->where('anggota_id', $member['id'])
            ->selectSum('total_shu')
            ->first()['total_shu'] ?? 0;

        return view('user/cooperative/shu_history', [
            'title'         => 'Koperasi Saya - SHU',
            'myShu'         => $myShu,
            'totalReceived' => floatval($totalReceived),
        ]);
    }
}
