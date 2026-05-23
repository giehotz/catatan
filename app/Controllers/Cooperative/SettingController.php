<?php

namespace App\Controllers\Cooperative;

use App\Controllers\BaseController;
use App\Models\KopSettingModel;
use App\Models\AuditLogModel;
use App\Helpers\SuratHelper;

class SettingController extends BaseController
{
    /**
     * Cooperative Settings page (Admin Only).
     */
    public function settings()
    {
        $user = auth()->user();
        if (!$user->inGroup('admin') && !$user->inGroup('superadmin')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Halaman tidak ditemukan.');
        }

        $settingsKeys = [
            'direct_loan_enabled',
            'kop_bunga_pinjaman_persen',
            'kop_bunga_pinjaman_jenis',
            'kop_bunga_pinjaman_periode',
            'kop_bunga_pinjaman_opsi_bayar',
            'kop_jasa_pinjaman_nominal',
            'kop_jasa_pinjaman_jenis',
            'kop_jasa_pinjaman_cara_bayar',
            'kop_simpanan_wajib_nominal',
            'kop_simpanan_wajib_batas_hari',
            'kop_dana_sosial_nominal',
            'kop_dana_sosial_batas_hari',
            'kop_rekening_bank_1_nama',
            'kop_rekening_bank_1_nomor',
            'kop_rekening_bank_1_atas_nama',
            'kop_rekening_bank_2_nama',
            'kop_rekening_bank_2_nomor',
            'kop_rekening_bank_2_atas_nama',

            // New Dynamic KOP settings
            'kop_nama_koperasi',
            'kop_badan_hukum',
            'kop_wilayah_kerja',
            'kop_alamat',
            'kop_telepon',
            'kop_email',
            'kop_website',
            'kop_logo_path',
            'kop_format_nomor_surat',
            'kop_letter_signers',
            'kop_unit_code'
        ];

        $settings = [];
        foreach ($settingsKeys as $key) {
            $settings[$key] = KopSettingModel::getSetting($key);
        }

        // Set default value for signers JSON if missing
        if (empty($settings['kop_letter_signers'])) {
            $settings['kop_letter_signers'] = '[]';
        }

        $userModel = new \App\Models\UserModel();
        $activeUsers = $userModel->where('active', 1)->orderBy('username', 'ASC')->findAll();

        return view('admin/cooperative/settings', [
            'title'             => 'Panel Koperasi - Pengaturan Modul',
            'settings'          => $settings,
            'directLoanEnabled' => ($settings['direct_loan_enabled'] ?? '0') === '1',
            'activeUsers'       => $activeUsers,
        ]);
    }

    /**
     * Save cooperative settings (Admin Only).
     */
    public function updateSettings()
    {
        $user = auth()->user();
        if (!$user->inGroup('admin') && !$user->inGroup('superadmin')) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Akses ditolak.');
        }

        $settingsKeys = [
            'kop_bunga_pinjaman_persen',
            'kop_bunga_pinjaman_jenis',
            'kop_bunga_pinjaman_periode',
            'kop_bunga_pinjaman_opsi_bayar',
            'kop_jasa_pinjaman_nominal',
            'kop_jasa_pinjaman_jenis',
            'kop_jasa_pinjaman_cara_bayar',
            'kop_simpanan_wajib_nominal',
            'kop_simpanan_wajib_batas_hari',
            'kop_dana_sosial_nominal',
            'kop_dana_sosial_batas_hari',
            'kop_rekening_bank_1_nama',
            'kop_rekening_bank_1_nomor',
            'kop_rekening_bank_1_atas_nama',
            'kop_rekening_bank_2_nama',
            'kop_rekening_bank_2_nomor',
            'kop_rekening_bank_2_atas_nama',

            // New KOP fields
            'kop_nama_koperasi',
            'kop_badan_hukum',
            'kop_wilayah_kerja',
            'kop_alamat',
            'kop_telepon',
            'kop_email',
            'kop_website',
            'kop_unit_code',
        ];

        // 1. Whitelist & Format String validation for nomor surat
        $format = $this->request->getPost('kop_format_nomor_surat');
        if ($format !== null) {
            $format = trim($format);
            
            // Check braces balance
            if (substr_count($format, '{') !== substr_count($format, '}')) {
                return redirect()->back()->withInput()->with('error', 'Format Nomor Surat tidak valid: Tanda kurung kurawal tidak seimbang (Jumlah "{" dan "}" harus sama).');
            }

            // Must contain {nomor_urut}
            if (!str_contains($format, '{nomor_urut}')) {
                return redirect()->back()->withInput()->with('error', 'Format Nomor Surat tidak valid: Wajib mengandung placeholder "{nomor_urut}" untuk pengurutan otomatis.');
            }

            // Whitelist placeholder validation
            preg_match_all('/\{([a-zA-Z0-9_-]+)\}/', $format, $matches);
            $whitelist = ['nomor_urut', 'kode', 'year', 'month', 'month_roman', 'unit_code'];
            
            if (isset($matches[1])) {
                foreach ($matches[1] as $placeholder) {
                    if (!in_array($placeholder, $whitelist, true)) {
                        return redirect()->back()->withInput()->with('error', "Format Nomor Surat tidak valid: Placeholder '{{$placeholder}}' tidak dikenali. Gunakan hanya yang terdaftar di panduan.");
                    }
                }
            }

            KopSettingModel::setSetting('kop_format_nomor_surat', $format);
        }

        // 2. Validate and process signers JSON structured schema
        $signersJson = $this->request->getPost('kop_letter_signers');
        if ($signersJson !== null) {
            $signers = json_decode($signersJson, true);
            if (!is_array($signers)) {
                return redirect()->back()->withInput()->with('error', 'Pengaturan penanda tangan tidak valid: Format JSON tidak terstruktur.');
            }

            foreach ($signers as $signer) {
                if (empty($signer['signer_id']) || empty($signer['name']) || empty($signer['role']) || empty($signer['letter_type'])) {
                    return redirect()->back()->withInput()->with('error', 'Pengaturan penanda tangan tidak valid: Setiap profil dewan pengurus wajib memiliki ID, Nama, Jabatan, dan Tipe Surat.');
                }
                if (!isset($signer['priority']) || !is_numeric($signer['priority'])) {
                    return redirect()->back()->withInput()->with('error', 'Pengaturan penanda tangan tidak valid: Setiap profil dewan pengurus wajib memiliki nilai prioritas numerik.');
                }
            }
            KopSettingModel::setSetting('kop_letter_signers', json_encode($signers));
        }

        // 3. Process Dynamic Logo Upload without deleting historical versions
        $logoFile = $this->request->getFile('kop_logo');
        if ($logoFile && $logoFile->isValid() && !$logoFile->hasMoved()) {
            $logoRules = [
                'kop_logo' => 'uploaded[kop_logo]|max_size[kop_logo,2048]|is_image[kop_logo]|mime_in[kop_logo,image/png,image/jpeg,image/jpg]'
            ];
            
            if (!$this->validate($logoRules)) {
                return redirect()->back()->withInput()->with('error', 'Gagal mengunggah logo: ' . implode(' ', $this->validator->getErrors()));
            }

            $newName = $logoFile->getRandomName();
            // Move file securely to uploads
            if ($logoFile->move(FCPATH . 'uploads/logos/', $newName)) {
                KopSettingModel::setSetting('kop_logo_path', 'uploads/logos/' . $newName);
            }
        }

        // Update Direct Loan toggle
        $enabled = $this->request->getPost('direct_loan_enabled') ? '1' : '0';
        KopSettingModel::setSetting('direct_loan_enabled', $enabled);

        // Update standard keys
        foreach ($settingsKeys as $key) {
            $value = $this->request->getPost($key);
            if ($value !== null) {
                KopSettingModel::setSetting($key, $value);
            }
        }

        AuditLogModel::log('coop_setting_changed', "Mengubah konfigurasi pengaturan modul dan template surat resmi koperasi.");

        return redirect()->back()->with('message', 'Pengaturan koperasi berhasil diperbarui.');
    }

    /**
     * AJAX Live preview endpoint protected with throttler rate limiting (15 requests/minute).
     */
    public function previewNumber()
    {
        // 1. Throttler rate limiting check (max 15 requests per minute per IP)
        $throttler = service('throttler');
        $ipHash = md5($this->request->getIPAddress());
        
        if ($throttler->check($ipHash, 15, MINUTE) === false) {
            return $this->response->setStatusCode(429)->setJSON([
                'success' => false,
                'error'   => 'Terlalu banyak permintaan (Rate limit exceeded). Harap tunggu beberapa detik.'
            ]);
        }

        $format = $this->request->getPost('format');
        $kode = $this->request->getPost('kode') ?? 'RE';
        
        if (empty($format)) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Format tidak boleh kosong.'
            ]);
        }

        // 2. Validate curly braces balance
        if (substr_count($format, '{') !== substr_count($format, '}')) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Tanda kurung kurawal tidak seimbang (Jumlah "{" dan "}" harus sama).'
            ]);
        }

        // 3. Must contain {nomor_urut}
        if (!str_contains($format, '{nomor_urut}')) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Wajib mengandung placeholder "{nomor_urut}" untuk pengurutan otomatis.'
            ]);
        }

        // 4. Validate placeholders whitelist
        preg_match_all('/\{([a-zA-Z0-9_-]+)\}/', $format, $matches);
        $whitelist = ['nomor_urut', 'kode', 'year', 'month', 'month_roman', 'unit_code'];
        
        if (isset($matches[1])) {
            foreach ($matches[1] as $placeholder) {
                if (!in_array($placeholder, $whitelist, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'error'   => "Placeholder '{{$placeholder}}' tidak dikenal."
                    ]);
                }
            }
        }

        // Generate test preview using SuratHelper
        try {
            $preview = SuratHelper::generateNomorSurat(1, $kode, $format);
            return $this->response->setJSON([
                'success' => true,
                'preview' => $preview
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'error'   => 'Gagal mem-parsing format surat: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Administrative cache-clearing endpoint with parameter validation and 400 Bad Request handling.
     */
    public function clearCache()
    {
        $user = auth()->user();
        if (!$user->inGroup('admin') && !$user->inGroup('superadmin')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'error'   => 'Akses ditolak.'
            ]);
        }

        $type = $this->request->getGet('type');
        $validTypes = ['all', 'config', 'snapshot'];

        if (!in_array($type, $validTypes, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error'   => "Parameter 'type' tidak valid. Gunakan opsi: 'all', 'config', atau 'snapshot'."
            ]);
        }

        try {
            if ($type === 'all' || $type === 'config') {
                // Invalidate all setting keys
                $settingsKeys = [
                    'direct_loan_enabled', 'kop_bunga_pinjaman_persen', 'kop_bunga_pinjaman_jenis',
                    'kop_bunga_pinjaman_periode', 'kop_bunga_pinjaman_opsi_bayar', 'kop_jasa_pinjaman_nominal',
                    'kop_jasa_pinjaman_jenis', 'kop_jasa_pinjaman_cara_bayar', 'kop_simpanan_wajib_nominal',
                    'kop_simpanan_wajib_batas_hari', 'kop_dana_sosial_nominal', 'kop_dana_sosial_batas_hari',
                    'kop_rekening_bank_1_nama', 'kop_rekening_bank_1_nomor', 'kop_rekening_bank_1_atas_nama',
                    'kop_rekening_bank_2_nama', 'kop_rekening_bank_2_nomor', 'kop_rekening_bank_2_atas_nama',
                    'kop_nama_koperasi', 'kop_badan_hukum', 'kop_wilayah_kerja', 'kop_alamat', 'kop_telepon',
                    'kop_email', 'kop_website', 'kop_logo_path', 'kop_format_nomor_surat', 'kop_letter_signers',
                    'kop_unit_code'
                ];
                
                foreach ($settingsKeys as $key) {
                    cache()->delete("kop_setting_{$key}");
                }
                cache()->delete("kop_settings_cached_all");
                cache()->delete("kop_settings_all");
            }

            if ($type === 'all' || $type === 'snapshot') {
                // To safely clear all snapshot caches in CI4 without wildcards (since file cache doesn't support wildcards well),
                // we can clear by active document types (e.g. resign) if we loop through their primary keys,
                // or flush the entire cache driver if it's purely for system refresh.
                // We will delete all resignation snapshots by reading approved records
                $db = \Config\Database::connect();
                if ($db->tableExists('kop_pengunduran_diri')) {
                    $approved = $db->table('kop_pengunduran_diri')->where('status', 'approved')->get()->getResultArray();
                    foreach ($approved as $row) {
                        cache()->delete("kop_snapshot_resign_{$row['id']}");
                    }
                }
            }

            AuditLogModel::log('coop_cache_flushed', "Membersihkan cache sistem koperasi (Tipe: {$type}).");

            return $this->response->setJSON([
                'success' => true,
                'message' => "Cache sistem koperasi tipe '{$type}' berhasil dibersihkan sepenuhnya."
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'error'   => 'Gagal membersihkan cache: ' . $e->getMessage()
            ]);
        }
    }
}
