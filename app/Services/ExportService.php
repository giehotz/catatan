<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportService
{
    protected TransactionModel $transactionModel;
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
    }

    /**
     * Fetch filtered transactions for a user
     */
    public function getFilteredTransactions(int $userId, array $filters): array
    {
        $this->transactionModel->resetQuery();
        $this->transactionModel
            ->select('transactions.*, COALESCE(ic.name, ec.name, \'Kategori Dihapus\') as category_name')
            ->join('income_categories ic', "transactions.category_id = ic.id AND transactions.type = 'income'", 'left')
            ->join('expense_categories ec', "transactions.category_id = ec.id AND transactions.type = 'expense'", 'left')
            ->where('transactions.user_id', $userId);

        if (!empty($filters['type'])) {
            $this->transactionModel->where('transactions.type', $filters['type']);
        }
        if (!empty($filters['start_date'])) {
            $this->transactionModel->where('transactions.transaction_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->transactionModel->where('transactions.transaction_date <=', $filters['end_date']);
        }
        if (!empty($filters['search'])) {
            $this->transactionModel->like('transactions.description', $filters['search']);
        }

        $transactions = $this->transactionModel
            ->orderBy('transactions.transaction_date', 'DESC')
            ->orderBy('transactions.id', 'DESC')
            ->findAll();

        foreach ($transactions as &$tx) {
            $tx['amount'] = floatval($tx['amount']);
        }

        return $transactions;
    }

    /**
     * Export transactions to CSV format
     */
    public function exportToCSV(int $userId, array $filters): string
    {
        $transactions = $this->getFilteredTransactions($userId, $filters);

        $output = fopen('php://temp', 'r+');
        
        // Write UTF-8 BOM for Excel compatibility
        fwrite($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, ['No', 'Tanggal', 'Tipe', 'Kategori', 'Jumlah (Rp)', 'Deskripsi']);

        $no = 1;
        foreach ($transactions as $tx) {
            fputcsv($output, [
                $no++,
                date('d-m-Y', strtotime($tx['transaction_date'])),
                $tx['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran',
                $tx['category_name'],
                $tx['amount'],
                $tx['description'] ?: '-'
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Export transactions to Excel (XLSX) format
     */
    public function exportToExcel(int $userId, array $filters): string
    {
        $transactions = $this->getFilteredTransactions($userId, $filters);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        // Style header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'], // Beautiful Indigo color
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];

        // Set Title Blocks
        $sheet->setCellValue('A1', 'LAPORAN CATATAN KEUANGAN PRIBADI');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4F46E5'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Periode Laporan: ' . ($filters['start_date'] ? date('d M Y', strtotime($filters['start_date'])) : 'Awal') . ' s/d ' . ($filters['end_date'] ? date('d M Y', strtotime($filters['end_date'])) : 'Akhir'));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header Columns
        $headers = ['No', 'Tanggal', 'Tipe', 'Kategori', 'Jumlah', 'Deskripsi'];
        $column = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($column . '4', $h);
            $sheet->getColumnDimension($column)->setAutoSize(true);
            $column++;
        }
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension('4')->setRowHeight(28);

        // Write rows
        $rowNum = 5;
        $no = 1;
        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $tx) {
            $sheet->setCellValue('A' . $rowNum, $no++);
            $sheet->setCellValue('B' . $rowNum, date('d-m-Y', strtotime($tx['transaction_date'])));
            $sheet->setCellValue('C' . $rowNum, $tx['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran');
            $sheet->setCellValue('D' . $rowNum, $tx['category_name']);
            $sheet->setCellValue('E' . $rowNum, $tx['amount']);
            $sheet->setCellValue('F' . $rowNum, $tx['description'] ?: '-');

            // Format Jumlah column as currency
            $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');

            // Alignments & Styles
            $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            
            // Color code Tipe
            if ($tx['type'] === 'income') {
                $totalIncome += $tx['amount'];
                $sheet->getStyle('C' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('10B981')); // Green
            } else {
                $totalExpense += $tx['amount'];
                $sheet->getStyle('C' . $rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('EF4444')); // Red
            }

            $rowNum++;
        }

        // Summary Rows
        $sheet->setCellValue('A' . $rowNum, 'TOTAL PEMASUKAN');
        $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
        $sheet->setCellValue('E' . $rowNum, $totalIncome);
        $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('10B981'));
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $rowNum++;

        $sheet->setCellValue('A' . $rowNum, 'TOTAL PENGELUARAN');
        $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
        $sheet->setCellValue('E' . $rowNum, $totalExpense);
        $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('EF4444'));
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $rowNum++;

        $sheet->setCellValue('A' . $rowNum, 'SALDO BERSIH');
        $sheet->mergeCells('A' . $rowNum . ':D' . $rowNum);
        $sheet->setCellValue('E' . $rowNum, $totalIncome - $totalExpense);
        $sheet->getStyle('E' . $rowNum)->getNumberFormat()->setFormatCode('"Rp"#,##0');
        $sheet->getStyle('A' . $rowNum . ':E' . $rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $netBalance = $totalIncome - $totalExpense;
        $sheet->getStyle('E' . $rowNum)->getFont()->setColor(
            $netBalance >= 0 
                ? new \PhpOffice\PhpSpreadsheet\Style\Color('10B981') 
                : new \PhpOffice\PhpSpreadsheet\Style\Color('EF4444')
        );

        // Add borders to the entire table
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
        $sheet->getStyle('A4:F' . $rowNum)->applyFromArray($styleArray);

        // Render to Xlsx binary string
        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        return $excelData;
    }

    /**
     * Export transactions to PDF format
     * 
     * @param int $userId
     * @param array $filters
     * @param object $user
     * @param string|null $trendChart Base64 image data of trend chart
     * @param string|null $categoryChart Base64 image data of category chart
     * @return string
     */
    public function exportToPDF(int $userId, array $filters, $user, ?string $trendChart = null, ?string $categoryChart = null): string
    {
        $transactions = $this->getFilteredTransactions($userId, $filters);

        $totalIncome = 0;
        $totalExpense = 0;

        foreach ($transactions as $tx) {
            if ($tx['type'] === 'income') {
                $totalIncome += $tx['amount'];
            } else {
                $totalExpense += $tx['amount'];
            }
        }

        $netBalance = $totalIncome - $totalExpense;
        $periode = ($filters['start_date'] ? date('d M Y', strtotime($filters['start_date'])) : 'Awal') . ' s/d ' . ($filters['end_date'] ? date('d M Y', strtotime($filters['end_date'])) : 'Akhir');

        // Elegant CSS/HTML report template
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Laporan Keuangan Pribadi</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    color: #334155;
                    font-size: 11px;
                    line-height: 1.5;
                    margin: 0;
                    padding: 0;
                }
                .header-container {
                    border-bottom: 2px solid #4F46E5;
                    padding-bottom: 12px;
                    margin-bottom: 20px;
                }
                .company-name {
                    font-size: 20px;
                    font-weight: bold;
                    color: #4F46E5;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .report-title {
                    font-size: 13px;
                    font-weight: bold;
                    color: #475569;
                    margin-top: 5px;
                }
                .meta-table {
                    width: 100%;
                    margin-top: 10px;
                }
                .meta-table td {
                    padding: 2px 0;
                    vertical-align: top;
                }
                .meta-label {
                    color: #64748B;
                    font-weight: bold;
                    width: 90px;
                }
                .summary-box-container {
                    margin-bottom: 20px;
                    width: 100%;
                }
                .summary-box-container td {
                    width: 33.33%;
                    padding: 0 5px;
                }
                .summary-box-container td:first-child {
                    padding-left: 0;
                }
                .summary-box-container td:last-child {
                    padding-right: 0;
                }
                .summary-card {
                    border: 1px solid #E2E8F0;
                    border-radius: 8px;
                    padding: 10px 12px;
                    text-align: left;
                    background-color: #F8FAFC;
                }
                .card-title {
                    font-size: 9px;
                    color: #64748B;
                    font-weight: bold;
                    text-transform: uppercase;
                    margin-bottom: 4px;
                }
                .card-value {
                    font-size: 14px;
                    font-weight: bold;
                }
                .income-color { color: #10B981; }
                .expense-color { color: #EF4444; }
                .net-color-pos { color: #4F46E5; }
                .net-color-neg { color: #EF4444; }
                
                .tx-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 10px;
                }
                .tx-table th {
                    background-color: #4F46E5;
                    color: #ffffff;
                    font-weight: bold;
                    text-align: left;
                    padding: 8px 10px;
                    font-size: 10px;
                    border: 1px solid #4F46E5;
                }
                .tx-table td {
                    padding: 7px 10px;
                    border: 1px solid #E2E8F0;
                    vertical-align: middle;
                }
                .tx-table tr:nth-child(even) {
                    background-color: #F8FAFC;
                }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                .font-bold { font-weight: bold; }
                
                .footer {
                    position: fixed;
                    bottom: 0;
                    left: 0;
                    right: 0;
                    text-align: center;
                    color: #94A3B8;
                    font-size: 8px;
                    border-top: 1px solid #E2E8F0;
                    padding-top: 5px;
                }
            </style>
        </head>
        <body>
            <div class="header-container">
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <div class="company-name">CATATAN KEUANGAN PRIBADI</div>
                            <div class="report-title">Laporan Riwayat Arus Kas & Transaksi</div>
                        </td>
                        <td style="text-align: right; vertical-align: bottom; color: #64748B; font-size: 9px;">
                            Dicetak pada: ' . date('d F Y H:i') . '
                        </td>
                    </tr>
                </table>
                
                <table class="meta-table">
                    <tr>
                        <td class="meta-label">Nama Pengguna:</td>
                        <td>' . (string) esc((string) $user->username) . ' (' . (string) esc((string) $user->email) . ')</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Periode Laporan:</td>
                        <td>' . $periode . '</td>
                    </tr>
                </table>
            </div>';

        // Sisipkan Grafik jika ada (Trend dan Category)
        if ($trendChart || $categoryChart) {
            $html .= '<div style="margin-bottom: 20px;">
                        <table style="width: 100%;" cellspacing="0" cellpadding="0">
                            <tr>';
            
            if ($trendChart) {
                // Bersihkan data uri prefix jika berlebih (opsional, tapi Dompdf bisa memprosesnya langsung jika valid format base64)
                $html .= '<td style="width: ' . ($categoryChart ? '60%' : '100%') . '; padding-right: 10px; vertical-align: top;">
                            <div class="summary-card">
                                <div class="card-title" style="margin-bottom: 8px;">Tren Keuangan Bulanan</div>
                                <img src="' . (string) esc($trendChart) . '" style="width: 100%; border-radius: 4px;" alt="Trend Chart">
                            </div>
                          </td>';
            }
            
            if ($categoryChart) {
                $html .= '<td style="width: ' . ($trendChart ? '40%' : '100%') . '; padding-left: ' . ($trendChart ? '10px' : '0') . '; vertical-align: top;">
                            <div class="summary-card">
                                <div class="card-title" style="margin-bottom: 8px;">Alokasi Kategori Transaksi</div>
                                <img src="' . (string) esc($categoryChart) . '" style="width: 100%; border-radius: 4px;" alt="Category Chart">
                            </div>
                          </td>';
            }
            
            $html .= '      </tr>
                        </table>
                      </div>';
        }

        $html .= '
            <!-- Summary Cards -->
            <table class="summary-box-container" cellspacing="0" cellpadding="0">
                <tr>
                    <td>
                        <div class="summary-card">
                            <div class="card-title">Total Pemasukan</div>
                            <div class="card-value income-color">Rp ' . number_format($totalIncome, 0, ',', '.') . '</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="card-title">Total Pengeluaran</div>
                            <div class="card-value expense-color">Rp ' . number_format($totalExpense, 0, ',', '.') . '</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="card-title">Saldo Bersih</div>
                            <div class="card-value ' . ($netBalance >= 0 ? 'net-color-pos' : 'net-color-neg') . '">
                                Rp ' . number_format($netBalance, 0, ',', '.') . '
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Transactions Table -->
            <div class="font-bold" style="font-size: 11px; margin-bottom: 6px; color: #475569;">Detail Transaksi Keuangan</div>
            <table class="tx-table">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">No</th>
                        <th style="width: 12%;" class="text-center">Tanggal</th>
                        <th style="width: 13%;" class="text-center">Tipe</th>
                        <th style="width: 18%;">Kategori</th>
                        <th style="width: 37%;">Deskripsi</th>
                        <th style="width: 15%;" class="text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        if (empty($transactions)) {
            $html .= '<tr><td colspan="6" class="text-center" style="padding: 20px; color: #94A3B8;">Tidak ada data transaksi ditemukan untuk periode ini.</td></tr>';
        } else {
            foreach ($transactions as $tx) {
                $html .= '
                <tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td class="text-center">' . date('d-m-Y', strtotime($tx['transaction_date'])) . '</td>
                    <td class="text-center font-bold ' . ($tx['type'] === 'income' ? 'income-color' : 'expense-color') . '">
                        ' . ($tx['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran') . '
                    </td>
                    <td class="font-bold">' . (string) esc((string) $tx['category_name']) . '</td>
                    <td>' . (string) esc((string) ($tx['description'] ?: '-')) . '</td>
                    <td class="text-right font-bold ' . ($tx['type'] === 'income' ? 'income-color' : 'expense-color') . '">
                        ' . ($tx['type'] === 'income' ? '+' : '-') . ' Rp ' . number_format($tx['amount'], 0, ',', '.') . '
                    </td>
                </tr>';
            }
        }

        $html .= '
                </tbody>
            </table>

            <div class="footer">
                Dokumen ini dibuat secara otomatis oleh Aplikasi Catatan Keuangan Pribadi. halaman 1 dari 1
            </div>
        </body>
        </html>';

        // Setup dompdf options
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', false);
        $options->set('isRemoteEnabled', false);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
