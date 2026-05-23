<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopSettingModel;
use App\Traits\MemberTrait;

class LoansController extends BaseController
{
    use MemberTrait;

    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;

    public function __construct()
    {
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
    }

    /**
     * Mutasi Pinjaman & Simulasi.
     */
    public function loans()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        // Fetch user's loan lists with installments count
        $loans = $this->pinjamanModel->where('anggota_id', $member['id'])->orderBy('created_at', 'DESC')->findAll();
        foreach ($loans as &$l) {
            $l['approved_installments'] = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->countAllResults();
            $l['total_paid'] = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
            $l['pending_installments'] = $this->angsuranModel->where('pinjaman_id', $l['id'])->where('status', 'pending')->countAllResults();
        }

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

        return view('user/cooperative/loans', [
            'title'     => 'Riwayat & Pengajuan Pinjaman',
            'loans'     => $loans,
            'settings'  => $settings,
            'is_member' => true,
        ]);
    }

    /**
     * Submit a loan request.
     */
    public function requestLoan()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $rules = [
            'nominal_pinjaman' => 'required|numeric|greater_than[0]',
            'tenor_bulan'      => 'required|integer|greater_than[0]|less_than[61]',
            'keterangan'       => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nominal = floatval($this->request->getPost('nominal_pinjaman'));
        $tenor = intval($this->request->getPost('tenor_bulan'));

        $calc = KopPinjamanModel::calculateLoanDetails($nominal, $tenor);

        $this->pinjamanModel->insert([
            'anggota_id'       => $member['id'],
            'nominal_pinjaman' => $nominal,
            'tenor_bulan'      => $tenor,
            'bunga_persen'     => $calc['bunga_persen'],
            'nominal_total'    => $calc['nominal_total'],
            'jasa_nominal'     => $calc['jasa_nominal'],
            'metode_bayar_jasa'=> $calc['jasa_cara_bayar'],
            'jenis_bunga'      => $calc['jenis_bunga'],
            'bunga_opsi_bayar' => $calc['bunga_opsi_bayar'],
            'status'           => 'pending',
            'keterangan'       => esc($this->request->getPost('keterangan')),
        ]);

        return redirect()->to(base_url('cooperative/loans'))->with('message', 'Pengajuan pinjaman berhasil disubmit. Tim pengurus koperasi akan segera meninjau kelayakan kredit Anda.');
    }

    /**
     * Submit installment receipt attachment.
     */
    public function payInstallment(int $loanId)
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $loan = $this->pinjamanModel->where('id', $loanId)->where('anggota_id', $member['id'])->first();
        if (!$loan || $loan['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Pinjaman aktif tidak ditemukan.');
        }

        $rules = [
            'angsuran_ke' => 'required|integer|greater_than[0]',
            'bukti_bayar' => 'uploaded[bukti_bayar]|is_image[bukti_bayar]|max_size[bukti_bayar,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $angsuranKe = intval($this->request->getPost('angsuran_ke'));

        // Prevent double submit for the exact same installment number if pending/approved
        $exist = $this->angsuranModel->where('pinjaman_id', $loanId)->where('angsuran_ke', $angsuranKe)->whereIn('status', ['pending', 'approved'])->countAllResults();
        if ($exist > 0) {
            return redirect()->back()->with('error', "Pembayaran untuk Angsuran ke-{$angsuranKe} sudah diajukan sebelumnya.");
        }

        // Nominal is calculated flat per month: nominal_total / tenor_bulan
        $nominalBayar = floatval($loan['nominal_total']) / intval($loan['tenor_bulan']);

        $file = $this->request->getFile('bukti_bayar');
        $newName = '';
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/bukti_cicilan')) {
                mkdir(FCPATH . 'uploads/bukti_cicilan', 0777, true);
            }
            $file->move(FCPATH . 'uploads/bukti_cicilan', $newName);
        }

        $this->angsuranModel->insert([
            'pinjaman_id'   => $loanId,
            'angsuran_ke'   => $angsuranKe,
            'nominal_bayar' => $nominalBayar,
            'bukti_bayar'   => $newName,
            'status'        => 'pending',
            'tanggal_bayar' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('cooperative/loans'))->with('message', "Bukti angsuran ke-{$angsuranKe} berhasil diunggah. Status akan segera diperbarui.");
    }
}
