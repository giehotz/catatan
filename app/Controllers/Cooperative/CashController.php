<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopKasInternalModel;
use App\Models\KopPinjamanModel;
use App\Models\KopAngsuranModel;
use App\Models\AuditLogModel;
use App\Models\KopSettingModel;
use App\Helpers\SuratHelper;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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
        $riwayatDana = $this->kasInternalModel
            ->orderBy('tanggal_transaksi', 'DESC')
            ->findAll(50);

        // Audit Trail — cooperative fund actions only
        $auditLogModel = new AuditLogModel();
        $auditTrail = $auditLogModel
            ->select('audit_logs.*, users.username')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->whereIn('audit_logs.action', ['coop_fund_transfer', 'coop_fund_manual'])
            ->orderBy('audit_logs.created_at', 'DESC')
            ->findAll(30);

        // Tambahin nomor urut
        foreach ($auditTrail as &$row) {
            $row['actor'] = $row['username'] ?? 'System';
        }

        $data = [
            'title' => 'Kelola Kas & Dana Eksternal',
            'saldoKasUtama' => $saldoKasUtama,
            'saldoDanaTalangan' => $saldoDanaTalangan,
            'totalTarget' => (float)$totalTarget,
            'totalTerkumpul' => (float)$totalTerkumpul,
            'riwayatDana' => $riwayatDana,
            'auditTrail' => $auditTrail,
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

    public function exportPdf()
    {
        $bulan = (int)$this->request->getPost('bulan');
        $tahun = (int)$this->request->getPost('tahun');

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return redirect()->back()->with('error', 'Parameter bulan atau tahun tidak valid.');
        }

        $riwayatDana = $this->kasInternalModel
            ->where('MONTH(tanggal_transaksi)', $bulan)
            ->where('YEAR(tanggal_transaksi)', $tahun)
            ->orderBy('tanggal_transaksi', 'ASC')
            ->findAll();

        if (empty($riwayatDana)) {
            return redirect()->back()->with('error', 'Tidak ada data riwayat mutasi pada periode tersebut.');
        }

        $kopSetting = [
            'nama_koperasi'    => KopSettingModel::getSetting('nama_koperasi', 'KOPERASI SIMPAN PINJAM'),
            'alamat_koperasi'  => KopSettingModel::getSetting('alamat_koperasi', '-'),
            'telepon_koperasi' => KopSettingModel::getSetting('telepon_koperasi', '-'),
            'email_koperasi'   => KopSettingModel::getSetting('email_koperasi', '-'),
            'logo_url'         => KopSettingModel::getSetting('logo_url', ''),
            'work_region'      => KopSettingModel::getSetting('kop_wilayah_kerja', 'Jakarta'),
        ];
        $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $periode = $bulanList[$bulan - 1] . " " . $tahun;

        $signer = SuratHelper::getSigner('default');

        $data = [
            'kop' => $kopSetting,
            'riwayatDana' => $riwayatDana,
            'periode' => $periode,
            'signer' => $signer
        ];

        $html = view('admin/cooperative/partials/funds_pdf_template', $data);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $dompdf->stream("Riwayat_Mutasi_Kas_Koperasi_{$periode}.pdf", ["Attachment" => true]);
    }

    public function exportExcel()
    {
        $bulan = (int)$this->request->getPost('bulan');
        $tahun = (int)$this->request->getPost('tahun');

        if ($bulan < 1 || $bulan > 12 || $tahun < 2000 || $tahun > 2100) {
            return redirect()->back()->with('error', 'Parameter bulan atau tahun tidak valid.');
        }

        $riwayatDana = $this->kasInternalModel
            ->where('MONTH(tanggal_transaksi)', $bulan)
            ->where('YEAR(tanggal_transaksi)', $tahun)
            ->orderBy('tanggal_transaksi', 'ASC')
            ->findAll();

        if (empty($riwayatDana)) {
            return redirect()->back()->with('error', 'Tidak ada data riwayat mutasi pada periode tersebut.');
        }

        $bulanList = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $periode = $bulanList[$bulan - 1] . " " . $tahun;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $kopSetting = [
            'nama_koperasi'    => KopSettingModel::getSetting('nama_koperasi', 'KOPERASI SIMPAN PINJAM'),
        ];
        $namaKoperasi = $kopSetting['nama_koperasi'];

        // Title
        $sheet->setCellValue('A1', $namaKoperasi);
        $sheet->setCellValue('A2', "Laporan Riwayat Mutasi Kas (Audit Trail)");
        $sheet->setCellValue('A3', "Periode: " . $periode);

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Header
        $headers = ['No', 'Tanggal', 'Kas (Kategori)', 'Transaksi', 'Nominal (Rp)', 'Keterangan'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']], // Slate-800
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A5:F5')->applyFromArray($headerStyle);

        // Data Rows
        $rowNum = 6;
        $no = 1;
        foreach ($riwayatDana as $row) {
            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, date('d/m/Y H:i', strtotime($row['tanggal_transaksi'])));
            $sheet->setCellValue('C' . $rowNum, strtoupper(str_replace('_', ' ', $row['kategori_dana'])));
            
            $jenisLabel = 'Mutasi';
            if ($row['jenis_transaksi'] === 'pemasukan') $jenisLabel = 'Masuk';
            if ($row['jenis_transaksi'] === 'pengeluaran') $jenisLabel = 'Keluar';
            
            $sheet->setCellValue('D' . $rowNum, strtoupper($jenisLabel));
            
            // Nominal Formatting
            $cellE = 'E' . $rowNum;
            $sheet->setCellValue($cellE, $row['nominal']);
            $sheet->getStyle($cellE)->getNumberFormat()->setFormatCode('"Rp"#,##0');

            // Warna Baris Berdasarkan Jenis Transaksi
            if ($row['jenis_transaksi'] === 'pemasukan') {
                $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->getColor()->setRGB('10B981'); // Hijau (Emerald-500)
            } elseif ($row['jenis_transaksi'] === 'pengeluaran') {
                $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->getColor()->setRGB('F43F5E'); // Merah (Rose-500)
            } else {
                $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->getColor()->setRGB('8B5CF6'); // Ungu (Purple-500) untuk Mutasi/Lainnya
            }

            $sheet->setCellValue('F' . $rowNum, $row['keterangan']);

            // Border
            $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $rowNum++;
            $no++;
        }

        // Auto Size Columns
        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Output
        $writer = new Xlsx($spreadsheet);
        $filename = "Riwayat_Mutasi_Kas_Koperasi_{$periode}.xlsx";

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit();
    }
}
