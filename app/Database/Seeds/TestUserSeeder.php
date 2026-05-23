<?php
 
namespace App\Database\Seeds;
 
use CodeIgniter\Database\Seeder;
 
class TestUserSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        
        // 1. Get or Create Test User in Shield
        $users = auth()->getProvider();
        $user = $users->findByCredentials(['email' => 'aanrozanahs1984@gmail.com']);
        
        if (!$user) {
            $user = new \CodeIgniter\Shield\Entities\User([
                'username' => 'aanrozanah',
                'email'    => 'aanrozanahs1984@gmail.com',
                'password' => 'aanrozanah1984', // memorable test password
            ]);
            $users->save($user);
            $user = $users->findByCredentials(['email' => 'aanrozanahs1984@gmail.com']);
        }
        
        $userId = $user->id;
 
        // 2. Force activate the user in Database
        $db->table('users')->where('id', $userId)->update(['active' => 1]);
 
        // 3. Purge existing test data for this specific user to ensure repeatable seeds
        $db->table('savings_transactions')->where('user_id', $userId)->delete();
        $db->table('savings_goals')->where('user_id', $userId)->delete();
        $db->table('recurring_transactions')->where('user_id', $userId)->delete();
        $db->table('budgets')->where('user_id', $userId)->delete();
        $db->table('transactions')->where('user_id', $userId)->delete();
        $db->table('debts')->where('user_id', $userId)->delete();
        $db->table('receivables')->where('user_id', $userId)->delete();
        $db->table('expense_categories')->where('user_id', $userId)->delete();
        $db->table('income_categories')->where('user_id', $userId)->delete();
        $db->table('wallets')->where('user_id', $userId)->delete();
 
        // 4. Seed Wallets
        $walletModel = new \App\Models\WalletModel();
        
        $walletModel->insert([
            'user_id' => $userId,
            'name'    => 'Dompet Utama',
            'type'    => 'cash',
            'balance' => 5000000.00
        ]);
        $wDompetId = $walletModel->getInsertID();
 
        $walletModel->insert([
            'user_id' => $userId,
            'name'    => 'Tabungan Bank Mandiri',
            'type'    => 'bank',
            'balance' => 15000000.00
        ]);
        $wMandiriId = $walletModel->getInsertID();
 
        $walletModel->insert([
            'user_id' => $userId,
            'name'    => 'GoPay & OVO E-Wallet',
            'type'    => 'ewallet',
            'balance' => 750000.00
        ]);
        $wEwalletId = $walletModel->getInsertID();
 
        // 5. Seed Income Categories
        $incomeCatModel = new \App\Models\IncomeCategoryModel();
        
        $incomeCatModel->insert(['user_id' => $userId, 'name' => 'Gaji Bulanan']);
        $cGajiId = $incomeCatModel->getInsertID();
        
        $incomeCatModel->insert(['user_id' => $userId, 'name' => 'Freelance']);
        $cFreelanceId = $incomeCatModel->getInsertID();
 
        $incomeCatModel->insert(['user_id' => $userId, 'name' => 'Dividen & Investasi']);
        $cDividenId = $incomeCatModel->getInsertID();
 
        // 6. Seed Expense Categories
        $expenseCatModel = new \App\Models\ExpenseCategoryModel();
 
        $expenseCatModel->insert(['user_id' => $userId, 'name' => 'Makanan & Minuman']);
        $cMakananId = $expenseCatModel->getInsertID();
 
        $expenseCatModel->insert(['user_id' => $userId, 'name' => 'Transportasi']);
        $cTransId = $expenseCatModel->getInsertID();
 
        $expenseCatModel->insert(['user_id' => $userId, 'name' => 'Belanja Bulanan']);
        $cBelanjaId = $expenseCatModel->getInsertID();
 
        $expenseCatModel->insert(['user_id' => $userId, 'name' => 'Tagihan & Listrik']);
        $cTagihanId = $expenseCatModel->getInsertID();
 
        $expenseCatModel->insert(['user_id' => $userId, 'name' => 'Hiburan']);
        $cHiburanId = $expenseCatModel->getInsertID();
 
        // 7. Seed Transactions
        $transactionModel = new \App\Models\TransactionModel();
 
        // Gaji Bulanan Income
        $transactionModel->insert([
            'user_id'          => $userId,
            'wallet_id'        => $wDompetId,
            'type'             => 'income',
            'category_id'      => $cGajiId,
            'amount'           => 8500000.00,
            'description'      => 'Gaji bulanan kantor utama',
            'transaction_date' => date('Y-m-d', strtotime('-5 days'))
        ]);
 
        // Freelance Income
        $transactionModel->insert([
            'user_id'          => $userId,
            'wallet_id'        => $wMandiriId,
            'type'             => 'income',
            'category_id'      => $cFreelanceId,
            'amount'           => 1500000.00,
            'description'      => 'Proyek pembuatan landing page premium',
            'transaction_date' => date('Y-m-d', strtotime('-2 days'))
        ]);
 
        // Belanja Bulanan Expense
        $transactionModel->insert([
            'user_id'          => $userId,
            'wallet_id'        => $wDompetId,
            'type'             => 'expense',
            'category_id'      => $cBelanjaId,
            'amount'           => 1200000.00,
            'description'      => 'Belanja sembako bulanan di Superindo',
            'transaction_date' => date('Y-m-d', strtotime('-4 days'))
        ]);
 
        // Listrik Expense
        $transactionModel->insert([
            'user_id'          => $userId,
            'wallet_id'        => $wEwalletId,
            'type'             => 'expense',
            'category_id'      => $cTagihanId,
            'amount'           => 350000.00,
            'description'      => 'Token listrik rumah bulanan',
            'transaction_date' => date('Y-m-d', strtotime('-3 days'))
        ]);
 
        // Makanan Expense
        $transactionModel->insert([
            'user_id'          => $userId,
            'wallet_id'        => $wDompetId,
            'type'             => 'expense',
            'category_id'      => $cMakananId,
            'amount'           => 85000.00,
            'description'      => 'Makan malam Bakso Mas Joko',
            'transaction_date' => date('Y-m-d', strtotime('-1 days'))
        ]);
 
        // 8. Seed Budgets
        $budgetModel = new \App\Models\BudgetModel();
 
        $budgetModel->insert([
            'user_id'      => $userId,
            'category_id'  => $cBelanjaId,
            'limit_amount' => 2000000.00
        ]);
 
        $budgetModel->insert([
            'user_id'      => $userId,
            'category_id'  => $cMakananId,
            'limit_amount' => 1500000.00
        ]);
 
        // 9. Seed Recurring Transactions
        $recurringModel = new \App\Models\RecurringTransactionModel();
 
        $recurringModel->insert([
            'user_id'     => $userId,
            'type'        => 'expense',
            'category_id' => $cBelanjaId,
            'amount'      => 1500000.00,
            'description' => 'Bayar Kos Bulanan Mandiri',
            'frequency'   => 'monthly',
            'start_date'  => date('Y-m-d'),
            'is_active'   => 1
        ]);
 
        $recurringModel->insert([
            'user_id'     => $userId,
            'type'        => 'expense',
            'category_id' => $cHiburanId,
            'amount'      => 186000.00,
            'description' => 'Langganan Netflix Premium',
            'frequency'   => 'monthly',
            'start_date'  => date('Y-m-d'),
            'is_active'   => 1
        ]);
 
        // 10. Seed Debts & Receivables
        $debtModel = new \App\Models\DebtModel();
        $debtModel->insert([
            'user_id'       => $userId,
            'creditor_name' => 'Bank Mandiri',
            'total_amount'  => 10000000.00,
            'description'   => 'Pinjaman Modal UMKM Kredit Usaha Rakyat',
            'due_date'      => date('Y-m-d', strtotime('+3 months')),
            'status'        => 'unpaid'
        ]);
 
        $receivableModel = new \App\Models\ReceivableModel();
        $receivableModel->insert([
            'user_id'       => $userId,
            'borrower_name' => 'Budi Santoso',
            'total_amount'  => 1500000.00,
            'description'   => 'Budi pinjam uang untuk servis motor',
            'due_date'      => date('Y-m-d', strtotime('+2 weeks')),
            'status'        => 'unpaid'
        ]);
 
        // 11. Seed Savings Goals (Savings Goals Planner)
        $savingsGoalModel = new \App\Models\SavingsGoalModel();
 
        $savingsGoalModel->insert([
            'user_id'        => $userId,
            'name'           => 'Dana Darurat (Emergency Fund)',
            'target_amount'  => 15000000.00,
            'current_amount' => 4500000.00,
            'target_date'    => date('Y-m-d', strtotime('+1 year'))
        ]);
        $gDanaDaruratId = $savingsGoalModel->getInsertID();
 
        $savingsGoalModel->insert([
            'user_id'        => $userId,
            'name'           => 'Beli Laptop Asus ROG Baru',
            'target_amount'  => 8000000.00,
            'current_amount' => 2000000.00,
            'target_date'    => date('Y-m-d', strtotime('+6 months'))
        ]);
        $gLaptopId = $savingsGoalModel->getInsertID();
 
        // 12. Seed Savings Transactions
        $savingsTxModel = new \App\Models\SavingsTransactionModel();
 
        // Goal 1: Dana Darurat Setor Rp3.000.000
        $savingsTxModel->insert([
            'user_id'         => $userId,
            'savings_goal_id' => $gDanaDaruratId,
            'wallet_id'       => $wMandiriId,
            'type'            => 'add',
            'amount'          => 3000000.00,
            'notes'           => 'Alokasi sisa gajian bulan lalu'
        ]);
 
        // Goal 1: Dana Darurat Setor Rp1.500.000
        $savingsTxModel->insert([
            'user_id'         => $userId,
            'savings_goal_id' => $gDanaDaruratId,
            'wallet_id'       => $wDompetId,
            'type'            => 'add',
            'amount'          => 1500000.00,
            'notes'           => 'Setoran cash sisa belanjaan'
        ]);
 
        // Goal 2: Beli Laptop Setor Rp2.000.000
        $savingsTxModel->insert([
            'user_id'         => $userId,
            'savings_goal_id' => $gLaptopId,
            'wallet_id'       => $wMandiriId,
            'type'            => 'add',
            'amount'          => 2000000.00,
            'notes'           => 'Alokasi awal bonus proyek freelance'
        ]);
    }
}
