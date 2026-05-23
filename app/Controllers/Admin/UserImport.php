<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\UserImportService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserImport extends BaseController
{
    /**
     * Import users via Excel.
     */
    public function importExcel()
    {
        $file = $this->request->getFile('excel_file');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'File tidak valid atau belum diunggah.');
        }

        // Validate extension
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
            return redirect()->back()->with('error', 'Format file tidak diizinkan. Gunakan .xls, .xlsx, atau .csv');
        }

        $importService = new UserImportService();
        $result = $importService->importFromExcel($file->getTempName());

        $message = "Berhasil mengimpor {$result['success_count']} pengguna.";
        
        if (!empty($result['errors'])) {
            // Save errors in flashdata for display
            session()->setFlashdata('import_errors', $result['errors']);
            $message .= " Namun, terdapat " . count($result['errors']) . " baris yang gagal.";
            return redirect()->back()->with('error', $message);
        }

        return redirect()->back()->with('message', $message);
    }

    /**
     * Download Excel Template for User Import
     */
    public function downloadImportTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set Headers
        $sheet->setCellValue('A1', 'Username');
        $sheet->setCellValue('B1', 'Email');
        $sheet->setCellValue('C1', 'Password');
        $sheet->setCellValue('D1', 'Role');

        // Provide an example row
        $sheet->setCellValue('A2', 'johndoe');
        $sheet->setCellValue('B2', 'john@example.com');
        $sheet->setCellValue('C2', 'SecretPass123!');
        $sheet->setCellValue('D2', 'user');

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Make header bold
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Template_Import_Pengguna.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return $this->response->download($tempFile, null)->setFileName($fileName);
    }
}
