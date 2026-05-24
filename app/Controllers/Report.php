<?php

namespace App\Controllers;

use App\Services\ChartService;
use App\Services\ExportService;
use App\Services\ReportPeriodService;

class Report extends BaseController
{
    protected ChartService $chartService;
    protected ExportService $exportService;
    protected ReportPeriodService $reportPeriodService;

    public function __construct()
    {
        $this->chartService = new ChartService();
        $this->exportService = new ExportService();
        $this->reportPeriodService = new ReportPeriodService();
    }

    /**
     * Display reporting dashboard
     */
    public function index()
    {
        $userId = auth()->id();
        
        // 1. Get dates from query params
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $search = $this->request->getGet('search');
        $type = $this->request->getGet('type');

        // Compile filters
        $filters = [
            'type'       => $type,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'search'     => $search
        ];

        // 2. Fetch filtered transactions list to calculate overall summaries for selected period
        $transactions = $this->exportService->getFilteredTransactions($userId, $filters);
        
        $totalIncome = 0;
        $totalExpense = 0;
        $incomeCount = 0;
        $expenseCount = 0;

        $incomeCatMap = [];
        $expenseCatMap = [];

        foreach ($transactions as $tx) {
            $amount = floatval($tx['amount']);
            $catName = $tx['category_name'] ?: 'Kategori Dihapus';

            if ($tx['type'] === 'income') {
                $totalIncome += $amount;
                $incomeCount++;
                if (!isset($incomeCatMap[$catName])) $incomeCatMap[$catName] = 0;
                $incomeCatMap[$catName] += $amount;
            } else {
                $totalExpense += $amount;
                $expenseCount++;
                if (!isset($expenseCatMap[$catName])) $expenseCatMap[$catName] = 0;
                $expenseCatMap[$catName] += $amount;
            }
        }

        // Helper to get Top N categories
        $getTopN = function($catMap, $limit = 5) {
            arsort($catMap);
            $labels = [];
            $series = [];
            $othersSum = 0;
            $count = 0;
            foreach ($catMap as $cat => $val) {
                if ($count < $limit) {
                    $labels[] = $cat;
                    $series[] = $val;
                } else {
                    $othersSum += $val;
                }
                $count++;
            }
            if ($othersSum > 0) {
                $labels[] = 'Lainnya';
                $series[] = $othersSum;
            }
            return ['labels' => $labels, 'series' => $series];
        };

        $topIncomeCategories = $getTopN($incomeCatMap, 5);
        $topExpenseCategories = $getTopN($expenseCatMap, 5);

        $netBalance = $totalIncome - $totalExpense;

        return view('user/reports/index', [
            'title'             => 'Analisis & Laporan',
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
            'incomeCount'       => $incomeCount,
            'expenseCount'      => $expenseCount,
            'topIncomeJSON'     => json_encode($topIncomeCategories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            'topExpenseJSON'    => json_encode($topExpenseCategories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP),
            'netBalance'        => $netBalance,
            'filterStartDate'   => $startDate,
            'filterEndDate'     => $endDate,
            'filterSearch'      => $search,
            'filterType'        => $type,
        ]);
    }

    /**
     * Get JSON data for frontend charts
     */
    public function chartData()
    {
        $userId = auth()->id();
        $year = $this->request->getGet('year') ? intval($this->request->getGet('year')) : intval(date('Y'));

        // Category distributions
        $incomeCategory = $this->chartService->getCategoryDistribution($userId, 'income');
        $expenseCategory = $this->chartService->getCategoryDistribution($userId, 'expense');

        // Monthly trends
        $monthlyTrend = $this->chartService->getMonthlyTrend($userId, $year);

        return $this->response->setJSON([
            'year'            => $year,
            'incomeCategory'  => $incomeCategory,
            'expenseCategory' => $expenseCategory,
            'monthlyTrend'    => $monthlyTrend
        ]);
    }

    /**
     * Export data to PDF, Excel, or CSV
     */
    public function export()
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        // Handle both GET (for CSV/Excel standard links) and POST (for PDF with charts)
        $format = $this->request->getPostGet('format') ?: 'pdf';
        
        $startMonth = $this->request->getPostGet('start_month');
        $endMonth = $this->request->getPostGet('end_month');

        try {
            $parsedDates = $this->reportPeriodService->parseMonthRange($startMonth, $endMonth);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
        
        $filters = [
            'type'       => $this->request->getPostGet('type'),
            'start_date' => $parsedDates['start_date'],
            'end_date'   => $parsedDates['end_date'],
            'search'     => $this->request->getPostGet('search')
        ];

        $timestamp = date('Ymd_His');
        
        switch (strtolower($format)) {
            case 'csv':
                $csvData = $this->exportService->exportToCSV($userId, $filters);
                
                return $this->response
                    ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
                    ->setHeader('Content-Disposition', 'attachment; filename="laporan_keuangan_' . $timestamp . '.csv"')
                    ->setBody($csvData);

            case 'excel':
                $excelData = $this->exportService->exportToExcel($userId, $filters);
                
                return $this->response
                    ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->setHeader('Content-Disposition', 'attachment; filename="laporan_keuangan_' . $timestamp . '.xlsx"')
                    ->setBody($excelData);

            case 'pdf':
            default:
                $trendChart = $this->request->getPost('trend_chart');
                $categoryChart = $this->request->getPost('category_chart');

                $pdfData = $this->exportService->exportToPDF($userId, $filters, $user, $trendChart, $categoryChart);
                
                return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="laporan_keuangan_grafik_' . $timestamp . '.pdf"')
                    ->setBody($pdfData);
        }
    }
}
