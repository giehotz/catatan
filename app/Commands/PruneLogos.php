<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\KopSettingModel;

class PruneLogos extends BaseCommand
{
    protected $group       = 'Cooperative';
    protected $name        = 'cooperative:prune-logos';
    protected $description = 'Clean up orphan cooperative logo files from the local storage that are no longer referenced.';
    
    // Command line options
    protected $options = [
        '-d, --dry-run' => 'Simulate the pruning process without actually deleting any files.',
    ];

    public function run(array $params)
    {
        $dryRun = CLI::getOption('dry-run') !== null || CLI::getOption('d') !== null;
        
        if ($dryRun) {
            CLI::write('=== [MODE SIMULASI] MENJALANKAN PRUNE LOGOS DRY-RUN ===', 'yellow');
        } else {
            CLI::write('=== MEMULAI PEMBERSIHAN LOGO YATIM (ORPHAN LOGOS) ===', 'blue');
        }

        $logoDirectory = FCPATH . 'uploads/logos/';
        if (!is_dir($logoDirectory)) {
            CLI::write('Direktori logo tidak ditemukan atau kosong.', 'yellow');
            return;
        }

        // 1. Gather all files in the logo directory
        $files = array_diff(scandir($logoDirectory), ['.', '..', '.gitkeep']);
        if (empty($files)) {
            CLI::write('Tidak ada file logo ditemukan untuk diperiksa.', 'yellow');
            return;
        }

        // 2. Gather referenced logo paths
        $referencedPaths = [];

        // Active logo
        $activeLogo = KopSettingModel::getSetting('kop_logo_path');
        if (!empty($activeLogo)) {
            $referencedPaths[basename($activeLogo)] = true;
        }

        // Snapshotted logos
        $db = \Config\Database::connect();
        if ($db->tableExists('kop_document_snapshots')) {
            $snapshots = $db->table('kop_document_snapshots')->select('kop_snapshot')->get()->getResultArray();
            foreach ($snapshots as $snap) {
                $kopData = json_decode($snap['kop_snapshot'], true);
                if (is_array($kopData) && !empty($kopData['logo_path'])) {
                    $referencedPaths[basename($kopData['logo_path'])] = true;
                }
            }
        }

        // 3. Filter orphan files
        $orphans = [];
        $totalBytes = 0;

        foreach ($files as $file) {
            if (!isset($referencedPaths[$file])) {
                $filePath = $logoDirectory . $file;
                $size = filesize($filePath);
                $totalBytes += $size;
                
                $orphans[] = [
                    'name' => $file,
                    'path' => $filePath,
                    'size' => $this->formatBytes($size)
                ];
            }
        }

        if (empty($orphans)) {
            CLI::write('Hebat! Tidak ada file logo yatim (orphan) ditemukan. Storage optimal.', 'green');
            return;
        }

        // 4. Output Tabular Grid of Orphans
        CLI::write('Menemukan ' . count($orphans) . ' berkas logo yatim (total ukuran: ' . $this->formatBytes($totalBytes) . '):', 'yellow');
        
        $tableHeaders = ['Nama Berkas', 'Path Lokasi', 'Ukuran'];
        $tableRows = [];
        foreach ($orphans as $o) {
            $tableRows[] = [$o['name'], $o['path'], $o['size']];
        }
        
        CLI::table($tableRows, $tableHeaders);

        // 5. Execution (Delete if not dry-run)
        if ($dryRun) {
            CLI::write("\n[SIMULASI] Mode dry-run aktif. " . count($orphans) . " file di atas LEWAT dari proses penghapusan nyata.", 'yellow');
        } else {
            $deletedCount = 0;
            foreach ($orphans as $o) {
                if (is_file($o['path'])) {
                    if (unlink($o['path'])) {
                        CLI::write("-> [SUKSES] Menghapus logo yatim: " . $o['name'], 'green');
                        $deletedCount++;
                    } else {
                        CLI::write("-> [GAGAL] Gagal menghapus file disk: " . $o['name'], 'red');
                    }
                }
            }
            CLI::write("\nPembersihan selesai! Berhasil menghapus {$deletedCount} berkas logo yatim dari disk lokal.", 'green');
        }
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
