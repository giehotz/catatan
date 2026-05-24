<?php

namespace App\Services;

use InvalidArgumentException;
use DateTime;

class ReportPeriodService
{
    /**
     * Parse and validate start_month and end_month to exact start_date and end_date
     *
     * @param string|null $startMonth (format: YYYY-MM)
     * @param string|null $endMonth (format: YYYY-MM)
     * @return array ['start_date' => 'YYYY-MM-DD', 'end_date' => 'YYYY-MM-DD']
     * @throws InvalidArgumentException
     */
    public function parseMonthRange(?string $startMonth, ?string $endMonth): array
    {
        if (empty($startMonth) || empty($endMonth)) {
            throw new InvalidArgumentException("Parameter rentang bulan (Mulai & Akhir) wajib diisi.");
        }

        // Validate format YYYY-MM
        if (!preg_match('/^\d{4}-\d{2}$/', $startMonth) || !preg_match('/^\d{4}-\d{2}$/', $endMonth)) {
            throw new InvalidArgumentException("Format bulan tidak valid. Gunakan format YYYY-MM.");
        }

        try {
            $start = new DateTime($startMonth . '-01');
            $end = new DateTime($endMonth . '-01');
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Bulan yang dimasukkan tidak valid.");
        }

        if ($start > $end) {
            throw new InvalidArgumentException("Bulan Akhir tidak boleh lebih awal dari Bulan Mulai.");
        }

        // Calculate month difference
        $yearDiff = (int)$end->format('Y') - (int)$start->format('Y');
        $monthDiff = (int)$end->format('n') - (int)$start->format('n');
        
        // Total months inclusive (e.g. same month = 1)
        $totalMonths = ($yearDiff * 12) + $monthDiff + 1;

        if ($totalMonths > 12) {
            throw new InvalidArgumentException("Rentang ekspor maksimal adalah 12 bulan.");
        }

        // start_date is the first day of startMonth
        $startDateStr = $start->format('Y-m-d');
        
        // end_date is the last day of endMonth
        // Use 't' to get the number of days in the given month
        $endDateStr = $end->format('Y-m-t');

        return [
            'start_date' => $startDateStr,
            'end_date' => $endDateStr
        ];
    }
}
