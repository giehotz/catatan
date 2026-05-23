<?php
 
namespace App\Controllers;
 
use App\Models\WalletModel;
use App\Models\TransactionModel;
use App\Models\IncomeCategoryModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
 
class Wallet extends BaseController
{
    protected WalletModel $walletModel;
    protected TransactionModel $transactionModel;
    protected IncomeCategoryModel $incomeCategoryModel;
 
    public function __construct()
    {
        $this->walletModel = new WalletModel();
        $this->transactionModel = new TransactionModel();
        $this->incomeCategoryModel = new IncomeCategoryModel();
    }
 
    public function index()
    {
        $userId = auth()->id();
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
 
        $totalAssets = 0.0;
        foreach ($wallets as $wallet) {
            $totalAssets += floatval($wallet['balance']);
        }
 
        return view('user/wallets/index', [
            'title'       => 'Rekening & Dompet',
            'wallets'     => $wallets,
            'totalAssets' => $totalAssets,
        ]);
    }
 
    public function create()
    {
        $rules = [
            'name'             => 'required|min_length[2]|max_length[100]',
            'type'             => 'required|in_list[cash,bank,e-wallet,investment,other]',
            'starting_balance' => 'permit_empty|string',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $name = (string) esc($this->request->getPost('name'));
        $type = $this->request->getPost('type');
        
        $startingBalanceRaw = $this->request->getPost('starting_balance');
        $startingBalance = 0.0;
        if (!empty($startingBalanceRaw)) {
            $startingBalance = floatval(preg_replace('/\D/', '', $startingBalanceRaw));
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            // Insert wallet
            $walletData = [
                'user_id' => $userId,
                'name'    => $name,
                'type'    => $type,
                'balance' => 0.00, // initialized to 0, will be adjusted by starting balance transaction
            ];
            $this->walletModel->insert($walletData);
            $walletId = $this->walletModel->getInsertID();
 
            // If starting balance is > 0, record an initial income transaction
            if ($startingBalance > 0) {
                // Find or create 'Saldo Awal' income category
                $category = $this->incomeCategoryModel
                    ->where('user_id', $userId)
                    ->where('name', 'Saldo Awal')
                    ->first();
 
                if (!$category) {
                    $this->incomeCategoryModel->insert([
                        'user_id' => $userId,
                        'name'    => 'Saldo Awal'
                    ]);
                    $categoryId = $this->incomeCategoryModel->getInsertID();
                } else {
                    $categoryId = $category['id'];
                }
 
                // Insert transaction
                $this->transactionModel->insert([
                    'user_id'          => $userId,
                    'wallet_id'        => $walletId,
                    'type'             => 'income',
                    'category_id'      => $categoryId,
                    'amount'           => $startingBalance,
                    'description'      => 'Saldo Awal Dompet ' . $name,
                    'transaction_date' => date('Y-m-d'),
                ]);
 
                // Update wallet balance
                $this->walletModel->update($walletId, ['balance' => $startingBalance]);
            }
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal membuat rekening baru.');
            }
 
            return redirect()->to('/wallets')->with('message', 'Rekening baru "' . $name . '" berhasil ditambahkan.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    public function update(int $id)
    {
        $userId = auth()->id();
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$wallet) {
            return redirect()->to('/wallets')->with('error', 'Rekening tidak ditemukan atau Anda tidak memiliki akses.');
        }
 
        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'type' => 'required|in_list[cash,bank,e-wallet,investment,other]',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $this->walletModel->update($id, [
            'name' => (string) esc($this->request->getPost('name')),
            'type' => $this->request->getPost('type'),
        ]);
 
        return redirect()->to('/wallets')->with('message', 'Informasi rekening berhasil diperbarui.');
    }
 
    public function delete(int $id)
    {
        $userId = auth()->id();
        $wallet = $this->walletModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$wallet) {
            return redirect()->to('/wallets')->with('error', 'Rekening tidak ditemukan atau Anda tidak memiliki akses.');
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            // Delete all transactions associated with this wallet (both wallet_id and to_wallet_id)
            $this->transactionModel->where('wallet_id', $id)->delete();
            $this->transactionModel->where('to_wallet_id', $id)->delete();
            
            // Delete the wallet
            $this->walletModel->delete($id);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->to('/wallets')->with('error', 'Gagal menghapus rekening.');
            }
 
            return redirect()->to('/wallets')->with('message', 'Rekening "' . $wallet['name'] . '" beserta seluruh riwayat transaksinya berhasil dihapus.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/wallets')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    public function transferIndex()
    {
        $userId = auth()->id();
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
 
        return view('user/wallets/transfer', [
            'title'   => 'Transfer Saldo',
            'wallets' => $wallets,
        ]);
    }
 
    public function processTransfer()
    {
        $rules = [
            'from_wallet_id'   => 'required|numeric',
            'to_wallet_id'     => 'required|numeric',
            'amount'           => 'required|string',
            'transaction_date' => 'required|valid_date',
            'description'      => 'permit_empty|max_length[255]',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $fromWalletId = (int) $this->request->getPost('from_wallet_id');
        $toWalletId = (int) $this->request->getPost('to_wallet_id');
        
        $amountRaw = $this->request->getPost('amount');
        $amount = floatval(preg_replace('/\D/', '', $amountRaw));
 
        if ($amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal transfer harus lebih besar dari Rp0.');
        }
 
        if ($fromWalletId === $toWalletId) {
            return redirect()->back()->withInput()->with('error', 'Rekening asal dan tujuan tidak boleh sama.');
        }
 
        // Verify ownership and fetch wallets
        $fromWallet = $this->walletModel->where('id', $fromWalletId)->where('user_id', $userId)->first();
        $toWallet = $this->walletModel->where('id', $toWalletId)->where('user_id', $userId)->first();
 
        if (!$fromWallet || !$toWallet) {
            return redirect()->back()->withInput()->with('error', 'Rekening asal atau tujuan tidak valid.');
        }
 
        // Verify balance
        if (floatval($fromWallet['balance']) < $amount) {
            return redirect()->back()->withInput()->with('error', 'Saldo "' . $fromWallet['name'] . '" tidak mencukupi. (Saldo saat ini: Rp' . number_format($fromWallet['balance'], 0, ',', '.') . ')');
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            // Insert transfer transaction record
            $description = (string) esc($this->request->getPost('description')) ?: 'Transfer Saldo';
            $this->transactionModel->insert([
                'user_id'          => $userId,
                'wallet_id'        => $fromWalletId,
                'to_wallet_id'     => $toWalletId,
                'type'             => 'transfer',
                'category_id'      => null, // transfers don't have a category
                'amount'           => $amount,
                'description'      => $description,
                'transaction_date' => $this->request->getPost('transaction_date'),
            ]);
 
            // Update wallet balances
            $this->walletModel->adjustBalance($fromWalletId, -$amount);
            $this->walletModel->adjustBalance($toWalletId, $amount);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal memproses transfer saldo.');
            }
 
            return redirect()->to('/wallets')->with('message', 'Transfer saldo sebesar Rp' . number_format($amount, 0, ',', '.') . ' dari "' . $fromWallet['name'] . '" ke "' . $toWallet['name'] . '" berhasil.');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
