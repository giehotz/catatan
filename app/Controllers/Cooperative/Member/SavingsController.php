<?php

namespace App\Controllers\Cooperative\Member;

use App\Controllers\BaseController;
use App\Models\KopSimpananModel;
use App\Traits\MemberTrait;

class SavingsController extends BaseController
{
    use MemberTrait;

    protected KopSimpananModel $simpananModel;

    public function __construct()
    {
        $this->simpananModel = new KopSimpananModel();
    }

    /**
     * Mutasi & Pencatatan Simpanan page.
     */
    public function savings()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $savingsList = $this->simpananModel->where('anggota_id', $member['id'])
            ->orderBy('tanggal_transaksi', 'DESC')
            ->findAll();

        $bankAccounts = [];
        for ($i = 1; $i <= 2; $i++) {
            $nama = \App\Models\KopSettingModel::getSetting("kop_rekening_bank_{$i}_nama");
            $nomor = \App\Models\KopSettingModel::getSetting("kop_rekening_bank_{$i}_nomor");
            $atasNama = \App\Models\KopSettingModel::getSetting("kop_rekening_bank_{$i}_atas_nama");
            if (!empty($nama) && !empty($nomor)) {
                $bankAccounts[] = [
                    'nama'      => $nama,
                    'nomor'     => $nomor,
                    'atas_nama' => $atasNama,
                ];
            }
        }

        return view('user/cooperative/savings', [
            'title'        => 'Mutasi & Simpanan Saya',
            'savingsList'  => $savingsList,
            'is_member'    => true,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Submit a setoran (deposit) with bank receipt attachment.
     */
    public function deposit()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $rules = [
            'jenis_simpanan' => 'required|in_list[pokok,wajib,sukarela]',
            'nominal'        => 'required|numeric|greater_than[0]',
            'bukti_transfer' => 'uploaded[bukti_transfer]|is_image[bukti_transfer]|max_size[bukti_transfer,2048]',
            'keterangan'     => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('bukti_transfer');
        $newName = '';
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if (!is_dir(FCPATH . 'uploads/bukti_setoran')) {
                mkdir(FCPATH . 'uploads/bukti_setoran', 0777, true);
            }
            $file->move(FCPATH . 'uploads/bukti_setoran', $newName);
        }

        $this->simpananModel->insert([
            'anggota_id'     => $member['id'],
            'jenis_simpanan' => $this->request->getPost('jenis_simpanan'),
            'tipe_transaksi' => 'setoran',
            'nominal'        => $this->request->getPost('nominal'),
            'status'         => 'pending',
            'bukti_transfer' => $newName,
            'keterangan'     => esc($this->request->getPost('keterangan')),
        ]);

        return redirect()->to(base_url('cooperative/savings'))->with('message', 'Permintaan setoran simpanan berhasil diajukan. Menunggu verifikasi pengelola.');
    }

    /**
     * Submit a penarikan (withdrawal) request (Only allowed from approved sukarela savings).
     */
    public function withdraw()
    {
        $member = $this->getMemberOrRedirect();
        if (is_object($member) && method_exists($member, 'getStatusCode')) {
            return $member;
        }

        $rules = [
            'nominal'    => 'required|numeric|greater_than[0]',
            'keterangan' => 'required|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nominal = floatval($this->request->getPost('nominal'));

        // 1. Calculate approved sukarela balance
        $savings = $this->simpananModel->where('anggota_id', $member['id'])->where('status', 'approved')->findAll();
        $saldoSukarela = 0;
        foreach ($savings as $s) {
            if ($s['jenis_simpanan'] === 'sukarela') {
                if ($s['tipe_transaksi'] === 'setoran') {
                    $saldoSukarela += floatval($s['nominal']);
                } else {
                    $saldoSukarela -= floatval($s['nominal']);
                }
            }
        }

        // Also subtract pending withdrawals to prevent double-spending/race conditions
        $pendingWithdrawals = $this->simpananModel->where('anggota_id', $member['id'])
            ->where('status', 'pending')
            ->where('tipe_transaksi', 'penarikan')
            ->selectSum('nominal')
            ->first()['nominal'] ?? 0;

        $usableSaldo = $saldoSukarela - floatval($pendingWithdrawals);

        if ($nominal > $usableSaldo) {
            return redirect()->back()->withInput()->with('error', 'Saldo Simpanan Sukarela Anda tidak mencukupi untuk melakukan penarikan ini. Saldo yang dapat ditarik saat ini: Rp ' . number_format($usableSaldo, 2));
        }

        $this->simpananModel->insert([
            'anggota_id'     => $member['id'],
            'jenis_simpanan' => 'sukarela',
            'tipe_transaksi' => 'penarikan',
            'nominal'        => $nominal,
            'status'         => 'pending',
            'bukti_transfer' => null,
            'keterangan'     => esc($this->request->getPost('keterangan')),
        ]);

        return redirect()->to(base_url('cooperative/savings'))->with('message', 'Permintaan penarikan simpanan sukarela berhasil diajukan. Pengurus akan segera memverifikasi.');
    }
}
