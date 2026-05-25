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

        // 2. Fetch all categories & wallets for dropdown selects
        $incomeCategories = $this->incomeCategoryModel->where('user_id', $userId)->findAll();
        $expenseCategories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();

        // 3. Calculate initial summary totals (SQL aggregates — no full row loading)
        $summary = $this->transactionModel->getSummaryTotals([], $userId);
        $totalIncome = $summary['total_income'];
        $totalExpense = $summary['total_expense'];
        $netBalance = $totalIncome - $totalExpense;

        // Render view (table data loaded via DataTables AJAX)
        return view('user/transactions/index', [
            'title'             => 'Kelola Transaksi',
            'incomeCategories'  => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'wallets'           => $wallets,
            'totalIncome'       => $totalIncome,
            'totalExpense'      => $totalExpense,
            'netBalance'        => $netBalance,
        ]);
    }

    public function getTransactionsData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Akses ditolak']);
        }

        $userId = auth()->id();
        $postData = $this->request->getPost();

        // Pre-load wallet & category maps for display enrichment
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
        $incomeCategories = $this->incomeCategoryModel->where('user_id', $userId)->findAll();
        $expenseCategories = $this->expenseCategoryModel->where('user_id', $userId)->findAll();

        $walletsCache = [];
        foreach ($wallets as $w) {
            $walletsCache[$w['id']] = $w;
        }
        $incomeCatMap = [];
        foreach ($incomeCategories as $c) {
            $incomeCatMap[$c['id']] = $c['name'];
        }
        $expenseCatMap = [];
        foreach ($expenseCategories as $c) {
            $expenseCatMap[$c['id']] = $c['name'];
        }

        // Fetch paginated data
        $list = $this->transactionModel->getDatatables($postData, $userId);

        // Build HTML rows
        $data = [];
        foreach ($list as $tx) {
            $walletName = $walletsCache[$tx['wallet_id']]['name'] ?? 'Dompet Dihapus';
            $toWalletName = $walletsCache[$tx['to_wallet_id']]['name'] ?? '';

            if ($tx['type'] === 'income') {
                $categoryName = $incomeCatMap[$tx['category_id']] ?? 'Kategori Dihapus';
            } elseif ($tx['type'] === 'expense') {
                $categoryName = $expenseCatMap[$tx['category_id']] ?? 'Kategori Dihapus';
            } else {
                $categoryName = 'Transfer Saldo';
            }

            $desc = $tx['description'] ?: '-';
            $descShort = mb_strlen($desc) > 100 ? mb_substr($desc, 0, 100) . '...' : $desc;

            $row = [];

            // Col 0: Tanggal
            $row[] = '<div class="font-medium text-tx-secondary whitespace-nowrap">' . date('d M Y', strtotime($tx['transaction_date'])) . '</div>';

            // Col 1: Tipe
            if ($tx['type'] === 'income') {
                $row[] = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-500/10 text-emerald-400">Pemasukan</span>';
            } elseif ($tx['type'] === 'expense') {
                $row[] = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-500/10 text-rose-400">Pengeluaran</span>';
            } else {
                $row[] = '<span class="px-2.5 py-1 text-xs font-bold rounded-full bg-indigo-500/10 text-indigo-400">Transfer</span>';
            }

            // Col 2: Rekening
            if ($tx['type'] === 'transfer') {
                $row[] = '<span class="text-xs font-semibold text-tx-primary bg-elevated border border-br-default px-2.5 py-1 rounded-lg">'
                    . esc($walletName) . ' &#10132; ' . esc($toWalletName) . '</span>';
            } else {
                $row[] = '<span class="text-xs font-bold text-brand bg-brand/10 border border-brand/20 px-2.5 py-1 rounded-full">'
                    . esc($walletName) . '</span>';
            }

            // Col 3: Kategori
            if ($tx['type'] === 'transfer') {
                $row[] = '<span class="text-brand font-bold text-xs">Transfer Saldo</span>';
            } else {
                $row[] = esc($categoryName);
            }

            // Col 4: Deskripsi
            $row[] = '<div class="text-tx-secondary max-w-xs truncate" title="' . esc($desc) . '">' . esc($descShort) . '</div>';

            // Col 5: Jumlah
            $amountFormatted = 'Rp' . number_format($tx['amount'], 0, ',', '.');
            if ($tx['type'] === 'income') {
                $row[] = '<span class="text-success font-bold whitespace-nowrap">+ ' . $amountFormatted . '</span>';
            } elseif ($tx['type'] === 'expense') {
                $row[] = '<span class="text-danger font-bold whitespace-nowrap">- ' . $amountFormatted . '</span>';
            } else {
                $row[] = '<span class="text-tx-secondary font-bold whitespace-nowrap">' . $amountFormatted . '</span>';
            }

            // Col 6: Aksi
            $editBtn = '';
            if ($tx['type'] !== 'transfer') {
                $escapedDesc = esc((string)$tx['description'], 'attr');
                $editBtn = '<button type="button" class="edit-transaction-btn p-2 text-brand/70 hover:text-brand bg-brand/5 hover:bg-brand/15 border border-brand/10 hover:border-brand/20 rounded-lg transition-all" title="Edit Transaksi"'
                    . ' data-id="' . (int)$tx['id'] . '"'
                    . ' data-type="' . (string)$tx['type'] . '"'
                    . ' data-date="' . date('Y-m-d', strtotime((string)$tx['transaction_date'])) . '"'
                    . ' data-wallet="' . (int)$tx['wallet_id'] . '"'
                    . ' data-category="' . (int)$tx['category_id'] . '"'
                    . ' data-amount="' . floatval($tx['amount']) . '"'
                    . ' data-description="' . $escapedDesc . '">'
                    . '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">'
                    . '<path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />'
                    . '</svg></button>';
            }

            $deleteForm = '<form action="' . base_url('transaction/delete/' . $tx['id']) . '" method="post" class="inline">'
                . csrf_field()
                . '<button type="button" class="delete-transaction-btn p-2 text-danger/70 hover:text-danger bg-danger/5 hover:bg-danger/15 border border-danger/10 hover:border-danger/20 rounded-lg transition-all" title="Hapus Transaksi">'
                . '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">'
                . '<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />'
                . '</svg></button></form>';

            $row[] = '<div class="flex justify-center gap-2">' . $editBtn . $deleteForm . '</div>';
            $data[] = $row;
        }

        // Also fetch summary totals for the filtered set
        $summary = $this->transactionModel->getSummaryTotals($postData, $userId);

        return $this->response->setJSON([
            'draw'            => (int) ($postData['draw'] ?? 1),
            'recordsTotal'    => $this->transactionModel->countAll($userId),
            'recordsFiltered' => $this->transactionModel->countFiltered($postData, $userId),
            'data'            => $data,
            'totalIncome'     => $summary['total_income'],
            'totalExpense'    => $summary['total_expense'],
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
