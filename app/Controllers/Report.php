<?php

namespace App\Controllers;

use App\Services\ChartService;
use App\Services\ExportService;

class Report extends BaseController
{
    protected ChartService $chartService;
    protected ExportService $exportService;

    public function __construct()
    {
        $this->chartService = new ChartService();
        $this->exportService = new ExportService();
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

        foreach ($transactions as $tx) {
            if ($tx['type'] === 'income') {
                $totalIncome += $tx['amount'];
            } else {
                $totalExpense += $tx['amount'];
            }
        }

        $netBalance = $totalIncome - $totalExpense;

        return view('user/reports/index', [
            'title'             => 'Analisis & Laporan',
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
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
        
        $filters = [
            'type'       => $this->request->getPostGet('type'),
            'start_date' => $this->request->getPostGet('start_date'),
            'end_date'   => $this->request->getPostGet('end_date'),
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
