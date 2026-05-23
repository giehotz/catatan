<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopKasInternalModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\AuditLogModel;

class CashController extends BaseController
{
    protected KopKasInternalModel $kasInternalModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;

    public function __construct()
    {
        $this->kasInternalModel = new KopKasInternalModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
    }

    public function funds()
    {
        // Saldo
        $saldoKasUtama = $this->kasInternalModel->getSaldo('kas_utama');
        $saldoDanaTalangan = $this->kasInternalModel->getSaldo('dana_talangan');

        // Target Angsuran vs Terkumpul (Piutang yang sudah disetujui, dikurangi yg udah lunas jika status paid memengaruhi)
        $totalTarget = $this->pinjamanModel->whereIn('status', ['approved', 'paid'])->selectSum('nominal_total')->first()['nominal_total'] ?? 0;
        $totalTerkumpul = $this->angsuranModel->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;

        // Riwayat
        $riwayatDana = $this->kasInternalModel->orderBy('tanggal_transaksi', 'DESC')->findAll();

        $data = [
            'title' => 'Kelola Kas & Dana Eksternal',
            'saldoKasUtama' => $saldoKasUtama,
            'saldoDanaTalangan' => $saldoDanaTalangan,
            'totalTarget' => (float)$totalTarget,
            'totalTerkumpul' => (float)$totalTerkumpul,
            'riwayatDana' => $riwayatDana
        ];

        return view('admin/cooperative/funds', $data);
    }

    public function storeFund()
    {
        $jenis = $this->request->getPost('jenis_transaksi'); // pemasukan, pengeluaran, transfer_internal
        $nominal = floatval(str_replace(',', '', $this->request->getPost('nominal')));
        $keterangan = $this->request->getPost('keterangan');

        if ($nominal <= 0) {
            return redirect()->back()->with('error', 'Nominal harus lebih dari 0.');
        }

        if ($jenis === 'transfer_internal') {
            $dari = $this->request->getPost('kategori_dari');
            $ke = $this->request->getPost('kategori_ke');
            
            if ($dari === $ke) {
                return redirect()->back()->with('error', 'Sumber dan tujuan kas tidak boleh sama.');
            }
            
            $saldoDari = $this->kasInternalModel->getSaldo($dari);
            if ($saldoDari < $nominal) {
                return redirect()->back()->with('error', 'Saldo kas asal tidak mencukupi untuk transfer.');
            }
            
            $this->kasInternalModel->transferFunds($dari, $ke, $nominal, auth()->id(), $keterangan);
            AuditLogModel::log('coop_fund_transfer', "Transfer Rp " . number_format($nominal, 2) . " dari {$dari} ke {$ke}");
            
            return redirect()->back()->with('message', 'Transfer antar kas internal berhasil dicatat.');
        } else {
            $kategori = $this->request->getPost('kategori_dana');
            
            if ($jenis === 'pengeluaran') {
                $saldo = $this->kasInternalModel->getSaldo($kategori);
                if ($saldo < $nominal) {
                    return redirect()->back()->with('error', 'Saldo kas tidak mencukupi untuk pengeluaran.');
                }
            }
            
            $this->kasInternalModel->insert([
                'kategori_dana'   => $kategori,
                'jenis_transaksi' => $jenis,
                'nominal'         => $nominal,
                'reference_type'  => 'manual',
                'keterangan'      => $keterangan,
                'created_by'      => auth()->id(),
            ]);
            
            AuditLogModel::log('coop_fund_manual', ucfirst($jenis) . " Kas ({$kategori}) senilai Rp " . number_format($nominal, 2));
            
            return redirect()->back()->with('message', 'Transaksi mutasi kas berhasil dicatat.');
        }
    }
}
