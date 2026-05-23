<?php

namespace App\Services;

use App\Models\BudgetModel;
use App\Models\TransactionModel;
use App\Models\ExpenseCategoryModel;

class BudgetService
{
    protected BudgetModel $budgetModel;
    protected TransactionModel $transactionModel;
    protected ExpenseCategoryModel $expenseCategoryModel;

    public function __construct()
    {
        $this->budgetModel = new BudgetModel();
        $this->transactionModel = new TransactionModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
    }

    /**
     * Get monthly budgeting report for a user
     *
     * @param int $userId
     * @return array
     */
    public function getBudgetReport(int $userId): array
    {
        // 1. Fetch all expense categories for user
        $categories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();
        
        // 2. Fetch all budgets/limits
        $budgets = $this->budgetModel->where('user_id', $userId)->findAll();
        $budgetMap = [];
        foreach ($budgets as $b) {
            $budgetMap[intval($b['category_id'])] = [
                'id' => intval($b['id']),
                'limit_amount' => floatval($b['limit_amount'])
            ];
        }

        // 3. Fetch expenses for current month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $expenseTx = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date >=', $startOfMonth)
            ->where('transaction_date <=', $endOfMonth)
            ->select('category_id')
            ->selectSum('amount')
            ->groupBy('category_id')
            ->findAll();

        $spendingMap = [];
        foreach ($expenseTx as $row) {
            $spendingMap[intval($row['category_id'])] = floatval($row['amount']);
        }

        // 4. Map parent-child relationships
        $parentMap = [];
        $childrenMap = [];
        foreach ($categories as $cat) {
            $id = intval($cat['id']);
            $parentId = $cat['parent_id'] !== null ? intval($cat['parent_id']) : null;
            if ($parentId === null) {
                $parentMap[$id] = $cat;
                $parentMap[$id]['children'] = [];
            } else {
                $childrenMap[$parentId][] = $cat;
            }
        }

        // Add children to parent
        foreach ($childrenMap as $parentId => $children) {
            if (isset($parentMap[$parentId])) {
                $parentMap[$parentId]['children'] = $children;
            }
        }

        // 5. Calculate direct and rollup spending
        $report = [];
        
        // Helper to calculate total parent spending (direct + all children)
        foreach ($categories as $cat) {
            $id = intval($cat['id']);
            $name = $cat['name'];
            $parentId = $cat['parent_id'] !== null ? intval($cat['parent_id']) : null;
            
            // Calculate direct spending
            $directSpending = $spendingMap[$id] ?? 0.0;
            
            // If it is a parent category, add all its children's spending to rollup spending
            $totalSpending = $directSpending;
            if ($parentId === null && isset($childrenMap[$id])) {
                foreach ($childrenMap[$id] as $child) {
                    $childId = intval($child['id']);
                    $totalSpending += $spendingMap[$childId] ?? 0.0;
                }
            }
            
            $limitInfo = $budgetMap[$id] ?? null;
            $limitAmount = $limitInfo ? $limitInfo['limit_amount'] : 0.0;
            $budgetId = $limitInfo ? $limitInfo['id'] : null;

            $percent = 0.0;
            $statusColor = 'green'; // green, yellow, red
            $statusText = 'Aman'; // Safe

            if ($limitAmount > 0) {
                $percent = ($totalSpending / $limitAmount) * 100;
                if ($percent > 85) {
                    $statusColor = 'red';
                    $statusText = $percent > 100 ? 'Overbudget' : 'Kritis';
                } elseif ($percent >= 60) {
                    $statusColor = 'yellow';
                    $statusText = 'Peringatan';
                }
            }

            $reportItem = [
                'category_id'     => $id,
                'budgetId'        => $budgetId,
                'name'            => $name,
                'parent_id'       => $parentId,
                'spending'        => $totalSpending,
                'limit_amount'    => $limitAmount,
                'percent'         => round($percent, 1),
                'status_color'    => $statusColor,
                'status_text'     => $statusText,
                'is_parent'       => ($parentId === null),
                'children_count'  => $parentId === null ? (isset($childrenMap[$id]) ? count($childrenMap[$id]) : 0) : 0
            ];

            $report[$id] = $reportItem;
        }

        return $report;
    }

    /**
     * Get categories nearing or exceeding limits (percent >= 80)
     *
     * @param int $userId
     * @return array
     */
    public function getSmartAlerts(int $userId): array
    {
        $report = $this->getBudgetReport($userId);
        $alerts = [];
        foreach ($report as $item) {
            if ($item['limit_amount'] > 0 && $item['percent'] >= 80) {
                $alerts[] = $item;
            }
        }
        return $alerts;
    }

    /**
     * Set monthly limit amount for a category
     *
     * @param int $userId
     * @param int $categoryId
     * @param float $amount
     * @return bool
     */
    public function setLimit(int $userId, int $categoryId, float $amount): bool
    {
        $existing = $this->budgetModel
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->first();

        $data = [
            'user_id'      => $userId,
            'category_id'  => $categoryId,
            'limit_amount' => $amount
        ];

        if ($existing) {
            return $this->budgetModel->update($existing['id'], $data);
        } else {
            return $this->budgetModel->insert($data) !== false;
        }
    }

    /**
     * Delete a budget limit
     *
     * @param int $userId
     * @param int $budgetId
     * @return bool
     */
    public function deleteLimit(int $userId, int $budgetId): bool
    {
        return $this->budgetModel
            ->where('user_id', $userId)
            ->where('id', $budgetId)
            ->delete() !== false;
    }
}
