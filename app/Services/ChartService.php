<?php

namespace App\Services;

use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;

class ChartService
{
    protected TransactionModel $transactionModel;
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;

    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
    }

    /**
     * Get transaction distribution by category for a user
     */
    public function getCategoryDistribution(int $userId, string $type): array
    {
        // 1. Fetch categories
        $categories = $type === 'income' 
            ? $this->incomeCategoryModel->where('user_id', $userId)->findAll()
            : $this->expenseCategoryModel->where('user_id', $userId)->findAll();

        $categoryMap = [];
        foreach ($categories as $cat) {
            $categoryMap[$cat['id']] = $cat['name'];
        }

        // 2. Query transactions grouped by category_id
        $results = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', $type)
            ->select('category_id')
            ->selectSum('amount')
            ->groupBy('category_id')
            ->findAll();

        $labels = [];
        $series = [];

        foreach ($results as $row) {
            $catId = intval($row['category_id']);
            $amount = floatval($row['amount']);
            $catName = $categoryMap[$catId] ?? 'Kategori Dihapus';

            $labels[] = $catName;
            $series[] = $amount;
        }

        // If empty, return a fallback so chart displays something nice
        if (empty($series)) {
            $labels[] = 'Belum Ada Transaksi';
            $series[] = 0;
        }

        return [
            'labels' => $labels,
            'series' => $series
        ];
    }

    /**
     * Get monthly income vs expense trend for a user for a specific year
     */
    public function getMonthlyTrend(int $userId, int $year): array
    {
        $months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 
            'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        $incomeData = array_fill(0, 12, 0);
        $expenseData = array_fill(0, 12, 0);

        // Fetch income transactions for this year
        $incomeTx = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->where('YEAR(transaction_date)', $year)
            ->select('MONTH(transaction_date) as month')
            ->selectSum('amount')
            ->groupBy('month')
            ->findAll();

        foreach ($incomeTx as $row) {
            $monthIndex = intval($row['month']) - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $incomeData[$monthIndex] = floatval($row['amount']);
            }
        }

        // Fetch expense transactions for this year
        $expenseTx = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('YEAR(transaction_date)', $year)
            ->select('MONTH(transaction_date) as month')
            ->selectSum('amount')
            ->groupBy('month')
            ->findAll();

        foreach ($expenseTx as $row) {
            $monthIndex = intval($row['month']) - 1;
            if ($monthIndex >= 0 && $monthIndex < 12) {
                $expenseData[$monthIndex] = floatval($row['amount']);
            }
        }

        return [
            'categories' => $months,
            'income'     => $incomeData,
            'expense'    => $expenseData
        ];
    }
}
