<?php

namespace App\Models;

use CodeIgniter\Model;

class KopPinjamanModel extends Model
{
    protected $table            = 'kop_pinjaman';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'anggota_id',
        'nominal_pinjaman',
        'tenor_bulan',
        'bunga_persen',
        'nominal_total',
        'jasa_nominal',
        'metode_bayar_jasa',
        'jenis_bunga',
        'bunga_opsi_bayar',
        'status',
        'debt_id_fk',
        'receivable_id_fk',
        'keterangan',
        'approved_by',
        'approved_at'
    ];

    protected $useTimestamps = false;

    // ── Cache Invalidation Hooks ──────────────────────────────────────────────
    protected $afterUpdate = ['invalidateLoanCache'];
    protected $afterDelete = ['invalidateLoanCache'];

    /**
     * Automatically clear the cached amortization schedule whenever a loan
     * record is updated or deleted. Triggered at the Model layer to cover
     * direct model calls, bulk operations, and admin manual edits.
     */
    protected function invalidateLoanCache(array $data): array
    {
        $id = $data['id'][0] ?? ($data['id'] ?? null);
        if ($id) {
            cache()->delete("loan_schedule_{$id}");
        }
        return $data;
    }

    /**
     * Calculate comprehensive loan details based on current settings.
     *
     * @param float $nominal
     * @param int $tenor
     * @return array
     */
    public static function calculateLoanDetails(float $nominal, int $tenor): array
    {
        // Load settings with fallback defaults matching CI4 standard cooperative specs
        $bungaPersen = floatval(\App\Models\KopSettingModel::getSetting('kop_bunga_pinjaman_persen', '1.50'));
        $jenisBunga = \App\Models\KopSettingModel::getSetting('kop_bunga_pinjaman_jenis', 'flat'); // flat, efektif
        $bungaPeriode = \App\Models\KopSettingModel::getSetting('kop_bunga_pinjaman_periode', 'bulanan'); // bulanan, tahunan
        $bungaOpsiBayar = \App\Models\KopSettingModel::getSetting('kop_bunga_pinjaman_opsi_bayar', 'cicil'); // cicil, di_awal

        $jasaNominalSetting = floatval(\App\Models\KopSettingModel::getSetting('kop_jasa_pinjaman_nominal', '0'));
        $jasaJenis = \App\Models\KopSettingModel::getSetting('kop_jasa_pinjaman_jenis', 'nominal_tetap'); // persentase, nominal_tetap
        $jasaCaraBayar = \App\Models\KopSettingModel::getSetting('kop_jasa_pinjaman_cara_bayar', 'cicil'); // cicil, di_awal

        // 1. Calculate Jasa
        if ($jasaJenis === 'persentase') {
            $jasaTotal = $nominal * ($jasaNominalSetting / 100);
        } else {
            $jasaTotal = $jasaNominalSetting;
        }

        // 2. Adjust interest rate based on period (convert annual to monthly rate if needed)
        $monthlyRate = ($bungaPeriode === 'tahunan') ? ($bungaPersen / 12) : $bungaPersen;

        // 3. Calculate Bunga
        $bungaTotal = 0;
        if ($jenisBunga === 'flat') {
            $bungaTotal = $nominal * ($monthlyRate / 100) * $tenor;
        } else { // efektif (declining balance)
            $monthlyPrincipal = $nominal / $tenor;
            for ($i = 0; $i < $tenor; $i++) {
                $remaining = $nominal - ($i * $monthlyPrincipal);
                $bungaTotal += $remaining * ($monthlyRate / 100);
            }
        }

        // 4. Determine upfront vs monthly payments
        $jasaDiAwal = ($jasaCaraBayar === 'di_awal') ? $jasaTotal : 0.00;
        $jasaCicilan = ($jasaCaraBayar === 'cicil') ? $jasaTotal : 0.00;

        $bungaDiAwal = ($bungaOpsiBayar === 'di_awal') ? $bungaTotal : 0.00;
        $bungaCicilan = ($bungaOpsiBayar === 'cicil') ? $bungaTotal : 0.00;

        // Payout to member is the principal minus any upfront interest and service fees
        $payoutAmount = $nominal - $bungaDiAwal - $jasaDiAwal;

        // Total repayment that the user has to repay over time
        $nominalTotal = $nominal + $bungaCicilan + $jasaCicilan;

        // Monthly details
        $pokokPerBulan = $nominal / $tenor;
        $bungaPerBulan = $bungaCicilan / $tenor;
        $jasaPerBulan = $jasaCicilan / $tenor;
        $totalAngsuranPerBulan = $pokokPerBulan + $bungaPerBulan + $jasaPerBulan;

        return [
            'bunga_persen'        => $bungaPersen,
            'jenis_bunga'         => $jenisBunga,
            'bunga_periode'       => $bungaPeriode,
            'bunga_opsi_bayar'    => $bungaOpsiBayar,
            'bunga_total'         => $bungaTotal,
            
            'jasa_nominal'        => $jasaTotal,
            'jasa_jenis'          => $jasaJenis,
            'jasa_cara_bayar'     => $jasaCaraBayar,
            
            'payout_amount'       => $payoutAmount,
            'nominal_total'       => $nominalTotal, // total to repay
            
            'pokok_per_bulan'     => $pokokPerBulan,
            'bunga_per_bulan'     => $bungaPerBulan,
            'jasa_per_bulan'      => $jasaPerBulan,
            'angsuran_per_bulan'  => $totalAngsuranPerBulan,
            'bunga_di_awal'       => $bungaDiAwal,
            'jasa_di_awal'        => $jasaDiAwal,
        ];
    }
}
