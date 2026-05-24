<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\KopSettingModel;
use App\Traits\MemberTrait;
use App\Services\InstallmentService;

class LoansController extends BaseController
{
    use MemberTrait;

    protected KopPinjamanModel $pinjamanModel;
    protected KopAngsuranModel $angsuranModel;
    protected InstallmentService $installmentService;

    public function __construct()
    {
        $this->pinjamanModel = new KopPinjamanModel();
        $this->angsuranModel = new KopAngsuranModel();
        $this->installmentService = new InstallmentService();
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
            $l['pending_installments'] = \Config\Database::connect()->table('kop_angsuran_submissions')->where('pinjaman_id', $l['id'])->where('status', 'pending')->countAllResults();
            $l['pending_submissions_amount'] = \Config\Database::connect()->table('kop_angsuran_submissions')->where('pinjaman_id', $l['id'])->where('status', 'pending')->selectSum('nominal_pengajuan')->get()->getRow()->nominal_pengajuan ?? 0;
            
            // Get submissions history for the view
            $l['submissions'] = \Config\Database::connect()->table('kop_angsuran_submissions')
                                ->where('pinjaman_id', $l['id'])
                                ->orderBy('created_at', 'DESC')
                                ->get()
                                ->getResultArray();
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
            'nominal_bayar' => 'required|numeric|greater_than[0]',
            'bukti_bayar' => 'uploaded[bukti_bayar]|is_image[bukti_bayar]|max_size[bukti_bayar,2048]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nominalBayar = floatval($this->request->getPost('nominal_bayar'));

        // Validate overpayment including pending submissions
        $totalApproved = $this->angsuranModel->where('pinjaman_id', $loanId)->where('status', 'approved')->selectSum('nominal_bayar')->first()['nominal_bayar'] ?? 0;
        $totalPendingSubmissions = \Config\Database::connect()->table('kop_angsuran_submissions')->where('pinjaman_id', $loanId)->where('status', 'pending')->selectSum('nominal_pengajuan')->get()->getRow()->nominal_pengajuan ?? 0;
        
        $sisaHutang = floatval($loan['nominal_total']) - floatval($totalApproved) - floatval($totalPendingSubmissions);
        if ($nominalBayar > $sisaHutang) {
            return redirect()->back()->withInput()->with('error', "Nominal transfer (Rp " . number_format($nominalBayar, 2, ',', '.') . ") melebihi batas maksimal yang diperbolehkan (Rp " . number_format($sisaHutang, 2, ',', '.') . "). Harap cek ulang sisa tagihan dan pengajuan Anda yang masih pending.");
        }

        $file = $this->request->getFile('bukti_bayar');
        $newName = '';
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/bukti_cicilan')) {
                mkdir(FCPATH . 'uploads/bukti_cicilan', 0777, true);
            }
            $file->move(FCPATH . 'uploads/bukti_cicilan', $newName);
        }

        try {
            $this->installmentService->submitUserPayment($loan, $nominalBayar, $newName);
            return redirect()->to(base_url('cooperative/loans'))->with('message', "Bukti transfer angsuran sebesar Rp " . number_format($nominalBayar, 2, ',', '.') . " berhasil diunggah dan sedang menunggu validasi admin.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function printReceipt(int $submissionId)
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $submission = \Config\Database::connect()->table('kop_angsuran_submissions')->where('id', $submissionId)->get()->getRowArray();
        if (!$submission || $submission['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Kuitansi tidak ditemukan atau belum disetujui.');
        }

        // Validate ownership securely
        $loan = $this->pinjamanModel->where('id', $submission['pinjaman_id'])->where('anggota_id', $member['id'])->first();
        if (!$loan) {
            return redirect()->back()->with('error', 'Kuitansi tidak ditemukan.');
        }

        return \App\Helpers\ReceiptHelper::generateInstallmentReceiptPdf($submission, $loan, $member);
    }
}
