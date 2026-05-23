<?php

use App\Services\LoanAmortizationService;
use CodeIgniter\Test\CIUnitTestCase;

final class LoanAmortizationServiceTest extends CIUnitTestCase
{
    private LoanAmortizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LoanAmortizationService();
    }

    public function testBuildScheduleFlatRateCicil(): void
    {
        $loan = [
            'nominal_pinjaman' => 12000000,
            'tenor_bulan'      => 12,
            'bunga_persen'     => 1.5,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 50000,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-15 10:00:00',
        ];

        $schedule = $this->service->buildSchedule($loan);

        $this->assertCount(12, $schedule);
        $this->assertSame(1, $schedule[1]['angsuran_ke']);
        $this->assertSame(12, $schedule[12]['angsuran_ke']);

        // Pokok per bulan: 12.000.000 / 12 = 1.000.000
        $this->assertSame(1000000.0, $schedule[1]['pokok']);
        $this->assertSame(1000000.0, $schedule[6]['pokok']);
        $this->assertSame(1000000.0, $schedule[12]['pokok']);

        // Bunga flat per bulan: 12.000.000 * (1.5/100) = 180.000
        $this->assertSame(180000.0, $schedule[1]['bunga']);
        $this->assertSame(180000.0, $schedule[12]['bunga']);

        // Jasa per bulan: 50.000 / 12 = 4.166.67
        $this->assertSame(4166.67, $schedule[1]['jasa']);

        // Total per bulan: 1.000.000 + 180.000 + 4.166.67 = 1.184.166.67
        $this->assertSame(1184166.67, $schedule[1]['total']);

        // Due dates
        $this->assertSame('2026-02-15', $schedule[1]['due_date']);
        $this->assertSame('2026-07-15', $schedule[6]['due_date']);
        $this->assertSame('2027-01-15', $schedule[12]['due_date']);
    }

    public function testBuildScheduleFlatRateDiAwal(): void
    {
        $loan = [
            'nominal_pinjaman' => 6000000,
            'tenor_bulan'      => 6,
            'bunga_persen'     => 2.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'di_awal',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-03-01 08:00:00',
        ];

        $schedule = $this->service->buildSchedule($loan);

        $this->assertCount(6, $schedule);

        // Bunga = 0 per bulan because paid upfront
        $this->assertSame(0.0, $schedule[1]['bunga']);
        $this->assertSame(0.0, $schedule[6]['bunga']);

        // Pokok per bulan: 6.000.000 / 6 = 1.000.000
        $this->assertSame(1000000.0, $schedule[1]['pokok']);

        // Total = pokok only (bunga = 0, jasa = 0)
        $this->assertSame(1000000.0, $schedule[1]['total']);
    }

    public function testBuildScheduleEffectiveRate(): void
    {
        $loan = [
            'nominal_pinjaman' => 12000000,
            'tenor_bulan'      => 12,
            'bunga_persen'     => 1.5,
            'jenis_bunga'      => 'efektif',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        $schedule = $this->service->buildSchedule($loan);

        $this->assertCount(12, $schedule);
        $this->assertSame(1000000.0, $schedule[1]['pokok']);

        // Month 1: bunga on full principal = 12.000.000 * 0.015 = 180.000
        $this->assertSame(180000.0, $schedule[1]['bunga']);

        // Month 6: remaining principal = 12.000.000 - 5*1.000.000 = 7.000.000
        // bunga = 7.000.000 * 0.015 = 105.000
        $this->assertSame(105000.0, $schedule[6]['bunga']);

        // Month 12: remaining principal = 1.000.000
        // bunga = 1.000.000 * 0.015 = 15.000
        $this->assertSame(15000.0, $schedule[12]['bunga']);
    }

    public function testBuildScheduleEffectiveBungaDeclinesOverTime(): void
    {
        $loan = [
            'nominal_pinjaman' => 12000000,
            'tenor_bulan'      => 12,
            'bunga_persen'     => 1.5,
            'jenis_bunga'      => 'efektif',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        $schedule = $this->service->buildSchedule($loan);

        // Effective rate: bunga declines each month
        $bungaValues = array_column($schedule, 'bunga');
        for ($i = 0; $i < count($bungaValues) - 1; $i++) {
            $this->assertGreaterThan(
                $bungaValues[$i + 1],
                $bungaValues[$i],
                "Bunga should strictly decline with effective rate, but month " . ($i + 1) . " <= month " . ($i + 2)
            );
        }
    }

    public function testBuildScheduleEmptyForZeroTenor(): void
    {
        $loan = [
            'nominal_pinjaman' => 1000000,
            'tenor_bulan'      => 0,
            'bunga_persen'     => 1.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
        ];

        $schedule = $this->service->buildSchedule($loan);
        $this->assertEmpty($schedule);
    }

    public function testBuildScheduleWithRecords(): void
    {
        $loan = [
            'nominal_pinjaman' => 3000000,
            'tenor_bulan'      => 3,
            'bunga_persen'     => 1.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-05-01 00:00:00',
        ];

        $records = [
            1 => ['angsuran_ke' => 1, 'status' => 'approved', 'tanggal_bayar' => '2026-05-15'],
        ];

        $schedule = $this->service->buildScheduleWithRecords($loan, $records);

        $this->assertCount(3, $schedule);
        $this->assertSame('approved', $schedule[1]['status']);
        $this->assertSame('Belum Dibayar', $schedule[2]['status']);
        $this->assertSame('Belum Dibayar', $schedule[3]['status']);
        $this->assertNotNull($schedule[1]['record']);
        $this->assertNull($schedule[2]['record']);
    }

    public function testGetInstallmentNumberForMonth(): void
    {
        $loan = [
            'approved_at' => '2026-01-15 10:00:00',
            'tenor_bulan' => 12,
        ];

        // Month 1 after approval = February (month 2)
        $this->assertSame(1, $this->service->getInstallmentNumberForMonth($loan, 2, 2026));
        $this->assertSame(2, $this->service->getInstallmentNumberForMonth($loan, 3, 2026));
        $this->assertSame(12, $this->service->getInstallmentNumberForMonth($loan, 1, 2027));

        // No installment due in January 2026 (approved mid-January, first due Feb)
        $this->assertNull($this->service->getInstallmentNumberForMonth($loan, 1, 2026));

        // No installment due after tenor
        $this->assertNull($this->service->getInstallmentNumberForMonth($loan, 2, 2027));
    }

    public function testGetInstallmentNumberForMonthNullWhenNoApprovedAt(): void
    {
        $loan = ['approved_at' => null, 'tenor_bulan' => 12];
        $this->assertNull($this->service->getInstallmentNumberForMonth($loan, 6, 2026));
    }

    public function testGetArrearsForMonthNoDueInstallment(): void
    {
        $loan = ['approved_at' => '2026-06-01', 'tenor_bulan' => 6];
        $result = $this->service->getArrearsForMonth($loan, 1, 2026, []);
        $this->assertFalse($result['has_due']);
        $this->assertSame(0.0, $result['bunga']);
        $this->assertSame(0.0, $result['jasa']);
    }

    public function testGetArrearsForMonthPaidInstallment(): void
    {
        $loan = [
            'nominal_pinjaman' => 6000000,
            'tenor_bulan'      => 6,
            'bunga_persen'     => 1.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        // First installment (Feb 2026) is paid
        $paid = [1 => ['status' => 'approved']];
        $result = $this->service->getArrearsForMonth($loan, 2, 2026, $paid);

        $this->assertTrue($result['has_due']);
        $this->assertSame(0.0, $result['bunga']);
    }

    public function testGetArrearsForMonthUnpaidInstallment(): void
    {
        $loan = [
            'nominal_pinjaman' => 6000000,
            'tenor_bulan'      => 6,
            'bunga_persen'     => 1.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        // First installment NOT paid
        $result = $this->service->getArrearsForMonth($loan, 2, 2026, []);

        $this->assertTrue($result['has_due']);
        // Bunga for month 1: 6.000.000 * 0.01 = 60.000
        $this->assertSame(60000.0, $result['bunga']);
    }

    public function testGetArrearsForMonthLoanPaidBeforeMonth(): void
    {
        $loan = [
            'nominal_pinjaman' => 6000000,
            'tenor_bulan'      => 6,
            'bunga_persen'     => 1.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        // Loan was paid before March 2026
        $result = $this->service->getArrearsForMonth($loan, 3, 2026, [], true);
        $this->assertFalse($result['has_due']);
        $this->assertSame(0.0, $result['bunga']);
    }

    public function testCalculateLoanSummaryNoPayments(): void
    {
        $loan = [
            'nominal_pinjaman' => 12000000,
            'tenor_bulan'      => 12,
            'bunga_persen'     => 1.5,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        $summary = $this->service->calculateLoanSummary($loan, []);

        $this->assertSame(12000000.0, $summary['total_pokok']);
        // 12 * 180.000 = 2.160.000
        $this->assertSame(2160000.0, $summary['total_bunga']);
        $this->assertSame(14160000.0, $summary['grand_total']);
        $this->assertSame(0.0, $summary['paid_total']);
        $this->assertSame(14160000.0, $summary['sisa_tagihan']);
        $this->assertSame(0.0, $summary['progress_persen']);
    }

    public function testCalculateLoanSummaryPartialPayment(): void
    {
        $loan = [
            'nominal_pinjaman' => 12000000,
            'tenor_bulan'      => 12,
            'bunga_persen'     => 1.5,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 0,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        // 3 installments paid
        $paid = [1 => ['status' => 'approved'], 2 => ['status' => 'approved'], 3 => ['status' => 'approved']];
        $summary = $this->service->calculateLoanSummary($loan, $paid);

        // Paid: 3 * (1.000.000 + 180.000) = 3.540.000
        $this->assertSame(3540000.0, $summary['paid_total']);

        // Progress: 3.540.000 / 14.160.000 * 100 = 25%
        $this->assertSame(25.0, $summary['progress_persen']);

        $this->assertSame(3, $summary['angsuran_dibayar']);
    }

    public function testCalculateLoanSummaryFullPayment(): void
    {
        $loan = [
            'nominal_pinjaman' => 6000000,
            'tenor_bulan'      => 3,
            'bunga_persen'     => 2.0,
            'jenis_bunga'      => 'flat',
            'bunga_opsi_bayar' => 'cicil',
            'jasa_nominal'     => 25000,
            'metode_bayar_jasa'=> 'cicil',
            'approved_at'      => '2026-01-01 00:00:00',
        ];

        $paid = [
            1 => ['status' => 'approved'],
            2 => ['status' => 'approved'],
            3 => ['status' => 'approved'],
        ];

        $summary = $this->service->calculateLoanSummary($loan, $paid);
        $this->assertSame(100.0, $summary['progress_persen']);
        $this->assertSame(3, $summary['angsuran_dibayar']);
    }
}
