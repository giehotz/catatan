<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopAnggotaModel;
use App\Models\KopSimpananModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Traits\MemberTrait;

class DashboardController extends BaseController
{
    use MemberTrait;

    protected KopSimpananModel $simpananModel;
    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;

    public function __construct()
    {
        $this->simpananModel = new KopSimpananModel();
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
    }

    /**
     * Cooperative Member Hub Dashboard.
     */
    public function index()
    {
        if (!auth()->loggedIn()) {
            return redirect()->to(base_url('login'));
        }

        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member; // RedirectResponse
        }

        // Calculate member savings balances
        $savings = $this->simpananModel->where('anggota_id', $member['id'])->where('status', 'approved')->findAll();
        $saldoPokok = 0;
        $saldoWajib = 0;
        $saldoSukarela = 0;

        foreach ($savings as $s) {
            $nominal = floatval($s['nominal']);
            if ($s['tipe_transaksi'] === 'setoran') {
                if ($s['jenis_simpanan'] === 'pokok') $saldoPokok += $nominal;
                if ($s['jenis_simpanan'] === 'wajib') $saldoWajib += $nominal;
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela += $nominal;
            } else {
                // Penarikan (only allowed for sukarela)
                if ($s['jenis_simpanan'] === 'sukarela') $saldoSukarela -= $nominal;
            }
        }
        $totalSimpanan = $saldoPokok + $saldoWajib + $saldoSukarela;

        // Calculate member debts
        $activeLoans = $this->pinjamanModel->where('anggota_id', $member['id'])->whereIn('status', ['approved', 'paid'])->findAll();
        $totalDebtLimit = 0;
        $totalPaidInstallments = 0;

        foreach ($activeLoans as $l) {
            $totalDebtLimit += floatval($l['nominal_total']);
            $paid = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
            $totalPaidInstallments += floatval($paid);
        }

        $sisaPinjaman = $totalDebtLimit - $totalPaidInstallments;

        // Fetch loan requests and installment schedules
        $loans = $this->pinjamanModel->where('anggota_id', $member['id'])->orderBy('created_at', 'DESC')->findAll();

        return view('user/cooperative/hub', [
            'title'          => 'Koperasi Saya',
            'member'         => $member,
            'saldoPokok'     => $saldoPokok,
            'saldoWajib'     => $saldoWajib,
            'saldoSukarela'  => $saldoSukarela,
            'totalSimpanan'  => $totalSimpanan,
            'sisaPinjaman'   => $sisaPinjaman,
            'loans'          => $loans,
            'is_member'      => true,
        ]);
    }

    /**
     * Cooperative Terms and Conditions Page.
     */
    public function terms()
    {
        return view('user/cooperative/terms', [
            'title'     => 'Syarat & Ketentuan Koperasi',
            'is_member' => auth()->loggedIn() && is_object($this->getMemberOrRedirect()) === false,
        ]);
    }
}
