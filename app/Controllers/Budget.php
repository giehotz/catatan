<?php

namespace App\Controllers;

use App\Services\BudgetService;
use App\Models\ExpenseCategoryModel;
use App\Models\TransactionModel;

class Budget extends BaseController
{
    protected BudgetService $budgetService;
    protected ExpenseCategoryModel $expenseCategoryModel;
    protected TransactionModel $transactionModel;

    public function __construct()
    {
        $this->budgetService = new BudgetService();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
        $this->transactionModel = new TransactionModel();
    }

    /**
     * Display budgeting dashboard
     */
    public function index()
    {
        $userId = auth()->id();
        
        $report = $this->budgetService->getBudgetReport($userId);
        
        // Calculate overall totals
        $totalBudget = 0.0;
        foreach ($report as $item) {
            $totalBudget += $item['limit_amount'];
        }
        
        // Calculate overall actual expense this month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $totalExpenseData = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date >=', $startOfMonth)
            ->where('transaction_date <=', $endOfMonth)
            ->selectSum('amount')
            ->first();
        $totalSpending = floatval($totalExpenseData['amount'] ?? 0.0);

        // Fetch categories for the drop-down (only expense categories)
        $expenseCategories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();

        return view('user/budgets/index', [
            'title'             => 'Anggaran Bulanan',
            'report'            => $report,
            'totalBudget'       => $totalBudget,
            'totalSpending'     => $totalSpending,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    /**
     * Create or update a budget limit
     */
    public function setLimit()
    {
        $rules = [
            'category_id'  => 'required|numeric',
            'limit_amount' => 'required|numeric|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = auth()->id();
        $categoryId = intval($this->request->getPost('category_id'));
        $limitAmount = floatval($this->request->getPost('limit_amount'));

        // Verify category belongs to this user
        $category = $this->expenseCategoryModel
            ->where('user_id', $userId)
            ->find($categoryId);

        if (!$category) {
            return redirect()->back()->with('error', 'Kategori pengeluaran tidak ditemukan atau tidak valid.');
        }

        if ($this->budgetService->setLimit($userId, $categoryId, $limitAmount)) {
            return redirect()->to('/budgets')->with('success', 'Batas anggaran bulanan berhasil disetel!');
        }

        return redirect()->back()->with('error', 'Gagal menyetel batas anggaran bulanan.');
    }

    /**
     * Delete a budget limit
     */
    public function delete(int $id)
    {
        $userId = auth()->id();

        if ($this->budgetService->deleteLimit($userId, $id)) {
            return redirect()->to('/budgets')->with('success', 'Batas anggaran bulanan berhasil dihapus!');
        }

        return redirect()->back()->with('error', 'Gagal menghapus batas anggaran bulanan.');
    }
}
