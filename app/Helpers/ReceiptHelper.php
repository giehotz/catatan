<?php

namespace App\Helpers;

use App\Models\KopSettingModel;
use App\Models\KopAngsuranModel;
use App\Helpers\SuratHelper;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReceiptHelper
{
    public static function generateInstallmentReceiptPdf(array $submission, array $loan, array $memberData)
    {
        $kopSetting = [
            'nama_koperasi'    => KopSettingModel::getSetting('nama_koperasi', 'KOPERASI SIMPAN PINJAM'),
            'alamat_koperasi'  => KopSettingModel::getSetting('alamat_koperasi', '-'),
            'telepon_koperasi' => KopSettingModel::getSetting('telepon_koperasi', '-'),
            'email_koperasi'   => KopSettingModel::getSetting('email_koperasi', '-'),
            'logo_url'         => KopSettingModel::getSetting('logo_url', ''),
        ];

        // Ensure fallback texts
        if (empty($kopSetting['nama_koperasi'])) $kopSetting['nama_koperasi'] = 'KOPERASI SIMPAN PINJAM';
        if (empty($kopSetting['alamat_koperasi'])) $kopSetting['alamat_koperasi'] = '-';

        $signer = SuratHelper::getSigner('bendahara');
        if (empty($signer['name'])) {
            $signer = SuratHelper::getSigner('default');
        }

        // Fetch User details
        $username = $memberData['username'] ?? 'Unknown Member';
        $nomorAnggota = $memberData['nomor_anggota'] ?? '-';
        if (!isset($memberData['username']) && isset($memberData['user_id'])) {
            $user = auth()->getProvider()->find($memberData['user_id']);
            $username = $user ? $user->username : 'Unknown';
        }

        // Fetch precise angsuran lines distributed by this submission
        $angsuranLines = \Config\Database::connect()->table('kop_angsuran')
                            ->where('submission_id_fk', $submission['id'])
                            ->orderBy('angsuran_ke', 'ASC')
                            ->get()->getResultArray();

        $data = [
            'kop' => $kopSetting,
            'submission' => $submission,
            'loan' => $loan,
            'username' => $username,
            'nomor_anggota' => $nomorAnggota,
            'signer' => $signer,
            'angsuranLines' => $angsuranLines,
        ];

        $html = view('partials/cooperative/installment_receipt_pdf', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A5', 'landscape');
        $dompdf->render();

        $filename = "Kuitansi_Angsuran_" . $submission['id'] . "_" . date('Ymd', strtotime($submission['approved_at'])) . ".pdf";
        $dompdf->stream($filename, ["Attachment" => true]);
    }
}
