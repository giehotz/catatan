<?php
 
namespace App\Controllers;
 
use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use App\Models\ExpenseCategoryModel;
use App\Models\WalletModel;
 
class Transaction extends BaseController
{
    protected TransactionModel $transactionModel;
    protected IncomeCategoryModel $incomeCategoryModel;
    protected ExpenseCategoryModel $expenseCategoryModel;
    protected WalletModel $walletModel;
 
    public function __construct()
    {
        $this->transactionModel = new TransactionModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
        $this->expenseCategoryModel = new ExpenseCategoryModel();
        $this->walletModel = new WalletModel();
    }
 
    public function index()
    {
        $userId = auth()->id();
 
        // 1. Seed default categories if user doesn't have any yet
        $this->seedDefaultCategories($userId);
 
        // 2. Fetch all categories & wallets for user dropdown select
        $incomeCategories = $this->incomeCategoryModel->where('user_id', $userId)->findAll();
        $expenseCategories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
 
        // 3. Process Filters
        $type = $this->request->getGet('type');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');
        $search = $this->request->getGet('search');
        $walletFilter = $this->request->getGet('wallet_id');
 
        $query = $this->transactionModel->where('user_id', $userId);
 
        if (!empty($type)) {
            $query->where('type', $type);
        }
        if (!empty($startDate)) {
            $query->where('transaction_date >=', $startDate);
        }
        if (!empty($endDate)) {
            $query->where('transaction_date <=', $endDate);
        }
        if (!empty($search)) {
            $query->like('description', $search);
        }
        if (!empty($walletFilter)) {
            $query->groupStart()
                  ->where('wallet_id', $walletFilter)
                  ->orWhere('to_wallet_id', $walletFilter)
                  ->groupEnd();
        }
 
        // Sort by date DESC, then id DESC
        $transactions = $query->orderBy('transaction_date', 'DESC')->orderBy('id', 'DESC')->findAll();
 
        // 4. Calculate Summaries and fetch names
        $totalIncome = 0;
        $totalExpense = 0;
 
        // Cache wallets for fast lookup
        $walletsCache = [];
        foreach ($wallets as $w) {
            $walletsCache[$w['id']] = $w;
        }
 
        foreach ($transactions as &$tx) {
            $amount = floatval($tx['amount']);
            
            // Add wallet names
            $tx['wallet_name'] = isset($walletsCache[$tx['wallet_id']]) ? $walletsCache[$tx['wallet_id']]['name'] : 'Dompet Dihapus';
            $tx['to_wallet_name'] = isset($walletsCache[$tx['to_wallet_id']]) ? $walletsCache[$tx['to_wallet_id']]['name'] : '';
 
            if ($tx['type'] === 'income') {
                $totalIncome += $amount;
                $cat = $this->incomeCategoryModel->find($tx['category_id']);
                $tx['category_name'] = $cat ? $cat['name'] : 'Kategori Dihapus';
            } elseif ($tx['type'] === 'expense') {
                $totalExpense += $amount;
                $cat = $this->expenseCategoryModel->find($tx['category_id']);
                $tx['category_name'] = $cat ? $cat['name'] : 'Kategori Dihapus';
            } else {
                // Transfer: doesn't affect period's income/expense, just records a movement
                $tx['category_name'] = 'Transfer Saldo';
            }
        }
 
        $netBalance = $totalIncome - $totalExpense;
 
        // Render view
        return view('user/transactions/index', [
            'title'             => 'Kelola Transaksi',
            'transactions'      => $transactions,
            'incomeCategories'  => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'wallets'           => $wallets,
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
            'netBalance'        => $netBalance,
            'filterType'        => $type,
            'filterStartDate'   => $startDate,
            'filterEndDate'     => $endDate,
            'filterSearch'      => $search,
            'filterWallet'      => $walletFilter,
        ]);
    }
 
    public function create()
    {
        $rules = [
            'type'             => 'required|in_list[income,expense]',
            'wallet_id'        => 'required|numeric',
            'category_id'      => 'required|numeric',
            'amount'           => 'required|numeric|greater_than[0]',
            'transaction_date' => 'required|valid_date',
            'description'      => 'permit_empty|max_length[255]',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $type = $this->request->getPost('type');
        $walletId = (int) $this->request->getPost('wallet_id');
        $categoryId = $this->request->getPost('category_id');
        $amount = floatval($this->request->getPost('amount'));
 
        // Verify wallet belongs to user
        $wallet = $this->walletModel->where('id', $walletId)->where('user_id', $userId)->first();
        if (!$wallet) {
            return redirect()->back()->withInput()->with('error', 'Rekening/Dompet yang dipilih tidak valid.');
        }
 
        // Verify category belongs to user
        if ($type === 'income') {
            $category = $this->incomeCategoryModel->where('id', $categoryId)->where('user_id', $userId)->first();
        } else {
            $category = $this->expenseCategoryModel->where('id', $categoryId)->where('user_id', $userId)->first();
        }
 
        if (!$category) {
            return redirect()->back()->withInput()->with('error', 'Kategori yang dipilih tidak valid.');
        }
 
        // DB Transaction for ACID safety
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            $data = [
                'user_id'          => $userId,
                'wallet_id'        => $walletId,
                'type'             => $type,
                'category_id'      => $categoryId,
                'amount'           => $amount,
                'description'      => esc($this->request->getPost('description')),
                'transaction_date' => $this->request->getPost('transaction_date'),
            ];
            $this->transactionModel->insert($data);
 
            // Adjust wallet balance
            $balanceAdjustment = ($type === 'income') ? $amount : -$amount;
            $this->walletModel->adjustBalance($walletId, $balanceAdjustment);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal mencatat transaksi.');
            }
 
            return redirect()->to('/transaction')->with('message', 'Transaksi berhasil ditambahkan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    public function update(int $id)
    {
        $userId = auth()->id();
        $transaction = $this->transactionModel->where('id', $id)->where('user_id', $userId)->first();

        if (!$transaction) {
            return redirect()->to('/transaction')->with('error', 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // We only support editing income or expense here (transfers are handled differently)
        if ($transaction['type'] === 'transfer') {
            return redirect()->to('/transaction')->with('error', 'Transaksi transfer tidak dapat diedit langsung. Silakan hapus dan buat ulang.');
        }

        $rules = [
            'type'             => 'required|in_list[income,expense]',
            'wallet_id'        => 'required|numeric',
            'category_id'      => 'required|numeric',
            'amount'           => 'required|numeric|greater_than[0]',
            'transaction_date' => 'required|valid_date',
            'description'      => 'permit_empty|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newType = $this->request->getPost('type');
        $newWalletId = (int) $this->request->getPost('wallet_id');
        $newCategoryId = $this->request->getPost('category_id');
        $newAmount = floatval($this->request->getPost('amount'));
        
        // Verify wallet
        $newWallet = $this->walletModel->where('id', $newWalletId)->where('user_id', $userId)->first();
        if (!$newWallet) {
            return redirect()->back()->withInput()->with('error', 'Rekening/Dompet yang dipilih tidak valid.');
        }

        // Verify category
        if ($newType === 'income') {
            $category = $this->incomeCategoryModel->where('id', $newCategoryId)->where('user_id', $userId)->first();
        } else {
            $category = $this->expenseCategoryModel->where('id', $newCategoryId)->where('user_id', $userId)->first();
        }

        if (!$category) {
            return redirect()->back()->withInput()->with('error', 'Kategori yang dipilih tidak valid.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            $oldAmount = floatval($transaction['amount']);
            $oldType = $transaction['type'];
            $oldWalletId = $transaction['wallet_id'];

            // 1. Reverse the old transaction's impact
            if ($oldType === 'income') {
                $this->walletModel->adjustBalance($oldWalletId, -$oldAmount);
            } elseif ($oldType === 'expense') {
                $this->walletModel->adjustBalance($oldWalletId, $oldAmount);
            }

            // 2. Apply the new transaction's impact
            $balanceAdjustment = ($newType === 'income') ? $newAmount : -$newAmount;
            $this->walletModel->adjustBalance($newWalletId, $balanceAdjustment);

            // 3. Update the transaction record
            $data = [
                'wallet_id'        => $newWalletId,
                'type'             => $newType,
                'category_id'      => $newCategoryId,
                'amount'           => $newAmount,
                'description'      => esc($this->request->getPost('description')),
                'transaction_date' => $this->request->getPost('transaction_date'),
            ];
            $this->transactionModel->update($id, $data);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal memperbarui transaksi.');
            }

            return redirect()->to('/transaction')->with('message', 'Transaksi berhasil diperbarui.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        $userId = auth()->id();
        $transaction = $this->transactionModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$transaction) {
            return redirect()->to('/transaction')->with('error', 'Transaksi tidak ditemukan atau Anda tidak memiliki akses.');
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            $amount = floatval($transaction['amount']);
            $type = $transaction['type'];
            $walletId = $transaction['wallet_id'];
 
            // Reverse the wallet balance impacts
            if ($type === 'income') {
                if ($walletId) {
                    $this->walletModel->adjustBalance($walletId, -$amount);
                }
            } elseif ($type === 'expense') {
                if ($walletId) {
                    $this->walletModel->adjustBalance($walletId, $amount);
                }
            } elseif ($type === 'transfer') {
                $toWalletId = $transaction['to_wallet_id'];
                if ($walletId) {
                    $this->walletModel->adjustBalance($walletId, $amount); // restore source
                }
                if ($toWalletId) {
                    $this->walletModel->adjustBalance($toWalletId, -$amount); // restore destination
                }
            }
 
            // Delete record
            $this->transactionModel->delete($id);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->to('/transaction')->with('error', 'Gagal menghapus transaksi.');
            }
 
            return redirect()->to('/transaction')->with('message', 'Transaksi berhasil dihapus.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/transaction')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    public function adjustBalance()
    {
        $rules = [
            'wallet_id'      => 'required|numeric',
            'target_balance' => 'required|string',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->with('error', 'Data penyesuaian tidak valid.');
        }
 
        $userId = auth()->id();
        $walletId = (int) $this->request->getPost('wallet_id');
        $targetBalanceRaw = $this->request->getPost('target_balance');
 
        // Verify wallet
        $wallet = $this->walletModel->where('id', $walletId)->where('user_id', $userId)->first();
        if (!$wallet) {
            return redirect()->back()->with('error', 'Rekening/Dompet tidak ditemukan.');
        }
 
        // Clean target balance input
        $isNegative = strpos($targetBalanceRaw, '-') === 0 || strpos($targetBalanceRaw, 'minus') !== false;
        $cleanNumber = preg_replace('/\D/', '', $targetBalanceRaw);
        $targetBalance = floatval($cleanNumber);
        if ($isNegative) {
            $targetBalance = -$targetBalance;
        }
 
        $currentBalance = floatval($wallet['balance']);
        $diff = $targetBalance - $currentBalance;
 
        if ($diff == 0) {
            return redirect()->to('/transaction')->with('message', 'Saldo rekening sudah sesuai dengan target.');
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            if ($diff > 0) {
                // Find or create 'Penyesuaian Saldo' in incomeCategoryModel
                $category = $this->incomeCategoryModel->where('user_id', $userId)->where('name', 'Penyesuaian Saldo')->first();
                if (!$category) {
                    $this->incomeCategoryModel->insert([
                        'user_id' => $userId,
                        'name'    => 'Penyesuaian Saldo'
                    ]);
                    $categoryId = $this->incomeCategoryModel->getInsertID();
                } else {
                    $categoryId = $category['id'];
                }
 
                $this->transactionModel->insert([
                    'user_id'          => $userId,
                    'wallet_id'        => $walletId,
                    'type'             => 'income',
                    'category_id'      => $categoryId,
                    'amount'           => $diff,
                    'description'      => 'Penyesuaian Saldo Rekening ' . $wallet['name'],
                    'transaction_date' => date('Y-m-d')
                ]);
            } else {
                // Find or create 'Penyesuaian Saldo' in expenseCategoryModel
                $category = $this->expenseCategoryModel->where('user_id', $userId)->where('name', 'Penyesuaian Saldo')->first();
                if (!$category) {
                    $this->expenseCategoryModel->insert([
                        'user_id' => $userId,
                        'name'    => 'Penyesuaian Saldo'
                    ]);
                    $categoryId = $this->expenseCategoryModel->getInsertID();
                } else {
                    $categoryId = $category['id'];
                }
 
                $this->transactionModel->insert([
                    'user_id'          => $userId,
                    'wallet_id'        => $walletId,
                    'type'             => 'expense',
                    'category_id'      => $categoryId,
                    'amount'           => abs($diff),
                    'description'      => 'Penyesuaian Saldo Rekening ' . $wallet['name'],
                    'transaction_date' => date('Y-m-d')
                ]);
            }
 
            // Set the wallet balance directly
            $this->walletModel->update($walletId, ['balance' => $targetBalance]);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->to('/transaction')->with('error', 'Gagal menyesuaikan saldo.');
            }
 
            return redirect()->to('/transaction')->with('message', 'Saldo "' . $wallet['name'] . '" berhasil disesuaikan secara otomatis.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/transaction')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    private function seedDefaultCategories(int $userId)
    {
        // Check if user already has income categories
        $incomeCount = $this->incomeCategoryModel->where('user_id', $userId)->countAllResults();
        if ($incomeCount === 0) {
            $defaultIncomes = ['Gaji Pokok', 'Freelance', 'Investasi', 'Lainnya'];
            foreach ($defaultIncomes as $name) {
                $this->incomeCategoryModel->insert([
                    'user_id' => $userId,
                    'name'    => $name
                ]);
            }
        }
 
        // Check if user already has expense categories
        $expenseCount = $this->expenseCategoryModel->where('user_id', $userId)->countAllResults();
        if ($expenseCount === 0) {
            $defaultExpenses = ['Makanan & Minuman', 'Belanja Bulanan', 'Transportasi', 'Utilitas & Tagihan', 'Hiburan', 'Kesehatan', 'Lainnya'];
            foreach ($defaultExpenses as $name) {
                $this->expenseCategoryModel->insert([
                    'user_id' => $userId,
                    'name'    => $name
                ]);
            }
        }
    }
}
