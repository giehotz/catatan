<?php

namespace App\Services;

/**
 * LoanAmortizationService
 *
 * Centralised amortization calculation service used by both the Member Bills
 * screen and the Admin Arrears Report. Extracting this logic here prevents
 * formula drift between the two consumers.
 */
class LoanAmortizationService
{
    /**
     * Build a complete per-installment amortization schedule for a single loan.
     *
     * Supports both flat-rate and effective/declining-balance interest schemes.
     * The schedule drives both the monthly bill view (BillsController) and the
     * arrears calculations (ArrearsReportController) so the numbers are always
     * identical between the two surfaces.
     *
     * @param array $loan  A kop_pinjaman row including: nominal_pinjaman,
     *                     tenor_bulan, bunga_persen, jenis_bunga,
     *                     bunga_opsi_bayar, jasa_nominal, metode_bayar_jasa,
     *                     approved_at.
     * @return array  Indexed from 1 to tenor_bulan.
     *                Each entry: [angsuran_ke, pokok, bunga, jasa, total, due_date]
     */
    public function buildSchedule(array $loan): array
    {
        $nominal     = floatval($loan['nominal_pinjaman']);
        $tenor       = intval($loan['tenor_bulan']);
        $bungaPersen = floatval($loan['bunga_persen'] ?? 0);
        $jenisBunga  = $loan['jenis_bunga']      ?? 'flat';
        $bungaOpsi   = $loan['bunga_opsi_bayar'] ?? 'cicil';
        $jasaTotal   = floatval($loan['jasa_nominal'] ?? 0);
        $jasaCaraBayar = $loan['metode_bayar_jasa'] ?? 'cicil';

        if ($tenor <= 0) {
            return [];
        }

        // --- Monthly interest rate ---
        // bunga_persen is stored as configured (monthly or annual).
        // If the setting kop_bunga_pinjaman_periode was 'tahunan' at approval
        // time, the stored value is the raw percentage per year; divide by 12.
        // However, since bunga_persen in the DB is always stored as the
        // monthly equivalent (KopPinjamanModel::calculateLoanDetails already
        // converts), we use it directly.
        $monthlyRate = $bungaPersen / 100; // e.g. 1.5% -> 0.015

        $pokokPerBulan = $nominal / $tenor;

        // --- Per-installment bunga (interest) ---
        // Flat: constant every month
        // Effective: declining balance — recalculate per period
        $bungaPerBulanFlatTotal = ($bungaOpsi === 'cicil')
            ? $nominal * $monthlyRate * $tenor / $tenor   // = $nominal * monthlyRate
            : 0.0;  // paid upfront → no monthly interest component

        // --- Jasa (service fee) per month ---
        $jasaPerBulan = ($jasaCaraBayar === 'cicil' && $tenor > 0)
            ? $jasaTotal / $tenor
            : 0.0;  // paid upfront → no monthly jasa component

        $schedule  = [];
        $sisaPokok = $nominal;

        for ($k = 1; $k <= $tenor; $k++) {
            if ($jenisBunga === 'efektif') {
                // Declining balance: interest on remaining principal
                $bungaBulanIni = $sisaPokok * $monthlyRate;
            } else {
                // Flat: constant interest regardless of remaining principal
                $bungaBulanIni = ($bungaOpsi === 'cicil') ? $nominal * $monthlyRate : 0.0;
            }

            $totalBulanIni = $pokokPerBulan + $bungaBulanIni + $jasaPerBulan;

            // Due date = approved_at + k months
            $dueDate = null;
            if (!empty($loan['approved_at'])) {
                $dueDate = date('Y-m-d', strtotime($loan['approved_at'] . " +{$k} months"));
            }

            $schedule[$k] = [
                'angsuran_ke' => $k,
                'pokok'       => round($pokokPerBulan, 2),
                'bunga'       => round($bungaBulanIni, 2),
                'jasa'        => round($jasaPerBulan, 2),
                'total'       => round($totalBulanIni, 2),
                'due_date'    => $dueDate,
            ];

            $sisaPokok -= $pokokPerBulan;
        }

        return $schedule;
    }

    /**
     * Determine the installment number (angsuran_ke) that falls due in a
     * given calendar month/year for a loan.
     *
     * Returns null if the loan was not yet approved or the month is before
     * the first due date.
     *
     * @param array    $loan  kop_pinjaman row (needs approved_at, tenor_bulan)
     * @param int      $month 1–12
     * @param int      $year  e.g. 2026
     * @return int|null  1-based installment number, or null if none falls on that month.
     */
    public function getInstallmentNumberForMonth(array $loan, int $month, int $year): ?int
    {
        if (empty($loan['approved_at'])) {
            return null;
        }

        $approvedDate = new \DateTime($loan['approved_at']);
        $tenor        = intval($loan['tenor_bulan']);

        for ($k = 1; $k <= $tenor; $k++) {
            $dueDate = clone $approvedDate;
            $dueDate->modify("+{$k} months");

            if ((int) $dueDate->format('n') === $month
                && (int) $dueDate->format('Y') === $year) {
                return $k;
            }
        }

        return null;
    }

    /**
     * Calculate the arrears breakdown for a single month for ONE loan.
     *
     * A loan contributes to arrears only when:
     *  - Its status is 'approved' (active, still being repaid)
     *  - An installment is due in the given month/year
     *  - That installment record does NOT exist in $paidInstallments with
     *    status 'approved'
     *
     * Paid loans (status = 'paid') are excluded for forward-looking reports
     * but INCLUDED for historical months (where the installment was due
     * during an active period). The caller must pass $loanWasPaidBeforeMonth
     * to control this.
     *
     * @param array    $loan                  kop_pinjaman row
     * @param int      $month                 1–12
     * @param int      $year                  e.g. 2026
     * @param array    $paidInstallments      Keyed by angsuran_ke → kop_angsuran row (only 'approved')
     * @param bool     $loanWasPaidBeforeMonth  True if loan reached 'paid' before this month started
     * @return array   ['bunga' => float, 'jasa' => float, 'has_due' => bool]
     */
    public function getArrearsForMonth(
        array $loan,
        int $month,
        int $year,
        array $paidInstallments,
        bool $loanWasPaidBeforeMonth = false
    ): array {
        // Fully settled loans that were already paid before this target month
        // do not contribute to historical arrears either.
        if ($loanWasPaidBeforeMonth) {
            return ['bunga' => 0.0, 'jasa' => 0.0, 'has_due' => false];
        }

        $angsuranKe = $this->getInstallmentNumberForMonth($loan, $month, $year);
        if ($angsuranKe === null) {
            return ['bunga' => 0.0, 'jasa' => 0.0, 'has_due' => false];
        }

        // If this installment is already paid (approved), no arrears
        if (isset($paidInstallments[$angsuranKe])) {
            return ['bunga' => 0.0, 'jasa' => 0.0, 'has_due' => true];
        }

        // Compute the per-installment breakdown from the amortization schedule
        $schedule = $this->buildSchedule($loan);
        if (!isset($schedule[$angsuranKe])) {
            return ['bunga' => 0.0, 'jasa' => 0.0, 'has_due' => true];
        }

        return [
            'bunga'   => $schedule[$angsuranKe]['bunga'],
            'jasa'    => $schedule[$angsuranKe]['jasa'],
            'has_due' => true,
        ];
    }

    /**
     * Build the full amortization schedule AND attach existing installment
     * records from kop_angsuran.
     *
     * Convenience method used by BillsController to avoid a separate DB loop.
     *
     * @param array $loan              kop_pinjaman row
     * @param array $installmentRecords All kop_angsuran rows for this loan, keyed by angsuran_ke
     * @return array  Same as buildSchedule() but with 'status' and 'record' keys added.
     */
    public function buildScheduleWithRecords(array $loan, array $installmentRecords): array
    {
        $schedule = $this->buildSchedule($loan);

        foreach ($schedule as $k => &$entry) {
            $record         = $installmentRecords[$k] ?? null;
            $entry['status'] = $record ? $record['status'] : 'Belum Dibayar';
            $entry['record'] = $record;
        }
        unset($entry);

        return $schedule;
    }

    /**
     * Calculate a comprehensive loan summary with aggregated metrics.
     *
     * Used by the Loan Directory Details page to show KPI widgets:
     * total principal/interest/jasa, amount paid, amount remaining,
     * overdue amount, and progress percentage.
     *
     * @param array $loan               kop_pinjaman row
     * @param array $paidInstallments   kop_angsuran rows keyed by angsuran_ke (status 'approved' only)
     * @return array  Aggregated summary metrics
     */
    public function calculateLoanSummary(array $loan, array $paidInstallments): array
    {
        $schedule = $this->buildSchedule($loan);
        $tenor    = intval($loan['tenor_bulan']);

        $totalPokok  = 0.0;
        $totalBunga  = 0.0;
        $totalJasa   = 0.0;

        $paidPokok   = 0.0;
        $paidBunga   = 0.0;
        $paidJasa    = 0.0;
        $paidTotal   = 0.0;

        $overduePokok = 0.0;
        $overdueBunga = 0.0;
        $overdueJasa  = 0.0;
        $overdueTotal = 0.0;

        $now = new \DateTime('now', new \DateTimeZone('Asia/Jakarta'));
        $angsuranDibayar = 0;

        foreach ($schedule as $k => $entry) {
            $totalPokok += $entry['pokok'];
            $totalBunga += $entry['bunga'];
            $totalJasa  += $entry['jasa'];

            if (isset($paidInstallments[$k]) && $paidInstallments[$k]['status'] === 'approved') {
                $paidPokok += $entry['pokok'];
                $paidBunga += $entry['bunga'];
                $paidJasa  += $entry['jasa'];
                $paidTotal += $entry['total'];
                $angsuranDibayar++;
            } else {
                // Check if this installment is overdue (due_date has passed)
                if (!empty($entry['due_date'])) {
                    $dueDate = new \DateTime($entry['due_date']);
                    if ($dueDate < $now) {
                        $overduePokok += $entry['pokok'];
                        $overdueBunga += $entry['bunga'];
                        $overdueJasa  += $entry['jasa'];
                        $overdueTotal += $entry['total'];
                    }
                }
            }
        }

        $grandTotal  = $totalPokok + $totalBunga + $totalJasa;
        $sisaTagihan = $grandTotal - $paidTotal;
        $progressPersen = $grandTotal > 0 ? round(($paidTotal / $grandTotal) * 100, 1) : 0;

        return [
            'total_pokok'       => round($totalPokok, 2),
            'total_bunga'       => round($totalBunga, 2),
            'total_jasa'        => round($totalJasa, 2),
            'grand_total'       => round($grandTotal, 2),

            'paid_pokok'        => round($paidPokok, 2),
            'paid_bunga'        => round($paidBunga, 2),
            'paid_jasa'         => round($paidJasa, 2),
            'paid_total'        => round($paidTotal, 2),

            'overdue_pokok'     => round($overduePokok, 2),
            'overdue_bunga'     => round($overdueBunga, 2),
            'overdue_jasa'      => round($overdueJasa, 2),
            'overdue_total'     => round($overdueTotal, 2),

            'sisa_tagihan'      => round($sisaTagihan, 2),
            'progress_persen'   => $progressPersen,
            'angsuran_dibayar'  => $angsuranDibayar,
            'tenor'             => $tenor,
        ];
    }
}
