<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopSettingModel;
use App\Models\KopAnggotaModel;
use App\Models\KopPinjamanModel;
use App\Models\ReceivableModel;
use App\Models\KopAngsuranModel;
use App\Models\KopKasInternalModel;
use App\Models\DebtModel;
use App\Models\AuditLogModel;

class DirectLoanController extends BaseController
{
    protected KopAnggotaModel $anggotaModel;
    protected KopPinjamanModel $pinjamanModel;
    protected ReceivableModel $receivableModel;
    protected KopKasInternalModel $kasInternalModel;
    protected DebtModel $debtModel;

    public function __construct()
    {
        $this->anggotaModel = new KopAnggotaModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->receivableModel = new ReceivableModel();
        $this->kasInternalModel = new KopKasInternalModel();
        $this->debtModel = new DebtModel();
    }

    /**
     * Check if direct loan is enabled.
     */
    protected function isDirectLoanEnabled(): bool
    {
        return KopSettingModel::getSetting('direct_loan_enabled', '0') === '1';
    }

    /**
     * Check access to direct loan feature.
     * Managers are blocked if feature is disabled.
     */
    protected function checkDirectLoanAccess()
    {
        $user = auth()->user();
        $isAdmin = $user->inGroup('admin') || $user->inGroup('superadmin');
        
        if (!$isAdmin && !$this->isDirectLoanEnabled()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Fitur pemberian pinjaman langsung dinonaktifkan oleh Administrator.');
        }
    }

    /**
     * Show form to grant direct loan.
     */
    public function directLoanForm()
    {
        $this->checkDirectLoanAccess();

        // Fetch active members
        $members = $this->anggotaModel->select('kop_anggota.id, users.username, kop_anggota.nomor_anggota')
            ->join('users', 'users.id = kop_anggota.user_id')
            ->where('kop_anggota.status_keaktifan', 'aktif')
            ->orderBy('users.username', 'ASC')
            ->findAll();

        // Fetch financial settings for live simulation
        $settingsKeys = [
            'kop_bunga_pinjaman_persen',
            'kop_bunga_pinjaman_jenis',
            'kop_bunga_pinjaman_periode',
            'kop_bunga_pinjaman_opsi_bayar',
            'kop_jasa_pinjaman_nominal',
            'kop_jasa_pinjaman_jenis',
            'kop_jasa_pinjaman_cara_bayar',
        ];
        $settings = [];
        foreach ($settingsKeys as $key) {
            $settings[$key] = KopSettingModel::getSetting($key);
        }

        return view('admin/cooperative/direct_loan', [
            'title'    => 'Panel Koperasi - Berikan Pinjaman Langsung',
            'members'  => $members,
            'settings' => $settings,
        ]);
    }

    /**
     * Store and disburse direct loan.
     */
    public function storeDirectLoan()
    {
        $this->checkDirectLoanAccess();

        $rules = [
            'anggota_id'       => 'required|numeric',
            'nominal_pinjaman' => 'required|numeric|greater_than[0]',
            'tenor_bulan'      => 'required|integer|greater_than[0]|less_than[61]',
            'sumber_dana'      => 'required|in_list[kas_utama,dana_talangan]',
            'bunga_opsi_bayar' => 'required|in_list[cicil,di_awal]',
            'metode_bayar_jasa'=> 'required|in_list[cicil,di_awal]',
            'keterangan'       => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $anggotaId = intval($this->request->getPost('anggota_id'));
        $nominal = floatval($this->request->getPost('nominal_pinjaman'));
        $tenor = intval($this->request->getPost('tenor_bulan'));
        $sumberDana = $this->request->getPost('sumber_dana');
        $keterangan = $this->request->getPost('keterangan');

        // Per-loan payment method overrides from form
        $formBungaOpsiBayar = $this->request->getPost('bunga_opsi_bayar');
        $formMetodeBayarJasa = $this->request->getPost('metode_bayar_jasa');

        // Verify membership
        $anggota = $this->anggotaModel->find($anggotaId);
        if (!$anggota || $anggota['status_keaktifan'] !== 'aktif') {
            return redirect()->back()->withInput()->with('error', 'Keanggotaan pemohon tidak aktif atau ditangguhkan.');
        }

        // Verify internal cash pool balance
        $saldoKas = $this->kasInternalModel->getSaldo($sumberDana);
        if ($saldoKas < $nominal) {
            return redirect()->back()->withInput()->with('error', "Pencairan gagal. Saldo {$sumberDana} (Rp " . number_format($saldoKas, 0, ',', '.') . ") tidak mencukupi.");
        }

        // Get base calculation from settings
        $calc = KopPinjamanModel::calculateLoanDetails($nominal, $tenor);
        $bungaPersen = $calc['bunga_persen'];

        // Override payment methods with form selections
        $bungaTotal = $calc['bunga_total'];
        $jasaTotal = $calc['jasa_nominal'];

        $bungaDiAwal = ($formBungaOpsiBayar === 'di_awal') ? $bungaTotal : 0.00;
        $bungaCicilan = ($formBungaOpsiBayar === 'cicil') ? $bungaTotal : 0.00;
        $jasaDiAwal = ($formMetodeBayarJasa === 'di_awal') ? $jasaTotal : 0.00;
        $jasaCicilan = ($formMetodeBayarJasa === 'cicil') ? $jasaTotal : 0.00;

        // Recalculate total repayment (only installment portions)
        $totalRepayment = $nominal + $bungaCicilan + $jasaCicilan;

        $userModel = auth()->getProvider();
        $user = $userModel->find($anggota['user_id']);
        $username = $user ? $user->username : 'Anggota';

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // A. Create approved loan in kop_pinjaman
            $this->pinjamanModel->insert([
                'anggota_id'       => $anggotaId,
                'nominal_pinjaman' => $nominal,
                'tenor_bulan'      => $tenor,
                'bunga_persen'     => $bungaPersen,
                'nominal_total'    => $totalRepayment,
                'jasa_nominal'     => $jasaTotal,
                'metode_bayar_jasa'=> $formMetodeBayarJasa,
                'jenis_bunga'      => $calc['jenis_bunga'],
                'bunga_opsi_bayar' => $formBungaOpsiBayar,
                'status'           => 'approved',
                'keterangan'       => esc($keterangan) ?: 'Pemberian pinjaman langsung oleh pengelola.',
                'approved_by'      => auth()->id(),
                'approved_at'      => date('Y-m-d H:i:s'),
            ]);
            $loanId = $this->pinjamanModel->getInsertID();

            // B. Create corresponding entry in Debts table for the member
            $dueDate = date('Y-m-d', strtotime("+{$tenor} months"));
            $bungaDesc = floatval($bungaPersen) . "% " . $calc['jenis_bunga'];
            $debtId = $this->debtModel->insert([
                'user_id'       => $anggota['user_id'],
                'creditor_name' => 'Koperasi Simpan Pinjam',
                'total_amount'  => $totalRepayment,
                'description'   => "Utang Pinjaman Langsung Koperasi (Tenor {$tenor} Bulan, Bunga {$bungaDesc})",
                'due_date'      => $dueDate,
                'status'        => 'unpaid',
            ]);

            // C. Create corresponding entry in Receivables table
            $receivableId = $this->receivableModel->insert([
                'user_id'       => auth()->id(), 
                'borrower_name' => esc($username),
                'total_amount'  => $totalRepayment,
                'description'   => "Piutang Pinjaman Langsung Anggota '{$username}' (ID Koperasi: {$loanId})",
                'due_date'      => $dueDate,
                'status'        => 'unpaid',
            ]);

            // D. Update kop_pinjaman foreign key references
            $this->pinjamanModel->update($loanId, [
                'debt_id_fk'       => $debtId,
                'receivable_id_fk' => $receivableId,
            ]);

            // E. Disburse cash from cooperative kas_internal
            $this->kasInternalModel->insert([
                'kategori_dana'   => $sumberDana,
                'jenis_transaksi' => 'pengeluaran',
                'nominal'         => $nominal,
                'reference_type'  => 'pinjaman',
                'reference_id'    => $loanId,
                'keterangan'      => "Pencairan Pinjaman Langsung ID: {$loanId} untuk Anggota '{$username}'",
                'created_by'      => auth()->id(),
            ]);

            // F. Potongan upfront bunga / jasa jika dibayar di awal
            if ($bungaDiAwal > 0) {
                $this->kasInternalModel->insert([
                    'kategori_dana'   => $sumberDana,
                    'jenis_transaksi' => 'pemasukan',
                    'nominal'         => $bungaDiAwal,
                    'reference_type'  => 'pinjaman',
                    'reference_id'    => $loanId,
                    'keterangan'      => "Potongan Bunga di Awal Pinjaman Langsung ID: {$loanId} untuk Anggota '{$username}'",
                    'created_by'      => auth()->id(),
                ]);
            }

            if ($jasaDiAwal > 0) {
                $this->kasInternalModel->insert([
                    'kategori_dana'   => $sumberDana,
                    'jenis_transaksi' => 'pemasukan',
                    'nominal'         => $jasaDiAwal,
                    'reference_type'  => 'pinjaman',
                    'reference_id'    => $loanId,
                    'keterangan'      => "Potongan Jasa di Awal Pinjaman Langsung ID: {$loanId} untuk Anggota '{$username}'",
                    'created_by'      => auth()->id(),
                ]);
            }

            // G. Log Audit entry
            AuditLogModel::log('coop_direct_loan_granted', "Pemberian pinjaman langsung untuk Anggota '{$username}' senilai Rp " . number_format($nominal, 2) . " dari {$sumberDana}. Bunga: {$formBungaOpsiBayar}, Jasa: {$formMetodeBayarJasa}.");

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Pencairan gagal karena kesalahan sistem.');
            }

            return redirect()->to(base_url('admin/cooperative/loans'))->with('message', "Pinjaman langsung senilai Rp " . number_format($nominal, 0, ',', '.') . " untuk {$username} berhasil dicairkan.");

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
}
