<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color as SpreadsheetColor;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ArrearsExcelBuilder
{
    /**
     * Build Excel workbook for a single member arrears report.
     */
    public function buildExcel(array $member, array $rows, int $year, array $kopData, array $signer): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tunggakan ' . $year);

        $tealDark   = '0F766E';
        $grayBorder = 'E2E8F0';
        $red        = 'EF4444';
        $green      = '10B981';
        $white      = 'FFFFFF';

        $colCount = 7; // A–G
        $colRange = 'A:G';

        $sheet->setCellValue('A1', strtoupper($kopData['cooperative_name']));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $kop2 = implode(' | ', array_filter([
            $kopData['legal_id'] ?? '',
            $kopData['address'] ?? '',
            $kopData['phone'] ?? '',
        ]));
        $sheet->setCellValue('A2', $kop2);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'LAPORAN TUNGGAKAN ANGSURAN ANGGOTA');
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDFA');

        $sheet->setCellValue('A4', 'Periode Tahun Buku: ' . $year);
        $sheet->mergeCells('A4:G4');
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true)->setColor(new SpreadsheetColor('475569'));
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A5', 'Nama Anggota: ' . ($member['username'] ?? '-'));
        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A6', 'No. Anggota: ' . ($member['nomor_anggota'] ?? '-'));
        $sheet->mergeCells('A6:C6');
        $sheet->getStyle('A5:A6')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('E5', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->mergeCells('E5:G5');
        $sheet->getStyle('E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E5')->getFont()->setSize(8)->setColor(new SpreadsheetColor('94A3B8'));

        $sheet->getRowDimension(7)->setRowHeight(6);

        $headers = ['No', 'Bulan', 'Tunggakan Wajib', 'Tunggakan Sosial', 'Tunggakan Jasa', 'Jumlah', 'Status'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '8', $h);
            $col++;
        }
        $sheet->getStyle('A8:G8')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getRowDimension(8)->setRowHeight(22);

        $rowNum = 9;
        $no = 1;
        $totalWajib = 0.0;
        $totalSosial = 0.0;
        $totalJasa = 0.0;
        $grandTotal = 0.0;
        $currencyFormat = '"Rp"#,##0';

        foreach ($rows as $r) {
            $isEven = ($no % 2 === 0);
            $rowBg  = $isEven ? 'F8FAFC' : 'FFFFFF';
            $hasArrears = $r['jumlah'] > 0;

            if ($hasArrears) $rowBg = 'FFF7ED';

            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, $r['bulan_nama']);
            $sheet->setCellValue('C' . $rowNum, $r['tunggakan_wajib']);
            $sheet->setCellValue('D' . $rowNum, $r['tunggakan_sosial']);
            $sheet->setCellValue('E' . $rowNum, $r['tunggakan_jasa']);
            $sheet->setCellValue('F' . $rowNum, $r['jumlah']);

            $pending = false;
            if ($hasArrears) {
                $pending = ($r['record_wajib'] && $r['record_wajib']['status'] === 'pending') || ($r['record_sosial'] && $r['record_sosial']['status'] === 'pending');
            }

            if ($hasArrears) {
                $statusTxt = $pending ? 'Menunggu' : 'Menunggak';
                $statusColor = $pending ? 'D97706' : $red;
            } else {
                $statusTxt = 'Lunas';
                $statusColor = $green;
            }

            $sheet->setCellValue('G' . $rowNum, strtoupper($statusTxt));
            $sheet->getStyle('G' . $rowNum)->getFont()->setBold(true)->setColor(new SpreadsheetColor($statusColor));

            foreach (['C', 'D', 'E', 'F'] as $c) {
                $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
            }

            if ($r['tunggakan_wajib'] > 0)  $sheet->getStyle('C' . $rowNum)->getFont()->getColor()->setRGB($red);
            if ($r['tunggakan_sosial'] > 0) $sheet->getStyle('D' . $rowNum)->getFont()->getColor()->setRGB($red);
            if ($r['tunggakan_jasa'] > 0)   $sheet->getStyle('E' . $rowNum)->getFont()->getColor()->setRGB($red);
            if ($hasArrears)                $sheet->getStyle('F' . $rowNum)->getFont()->setBold(true)->getColor()->setRGB($red);

            $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $grayBorder]]],
            ]);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('G' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $totalWajib  += $r['tunggakan_wajib'];
            $totalSosial += $r['tunggakan_sosial'];
            $totalJasa   += $r['tunggakan_jasa'];
            $grandTotal  += $r['jumlah'];

            $no++;
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, 'TOTAL TUNGGAKAN');
        $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
        $sheet->setCellValue('C' . $rowNum, $totalWajib);
        $sheet->setCellValue('D' . $rowNum, $totalSosial);
        $sheet->setCellValue('E' . $rowNum, $totalJasa);
        $sheet->setCellValue('F' . $rowNum, $grandTotal);
        $sheet->setCellValue('G' . $rowNum, '');

        foreach (['C', 'D', 'E', 'F'] as $c) {
            $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
        }
        $sheet->getStyle('A' . $rowNum . ':G' . $rowNum)->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => $white]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowNum += 2;

        $sheet->setCellValue('E' . $rowNum, 'Mengetahui,');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('E' . $rowNum, date('d F Y'));
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum += 4;

        $sheet->setCellValue('E' . $rowNum, $signer['name'] ?? 'Pengurus KSP');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('E' . $rowNum, $signer['role'] ?? 'Pengurus');
        $sheet->mergeCells('E' . $rowNum . ':G' . $rowNum);
        $sheet->getStyle('E' . $rowNum)->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }

    /**
     * Build Excel workbook for "Semua Anggota" recap.
     */
    public function buildExcelAllMembers(array $rows, int $year, array $months, array $kopData, array $signer): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Tunggakan ' . $year);

        $tealDark   = '0F766E';
        $grayBorder = 'E2E8F0';
        $red        = 'EF4444';
        $white      = 'FFFFFF';

        $colCount = 6; // A–F
        $colRange = 'A:F';

        $sheet->setCellValue('A1', strtoupper($kopData['cooperative_name']));
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $kop2 = implode(' | ', array_filter([
            $kopData['legal_id'] ?? '',
            $kopData['address'] ?? '',
            $kopData['phone'] ?? '',
        ]));
        $sheet->setCellValue('A2', $kop2);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Subtitle (period)
        $monthNames = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $firstMonth = $monthNames[min($months)] ?? '';
        $lastMonth  = $monthNames[max($months)] ?? '';
        $periodText = count($months) > 1 ? "$firstMonth - $lastMonth $year" : "$firstMonth $year";

        $sheet->setCellValue('A3', 'REKAPITULASI TUNGGAKAN ANGGOTA KSP');
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11)->setColor(new SpreadsheetColor($tealDark));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0FDFA');

        $sheet->setCellValue('A4', 'Periode: ' . $periodText);
        $sheet->mergeCells('A4:F4');
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true)->setColor(new SpreadsheetColor('475569'));
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A5', 'Dicetak: ' . date('d F Y H:i'));
        $sheet->mergeCells('A5:F5');
        $sheet->getStyle('A5')->getFont()->setSize(8)->setColor(new SpreadsheetColor('94A3B8'));
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(6)->setRowHeight(6);

        $headers = ['No', 'Nama Anggota', 'Tunggakan Wajib', 'Tunggakan Sosial', 'Tunggakan Jasa', 'Jumlah'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '7', $h);
            $col++;
        }
        $sheet->getStyle('A7:F7')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => $white], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(22);

        $rowNum = 8;
        $no = 1;
        $totalWajib = 0.0;
        $totalSosial = 0.0;
        $totalJasa = 0.0;
        $grandTotal = 0.0;
        $currencyFormat = '"Rp"#,##0';

        foreach ($rows as $r) {
            $isEven = ($no % 2 === 0);
            $rowBg  = $isEven ? 'F8FAFC' : 'FFFFFF';

            $sheet->setCellValue('A' . $rowNum, $no);
            $sheet->setCellValue('B' . $rowNum, $r['nama_anggota']);
            $sheet->setCellValue('C' . $rowNum, $r['tunggakan_wajib']);
            $sheet->setCellValue('D' . $rowNum, $r['tunggakan_sosial']);
            $sheet->setCellValue('E' . $rowNum, $r['tunggakan_jasa']);
            $sheet->setCellValue('F' . $rowNum, $r['jumlah']);

            foreach (['C', 'D', 'E', 'F'] as $c) {
                $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
            }

            $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $grayBorder]]],
            ]);
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $totalWajib  += $r['tunggakan_wajib'];
            $totalSosial += $r['tunggakan_sosial'];
            $totalJasa   += $r['tunggakan_jasa'];
            $grandTotal  += $r['jumlah'];

            $no++;
            $rowNum++;
        }

        $sheet->setCellValue('A' . $rowNum, 'GRAND TOTAL');
        $sheet->mergeCells('A' . $rowNum . ':B' . $rowNum);
        $sheet->setCellValue('C' . $rowNum, $totalWajib);
        $sheet->setCellValue('D' . $rowNum, $totalSosial);
        $sheet->setCellValue('E' . $rowNum, $totalJasa);
        $sheet->setCellValue('F' . $rowNum, $grandTotal);

        foreach (['C', 'D', 'E', 'F'] as $c) {
            $sheet->getStyle($c . $rowNum)->getNumberFormat()->setFormatCode($currencyFormat);
        }
        $sheet->getStyle('A' . $rowNum . ':F' . $rowNum)->applyFromArray([
            'font'    => ['bold' => true, 'color' => ['rgb' => $white]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $tealDark]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0D9488']]],
        ]);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $rowNum += 2;

        $sheet->setCellValue('D' . $rowNum, 'Mengetahui,');
        $sheet->mergeCells('D' . $rowNum . ':F' . $rowNum);
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('D' . $rowNum, date('d F Y'));
        $sheet->mergeCells('D' . $rowNum . ':F' . $rowNum);
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum += 4;

        $sheet->setCellValue('D' . $rowNum, $signer['name'] ?? 'Pengurus KSP');
        $sheet->mergeCells('D' . $rowNum . ':F' . $rowNum);
        $sheet->getStyle('D' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $rowNum++;

        $sheet->setCellValue('D' . $rowNum, $signer['role'] ?? 'Pengurus');
        $sheet->mergeCells('D' . $rowNum . ':F' . $rowNum);
        $sheet->getStyle('D' . $rowNum)->getFont()->setSize(9)->setColor(new SpreadsheetColor('64748B'));
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string) ob_get_clean();
    }
}
