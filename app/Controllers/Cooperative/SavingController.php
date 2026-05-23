<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopSimpananModel;
use App\Models\KopKasInternalModel;
use App\Models\AuditLogModel;
use App\Models\KopAnggotaModel;
use App\Models\KopSettingModel;

class SavingController extends BaseController
{
    protected KopSimpananModel $simpananModel;
    protected KopKasInternalModel $kasInternalModel;
    protected KopAnggotaModel $anggotaModel;

    public function __construct()
    {
        $this->simpananModel = new KopSimpananModel();
        $this->kasInternalModel = new KopKasInternalModel();
        $this->anggotaModel = new KopAnggotaModel();
    }

    /**
     * Manage savings verifications.
     */
    public function savings()
    {
        $savings = $this->simpananModel->select('kop_simpanan.*, users.username, kop_anggota.nomor_anggota')
            ->join('kop_anggota', 'kop_anggota.id = kop_simpanan.anggota_id')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->orderBy('kop_simpanan.tanggal_transaksi', 'DESC')
            ->findAll();

        return view('admin/cooperative/savings', [
            'title'   => 'Panel Koperasi - Setoran & Penarikan',
            'savings' => $savings,
        ]);
    }

    /**
     * Approve savings deposit or withdrawal.
     */
    public function approveSaving(int $id)
    {
        $saving = $this->simpananModel->find($id);
        if (!$saving || $saving['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Transaksi simpanan tidak ditemukan atau sudah diproses.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $this->simpananModel->update($id, [
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => date('Y-m-d H:i:s'),
            ]);

            // If the transaction is marked as annual prepayment, expand it to 12 monthly wajib and sosial records
            if ($saving['jenis_simpanan'] === 'tahunan') {
                $wajibNominal = floatval(KopSettingModel::getSetting('kop_simpanan_wajib_nominal', '50000'));
                $sosialNominal = floatval(KopSettingModel::getSetting('kop_dana_sosial_nominal', '20000'));
                $targetYear = intval($saving['tahun'] ?: date('Y'));

                for ($m = 1; $m <= 12; $m++) {
                    // Insert Wajib
                    $this->simpananModel->insert([
                        'anggota_id'     => $saving['anggota_id'],
                        'jenis_simpanan' => 'wajib',
                        'tipe_transaksi' => 'setoran',
                        'nominal'        => $wajibNominal,
                        'status'         => 'approved',
                        'bukti_transfer' => $saving['bukti_transfer'],
                        'bulan'          => $m,
                        'tahun'          => $targetYear,
                        'keterangan'     => "Simpanan Wajib Bulanan (Ekspansi Tahunan {$targetYear} - Bulan {$m})",
                        'approved_by'    => auth()->id(),
                        'approved_at'    => date('Y-m-d H:i:s'),
                        'tanggal_transaksi'=> $saving['tanggal_transaksi'] ?: date('Y-m-d H:i:s'),
                    ]);

                    // Insert Sosial
                    $this->simpananModel->insert([
                        'anggota_id'     => $saving['anggota_id'],
                        'jenis_simpanan' => 'sosial',
                        'tipe_transaksi' => 'setoran',
                        'nominal'         => $sosialNominal,
                        'status'         => 'approved',
                        'bukti_transfer' => $saving['bukti_transfer'],
                        'bulan'          => $m,
                        'tahun'          => $targetYear,
                        'keterangan'     => "Dana Sosial Bulanan (Ekspansi Tahunan {$targetYear} - Bulan {$m})",
                        'approved_by'    => auth()->id(),
                        'approved_at'    => date('Y-m-d H:i:s'),
                        'tanggal_transaksi'=> $saving['tanggal_transaksi'] ?: date('Y-m-d H:i:s'),
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Gagal menyetujui transaksi simpanan.');
            }

            $anggota = $this->anggotaModel->find($saving['anggota_id']);
            $user = auth()->getProvider()->find($anggota['user_id']);
            $username = $user ? $user->username : 'Unknown';

            AuditLogModel::log('coop_saving_approved', "Menyetujui " . strtoupper($saving['tipe_transaksi']) . " Simpanan " . strtoupper($saving['jenis_simpanan']) . " senilai Rp " . number_format($saving['nominal'], 2) . " untuk Anggota '{$username}'");

            return redirect()->back()->with('message', 'Transaksi simpanan berhasil disetujui.');

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Reject savings deposit or withdrawal.
     */
    public function rejectSaving(int $id)
    {
        $saving = $this->simpananModel->find($id);
        if (!$saving || $saving['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Transaksi simpanan tidak ditemukan atau sudah diproses.');
        }

        $this->simpananModel->update($id, [
            'status'      => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $anggota = $this->anggotaModel->find($saving['anggota_id']);
        $user = auth()->getProvider()->find($anggota['user_id']);
        $username = $user ? $user->username : 'Unknown';

        AuditLogModel::log('coop_saving_rejected', "Menolak " . strtoupper($saving['tipe_transaksi']) . " Simpanan " . strtoupper($saving['jenis_simpanan']) . " senilai Rp " . number_format($saving['nominal'], 2) . " untuk Anggota '{$username}'");

        return redirect()->back()->with('message', 'Transaksi simpanan berhasil ditolak.');
    }
}
