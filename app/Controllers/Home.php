<?php
 
namespace App\Controllers;
 
use App\Models\TransactionModel;
use App\Models\DebtModel;
use App\Models\ReceivableModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
use App\Models\WalletModel;
use App\Services\BudgetService;
use App\Services\RecurringService;
 
class Home extends BaseController
{
    protected TransactionModel $transactionModel;
    protected DebtModel $debtModel;
    protected ReceivableModel $receivableModel;
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;
    protected WalletModel $walletModel;
    protected BudgetService $budgetService;
    protected RecurringService $recurringService;
 
    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->debtModel = new DebtModel();
        $this->receivableModel = new ReceivableModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
        $this->walletModel = new WalletModel();
        $this->budgetService = new BudgetService();
        $this->recurringService = new RecurringService();
    }
 
    public function index()
    {
        $userId = auth()->id();
        $this->recurringService->processUserSchedules($userId);
 
        // 1. Calculate Pemasukan & Pengeluaran Bulan Ini
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
 
        // Total Pemasukan Bulan Ini
        $incomeThisMonth = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->where('transaction_date >=', $startOfMonth)
            ->where('transaction_date <=', $endOfMonth)
            ->selectSum('amount')
            ->first();
        $totalIncome = floatval($incomeThisMonth['amount'] ?? 0);
 
        // Total Pengeluaran Bulan Ini
        $expenseThisMonth = $this->transactionModel
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date >=', $startOfMonth)
            ->where('transaction_date <=', $endOfMonth)
            ->selectSum('amount')
            ->first();
        $totalExpense = floatval($expenseThisMonth['amount'] ?? 0);
 
        // 2. Calculate Total Utang & Piutang Belum Lunas (Status: unpaid / partial)
        $debtsSum = $this->debtModel
            ->where('user_id', $userId)
            ->where('status !=', 'paid')
            ->selectSum('total_amount')
            ->first();
        $totalDebt = floatval($debtsSum['total_amount'] ?? 0);
 
        $receivablesSum = $this->receivableModel
            ->where('user_id', $userId)
            ->where('status !=', 'paid')
            ->selectSum('total_amount')
            ->first();
        $totalReceivable = floatval($receivablesSum['total_amount'] ?? 0);
 
        // 3. Fetch Recent 5 Transactions
        $recentTransactions = $this->transactionModel
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->findAll();
 
        // Cache wallets for fast lookup
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
        $walletsCache = [];
        foreach ($wallets as $w) {
            $walletsCache[$w['id']] = $w;
        }
 
        foreach ($recentTransactions as &$tx) {
            $tx['wallet_name'] = isset($walletsCache[$tx['wallet_id']]) ? $walletsCache[$tx['wallet_id']]['name'] : 'Dompet Dihapus';
            $tx['to_wallet_name'] = isset($walletsCache[$tx['to_wallet_id']]) ? $walletsCache[$tx['to_wallet_id']]['name'] : '';
 
            if ($tx['type'] === 'income') {
                $cat = $this->incomeCategoryModel->find($tx['category_id']);
                $tx['category_name'] = $cat ? $cat['name'] : 'Kategori Dihapus';
            } elseif ($tx['type'] === 'expense') {
                $cat = $this->expenseCategoryModel->find($tx['category_id']);
                $tx['category_name'] = $cat ? $cat['name'] : 'Kategori Dihapus';
            } else {
                $tx['category_name'] = 'Transfer Saldo';
            }
        }
 
        // 4. Fetch Active Debts & Receivables quick overview (Urgent Unpaid / Partial)
        $activeDebts = $this->debtModel
            ->where('user_id', $userId)
            ->where('status !=', 'paid')
            ->orderBy('due_date', 'ASC')
            ->limit(2)
            ->findAll();
 
        $activeReceivables = $this->receivableModel
            ->where('user_id', $userId)
            ->where('status !=', 'paid')
            ->orderBy('due_date', 'ASC')
            ->limit(2)
            ->findAll();
 
        $smartAlerts = $this->budgetService->getSmartAlerts($userId);
 
        return view('user/dashboard', [
            'title'              => 'Dasbor Utama',
            'totalIncome'        => $totalIncome,
            'totalExpense'       => $totalExpense,
            'totalDebt'          => $totalDebt,
            'totalReceivable'    => $totalReceivable,
            'recentTransactions' => $recentTransactions,
            'activeDebts'        => $activeDebts,
            'activeReceivables'  => $activeReceivables,
            'smartAlerts'        => $smartAlerts,
            'wallets'            => $wallets,
        ]);
    }
}
