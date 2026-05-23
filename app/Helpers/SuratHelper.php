<?php

namespace App\Helpers;

use App\Models\KopSettingModel;
use App\Models\KopDocumentSnapshotModel;
use App\Adapters\SnapshotAdapter;
use Config\LetterFallback;

class SuratHelper
{
    /**
     * Retrieve structured KOP data from polymorphic snapshot or active database config with fallback.
     *
     * @param string $docType Document polymorphic type (e.g., 'resign', 'loan').
     * @param int $docId Document ID.
     * @return array Structured KOP configuration.
     */
    public static function getKopData(string $docType, int $docId): array
    {
        $cacheKey = "kop_snapshot_{$docType}_{$docId}";
        
        // 1. Try to read from static cache
        $cached = cache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // 2. Query polymorphic snapshots table
        $snapshotModel = new KopDocumentSnapshotModel();
        $snapshot = $snapshotModel->where('document_type', $docType)
                                  ->where('document_id', $docId)
                                  ->first();

        if ($snapshot) {
            $kopData = json_decode($snapshot['kop_snapshot'], true);
            if (is_array($kopData)) {
                // Apply dynamic runtime lazy upgrades for backwards compatibility
                $kopData = SnapshotAdapter::upgrade($kopData, 'kop', 1);
                
                // Cache the static snapshot forever (since it is immutable)
                cache()->save($cacheKey, $kopData, 0); // 0 = infinite cache duration in CI4
                return $kopData;
            }
        }

        // 3. Fallback Tier 1: Read active settings from database
        $kopData = [
            'schema_version'   => 1,
            'cooperative_name' => KopSettingModel::getSetting('kop_nama_koperasi'),
            'legal_id'         => KopSettingModel::getSetting('kop_badan_hukum'),
            'work_region'      => KopSettingModel::getSetting('kop_wilayah_kerja'),
            'address'          => KopSettingModel::getSetting('kop_alamat'),
            'phone'            => KopSettingModel::getSetting('kop_telepon'),
            'email'            => KopSettingModel::getSetting('kop_email'),
            'website'          => KopSettingModel::getSetting('kop_website'),
            'logo_path'        => KopSettingModel::getSetting('kop_logo_path'),
        ];

        // 4. Fallback Tier 2: Check if any fields are empty and apply hardcoded baseline Config\LetterFallback
        $fallback = new LetterFallback();
        if (empty($kopData['cooperative_name'])) {
            $kopData['cooperative_name'] = $fallback->cooperativeName;
            $kopData['legal_id']         = $fallback->legalId;
            $kopData['work_region']      = $fallback->workRegion;
            $kopData['address']          = $fallback->address;
            $kopData['phone']            = $fallback->phone;
            $kopData['email']            = $fallback->email;
            $kopData['website']          = $fallback->website;
            $kopData['logo_path']        = $fallback->logoPath;
        }

        return $kopData;
    }

    /**
     * Retrieve matching signer details based on priority, letter type, and fallbacks.
     *
     * @param string $letterType Document category / context (e.g., 'resign', 'loan').
     * @param int|null $docId Optional Document ID for snapshot checking.
     * @return array Signer profile.
     */
    public static function getSigner(string $letterType, ?int $docId = null): array
    {
        // 1. Check snapshot if docId is provided
        if ($docId !== null) {
            $snapshotModel = new KopDocumentSnapshotModel();
            $snapshot = $snapshotModel->where('document_type', $letterType)
                                      ->where('document_id', $docId)
                                      ->first();
            if ($snapshot) {
                $signerData = json_decode($snapshot['signer_snapshot'], true);
                if (is_array($signerData)) {
                    return SnapshotAdapter::upgrade($signerData, 'signer', 1);
                }
            }
        }

        // 2. Fallback Tier 1: Look in the structured signers JSON array in kop_settings
        $signersJson = KopSettingModel::getSetting('kop_letter_signers');
        if (!empty($signersJson)) {
            $signers = json_decode($signersJson, true);
            if (is_array($signers)) {
                // Filter active signers for the specific letter type
                $filtered = array_filter($signers, function ($s) use ($letterType) {
                    return isset($s['letter_type']) && $s['letter_type'] === $letterType && !empty($s['is_active']);
                });

                if (!empty($filtered)) {
                    // Sort by priority DESC (higher numbers mean higher priority)
                    usort($filtered, function ($a, $b) {
                        return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
                    });
                    return SnapshotAdapter::upgrade(reset($filtered), 'signer', 1);
                }

                // Fallback to active signers marked as 'default'
                $defaultSigners = array_filter($signers, function ($s) {
                    return isset($s['letter_type']) && $s['letter_type'] === 'default' && !empty($s['is_active']);
                });

                if (!empty($defaultSigners)) {
                    usort($defaultSigners, function ($a, $b) {
                        return ($b['priority'] ?? 0) <=> ($a['priority'] ?? 0);
                    });
                    return SnapshotAdapter::upgrade(reset($defaultSigners), 'signer', 1);
                }
            }
        }

        // 3. Fallback Tier 2: Hardcoded system baseline values (Ketua Koperasi)
        return [
            'schema_version' => 1,
            'signer_id'      => 'signer_fallback_default',
            'name'           => KopSettingModel::getSetting('kop_penanda_tangan_nama', 'H. Budi Santoso, M.B.A.'),
            'role'           => KopSettingModel::getSetting('kop_penanda_tangan_jabatan', 'Ketua Dewan Pengurus'),
            'letter_type'    => $letterType
        ];
    }

    /**
     * Parse and generate formatted dynamic letter number string replacing whitelisted placeholders.
     *
     * @param int $nomorUrut Sequential counter.
     * @param string $kode Document code/identifier (e.g. 'RE', 'LN').
     * @param string|null $format Optional dynamic format string fallback.
     * @return string Parsed letter number.
     */
    public static function generateNomorSurat(int $nomorUrut, string $kode, ?string $format = null): string
    {
        $formatString = $format ?? KopSettingModel::getSetting('kop_format_nomor_surat', '{nomor_urut}/KOP-SKP/{kode}/{year}');
        
        $year = date('Y');
        $month = date('m');
        
        // Convert month to Roman Numerals (1 - 12)
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        $monthRoman = $romanMonths[(int)$month] ?? 'I';

        $unitCode = KopSettingModel::getSetting('kop_unit_code', 'PST');

        // Whitelisted replacement map
        $replacements = [
            '{nomor_urut}'  => str_pad((string)$nomorUrut, 3, '0', STR_PAD_LEFT),
            '{kode}'        => $kode,
            '{year}'        => $year,
            '{month}'       => $month,
            '{month_roman}' => $monthRoman,
            '{unit_code}'   => $unitCode,
        ];

        // Replace all curly braced strings
        $parsedString = $formatString;
        foreach ($replacements as $placeholder => $value) {
            $parsedString = str_replace($placeholder, $value, $parsedString);
        }

        // Sanitization: Strip out any unrecognized curly braced placeholders e.g. {unknown}
        $parsedString = preg_replace('/\{[a-zA-Z0-9_-]+\}/', '', $parsedString);

        return trim($parsedString);
    }
}
