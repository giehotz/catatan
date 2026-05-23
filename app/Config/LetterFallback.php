<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class LetterFallback extends BaseConfig
{
    public string $cooperativeName = 'Koperasi Simpan Pinjam Catatan Keuangan';
    public string $legalId         = 'Badan Hukum No. 00892/KSP/BH/2025';
    public string $workRegion      = 'Wilayah Kerja Nasional DKI Jakarta';
    public string $address         = 'Jl. Jend. Sudirman Kav. 21, Jakarta Selatan';
    public string $phone           = '(021) 8089-9800';
    public string $email           = 'ksp@catatankeuangan.com';
    public string $website         = 'www.catatankeuangan.com';
    public string $logoPath        = 'assets/images/logo-ksp-default.png';
}
