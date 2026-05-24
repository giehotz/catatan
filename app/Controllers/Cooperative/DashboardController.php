<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopKasInternalModel;

class DashboardController extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected KopKasInternalModel $kasInternalModel;

    public function __construct()
    {
        $this->anggotaModel = new KopAnggotaModel();
        $this->simpananModel = new KopSimpananModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
        $this->kasInternalModel = new KopKasInternalModel();
    }

    /**
     * Cooperative Dashboard: Main metrics and quick overview.
     */
    public function index()
    {
        // 1. Calculate Total Kas Koperasi (Pokok + Wajib + Sukarela)
        $totalSetoran = $this->simpananModel->where('status', 'approved')->where('tipe_transaksi', 'setoran')->selectSum('nominal')->first()['nominal'] ?? 0;
        $totalPenarikan = $this->simpananModel->where('status', 'approved')->where('tipe_transaksi', 'penarikan')->selectSum('nominal')->first()['nominal'] ?? 0;
        $totalKas = floatval($totalSetoran) - floatval($totalPenarikan);

        // 2. Calculate Total Piutang Aktif (Penyaluran Pinjaman minus Pembayaran Cicilan)
        $totalPinjaman = $this->pinjamanModel->whereIn('status', ['approved', 'paid'])->selectSum('nominal_total')->first()['nominal_total'] ?? 0;
        $totalAngsuran = $this->angsuranModel->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
        $piutangAktif = floatval($totalPinjaman) - floatval($totalAngsuran);

        // Kas Internal
        $saldoKasUtama = $this->kasInternalModel->getSaldo('kas_utama');
        $saldoDanaTalangan = $this->kasInternalModel->getSaldo('dana_talangan');

        // 3. Count members
        $activeMembers = $this->anggotaModel->where('status_keaktifan', 'aktif')->countAllResults();

        // 4. Pending workflows counts
        $pendingMembers = 0; // In our code, users auto-join via invitation, but admin can toggle status. Let's count total members.
        $pendingSavings = $this->simpananModel->where('status', 'pending')->countAllResults();
        $pendingLoans = $this->pinjamanModel->where('status', 'pending')->countAllResults();
        $pendingInstallments = $this->angsuranModel->where('status', 'pending')->countAllResults();

        // 5. Chart Analytics Data (Current Year)
        $currentYear = date('Y');
        
        $chartSimpanan = array_fill(1, 12, 0);
        $chartPinjaman = array_fill(1, 12, 0);
        $chartAngsuran = array_fill(1, 12, 0);

        $db = \Config\Database::connect();
        
        // Fetch monthly setoran simpanan
        $simpananQuery = $db->table('kop_simpanan')
            ->select('MONTH(tanggal_transaksi) as bulan, SUM(nominal) as total')
            ->where('status', 'approved')
            ->where('tipe_transaksi', 'setoran')
            ->where('YEAR(tanggal_transaksi)', $currentYear)
            ->groupBy('MONTH(tanggal_transaksi)')
            ->get()->getResultArray();
            
        foreach ($simpananQuery as $row) {
            $chartSimpanan[(int)$row['bulan']] = (float)$row['total'];
        }

        // Fetch monthly pinjaman (approved)
        $pinjamanQuery = $db->table('kop_pinjaman')
            ->select('MONTH(created_at) as bulan, SUM(nominal_pinjaman) as total')
            ->whereIn('status', ['approved', 'paid'])
            ->where('YEAR(created_at)', $currentYear)
            ->groupBy('MONTH(created_at)')
            ->get()->getResultArray();
            
        foreach ($pinjamanQuery as $row) {
            $chartPinjaman[(int)$row['bulan']] = (float)$row['total'];
        }

        // Fetch monthly angsuran
        $angsuranQuery = $db->table('kop_angsuran')
            ->select('MONTH(tanggal_bayar) as bulan, SUM(nominal_bayar) as total')
            ->where('status', 'approved')
            ->where('YEAR(tanggal_bayar)', $currentYear)
            ->groupBy('MONTH(tanggal_bayar)')
            ->get()->getResultArray();
            
        foreach ($angsuranQuery as $row) {
            $chartAngsuran[(int)$row['bulan']] = (float)$row['total'];
        }

        $chartData = [
            'simpanan' => array_values($chartSimpanan),
            'pinjaman' => array_values($chartPinjaman),
            'angsuran' => array_values($chartAngsuran),
        ];

        return view('admin/cooperative/dashboard', [
            'title'              => 'Panel Koperasi - Ringkasan',
            'totalKas'           => $totalKas,
            'piutangAktif'       => $piutangAktif,
            'activeMembers'      => $activeMembers,
            'pendingSavings'     => $pendingSavings,
            'pendingLoans'       => $pendingLoans,
            'pendingInstallments'=> $pendingInstallments,
            'chartData'          => json_encode($chartData),
            'saldoKasUtama'      => $saldoKasUtama,
            'saldoDanaTalangan'  => $saldoDanaTalangan,
            'totalTarget'        => (float)$totalPinjaman,
            'totalTerkumpul'     => (float)$totalAngsuran,
        ]);
    }
}
