<?php
 
namespace App\Controllers;
 
use App\Models\SavingsGoalModel;
use App\Models\SavingsTransactionModel;
use App\Models\WalletModel;
use App\Models\TransactionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
 
class Savings extends BaseController
{
    protected SavingsGoalModel $savingsGoalModel;
    protected SavingsTransactionModel $savingsTransactionModel;
    protected WalletModel $walletModel;
    protected TransactionModel $transactionModel;
 
    public function __construct()
    {
        $this->savingsGoalModel = new SavingsGoalModel();
        $this->savingsTransactionModel = new SavingsTransactionModel();
        $this->walletModel = new WalletModel();
        $this->transactionModel = new TransactionModel();
    }
 
    public function index()
    {
        $userId = auth()->id();
        $goals = $this->savingsGoalModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
        $wallets = $this->walletModel->where('user_id', $userId)->orderBy('id', 'ASC')->findAll();
 
        $totalSavings = 0.0;
        foreach ($goals as &$goal) {
            $totalSavings += floatval($goal['current_amount']);
            
            // Percentage Calculation
            $target = floatval($goal['target_amount']);
            $current = floatval($goal['current_amount']);
            $goal['percent'] = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
            
            // ETA and Planner Logic
            $goal['eta_message'] = 'Belum ada rencana waktu.';
            $goal['eta_type'] = 'neutral'; // neutral, success, warning, danger
            
            $remaining = max(0.0, $target - $current);
            
            if ($remaining <= 0) {
                $goal['eta_message'] = 'Target Impian Tercapai!';
                $goal['eta_type'] = 'success';
            } else {
                $today = new \DateTime();
                
                // 1. Calculate Required Saving Rate if Target Date is set
                if (!empty($goal['target_date'])) {
                    $targetDate = new \DateTime($goal['target_date']);
                    $interval = $today->diff($targetDate);
                    
                    // Approximate months left
                    $monthsRemaining = ($interval->y * 12) + $interval->m + ($interval->d / 30);
                    $daysRemaining = $interval->days;
                    
                    if ($targetDate < $today) {
                        $goal['eta_message'] = 'Target tanggal selesai telah terlewati.';
                        $goal['eta_type'] = 'danger';
                    } elseif ($monthsRemaining > 0) {
                        $requiredMonthly = $remaining / $monthsRemaining;
                        $goal['eta_message'] = 'Diperlukan Rp' . number_format($requiredMonthly, 0, ',', '.') . ' / bulan (' . $daysRemaining . ' hari lagi).';
                        $goal['eta_type'] = 'warning';
                    }
                } else {
                    // 2. Estimate ETA based on Goal Age and saving rate
                    $created = new \DateTime($goal['created_at']);
                    $monthsActive = ($today->diff($created)->y * 12) + $today->diff($created)->m + ($today->diff($created)->d / 30);
                    $monthsActive = max(0.5, $monthsActive); // Minimum half a month to avoid division inflation
                    
                    $averageMonthly = $current / $monthsActive;
                    
                    if ($averageMonthly > 1000) {
                        $monthsLeft = $remaining / $averageMonthly;
                        if ($monthsLeft <= 1) {
                            $goal['eta_message'] = 'Estimasi tercapai: Kurang dari 1 bulan (rata-rata Rp' . number_format($averageMonthly, 0, ',', '.') . '/bln).';
                        } else {
                            $goal['eta_message'] = 'Estimasi tercapai: ~' . round($monthsLeft, 1) . ' bulan lagi (rata-rata Rp' . number_format($averageMonthly, 0, ',', '.') . '/bln).';
                        }
                        $goal['eta_type'] = 'info';
                    } else {
                        $goal['eta_message'] = 'Isi tabungan Anda untuk menghitung estimasi ETA.';
                        $goal['eta_type'] = 'neutral';
                    }
                }
            }
        }
        unset($goal); // Break reference
 
        // Get transaction history for each savings goal
        $history = $this->savingsTransactionModel
            ->select('savings_transactions.*, wallets.name as wallet_name')
            ->join('wallets', 'wallets.id = savings_transactions.wallet_id')
            ->where('savings_transactions.user_id', $userId)
            ->orderBy('savings_transactions.id', 'DESC')
            ->findAll();
 
        $historyByGoal = [];
        foreach ($history as $tx) {
            $historyByGoal[$tx['savings_goal_id']][] = $tx;
        }
 
        return view('user/savings/index', [
            'title'         => 'Savings Planner',
            'goals'         => $goals,
            'wallets'       => $wallets,
            'totalSavings'  => $totalSavings,
            'historyByGoal' => $historyByGoal,
        ]);
    }
 
    public function create()
    {
        $rules = [
            'name'          => 'required|min_length[2]|max_length[100]',
            'target_amount' => 'required|string',
            'target_date'   => 'permit_empty|valid_date',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $name = (string) esc($this->request->getPost('name'));
        
        $targetAmountRaw = $this->request->getPost('target_amount');
        $targetAmount = floatval(preg_replace('/\D/', '', $targetAmountRaw));
 
        if ($targetAmount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Target nominal tabungan harus lebih besar dari Rp0.');
        }
 
        $targetDate = $this->request->getPost('target_date') ?: null;
        if ($targetDate && strtotime($targetDate) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Target tanggal selesai tidak boleh di masa lalu.');
        }
 
        $this->savingsGoalModel->insert([
            'user_id'       => $userId,
            'name'          => $name,
            'target_amount' => $targetAmount,
            'current_amount'=> 0.00,
            'target_date'   => $targetDate,
        ]);
 
        return redirect()->to('/savings')->with('message', 'Target tabungan "' . $name . '" berhasil dibuat. Mulai menyisihkan dana sekarang!');
    }
 
    public function update(int $id)
    {
        $userId = auth()->id();
        $goal = $this->savingsGoalModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$goal) {
            return redirect()->to('/savings')->with('error', 'Target tidak ditemukan.');
        }
 
        $rules = [
            'name'          => 'required|min_length[2]|max_length[100]',
            'target_amount' => 'required|string',
            'target_date'   => 'permit_empty|valid_date',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $name = (string) esc($this->request->getPost('name'));
        $targetAmountRaw = $this->request->getPost('target_amount');
        $targetAmount = floatval(preg_replace('/\D/', '', $targetAmountRaw));
 
        if ($targetAmount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Target nominal tabungan harus lebih besar dari Rp0.');
        }
 
        $targetDate = $this->request->getPost('target_date') ?: null;
        if ($targetDate && strtotime($targetDate) < strtotime(date('Y-m-d'))) {
            return redirect()->back()->withInput()->with('error', 'Target tanggal selesai tidak boleh di masa lalu.');
        }
 
        $this->savingsGoalModel->update($id, [
            'name'          => $name,
            'target_amount' => $targetAmount,
            'target_date'   => $targetDate,
        ]);
 
        return redirect()->to('/savings')->with('message', 'Target tabungan berhasil diperbarui.');
    }
 
    public function delete(int $id)
    {
        $userId = auth()->id();
        $goal = $this->savingsGoalModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$goal) {
            return redirect()->to('/savings')->with('error', 'Target tidak ditemukan.');
        }
 
        $currentAmount = floatval($goal['current_amount']);
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            $refundMessage = '';
            // Safety Refund Protocol
            if ($currentAmount > 0) {
                // Find primary or first available wallet for user
                $wallet = $this->walletModel->where('user_id', $userId)->where('name', 'Dompet Utama')->first()
                        ?: $this->walletModel->where('user_id', $userId)->first();
 
                if (!$wallet) {
                    throw new \Exception('Refund gagal karena dompet utama tidak ditemukan. Hubungi admin.');
                }
 
                // 1. Refund wallet balance
                $this->walletModel->adjustBalance($wallet['id'], $currentAmount);
 
                // 2. Log in main ledger for audit transparency
                $this->transactionModel->insert([
                    'user_id'          => $userId,
                    'wallet_id'        => $wallet['id'],
                    'type'             => 'income',
                    'category_id'      => null, // audit adjustment
                    'amount'           => $currentAmount,
                    'description'      => 'Pengembalian Dana Target Tabungan: ' . $goal['name'],
                    'transaction_date' => date('Y-m-d'),
                ]);
 
                $refundMessage = ' Sisa saldo sebesar Rp' . number_format($currentAmount, 0, ',', '.') . ' telah dikembalikan secara aman ke "' . $wallet['name'] . '".';
            }
 
            // Delete target (cascade triggers savings_transactions deletion)
            $this->savingsGoalModel->delete($id);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->to('/savings')->with('error', 'Gagal menghapus target tabungan.');
            }
 
            return redirect()->to('/savings')->with('message', 'Target tabungan "' . $goal['name'] . '" berhasil dihapus.' . $refundMessage);
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->to('/savings')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
 
    public function allocate(int $id)
    {
        $rules = [
            'wallet_id' => 'required|numeric',
            'type'      => 'required|in_list[add,withdraw]',
            'amount'    => 'required|string',
            'notes'     => 'permit_empty|max_length[255]',
        ];
 
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
 
        $userId = auth()->id();
        $goal = $this->savingsGoalModel->where('id', $id)->where('user_id', $userId)->first();
 
        if (!$goal) {
            return redirect()->to('/savings')->with('error', 'Target tabungan tidak ditemukan.');
        }
 
        $walletId = (int) $this->request->getPost('wallet_id');
        $type = $this->request->getPost('type');
        $notes = (string) esc($this->request->getPost('notes')) ?: null;
        
        $amountRaw = $this->request->getPost('amount');
        $amount = floatval(preg_replace('/\D/', '', $amountRaw));
 
        if ($amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Nominal alokasi harus lebih besar dari Rp0.');
        }
 
        // Verify wallet ownership
        $wallet = $this->walletModel->where('id', $walletId)->where('user_id', $userId)->first();
        if (!$wallet) {
            return redirect()->back()->withInput()->with('error', 'Rekening/Dompet tidak valid.');
        }
 
        $db = \Config\Database::connect();
        $db->transStart();
 
        try {
            if ($type === 'add') {
                // Verify wallet has enough funds to save
                if (floatval($wallet['balance']) < $amount) {
                    throw new \Exception('Saldo "' . $wallet['name'] . '" tidak mencukupi untuk dialokasikan ke tabungan. (Saldo saat ini: Rp' . number_format($wallet['balance'], 0, ',', '.') . ')');
                }
 
                // 1. Decrease wallet balance
                $this->walletModel->adjustBalance($walletId, -$amount);
 
                // 2. Increase goal current amount
                $newCurrent = floatval($goal['current_amount']) + $amount;
                $this->savingsGoalModel->update($id, ['current_amount' => $newCurrent]);
            } else {
                // Verify goal has enough accumulated balance to withdraw
                if (floatval($goal['current_amount']) < $amount) {
                    throw new \Exception('Saldo pada target tabungan "' . $goal['name'] . '" tidak mencukupi untuk ditarik. (Saldo tabungan saat ini: Rp' . number_format($goal['current_amount'], 0, ',', '.') . ')');
                }
 
                // 1. Increase wallet balance
                $this->walletModel->adjustBalance($walletId, $amount);
 
                // 2. Decrease goal current amount
                $newCurrent = floatval($goal['current_amount']) - $amount;
                $this->savingsGoalModel->update($id, ['current_amount' => $newCurrent]);
            }
 
            // 3. Log savings transaction
            $this->savingsTransactionModel->insert([
                'user_id'         => $userId,
                'savings_goal_id' => $id,
                'wallet_id'       => $walletId,
                'type'            => $type,
                'amount'          => $amount,
                'notes'           => $notes ?: ($type === 'add' ? 'Setoran Tabungan' : 'Penarikan Tabungan'),
            ]);
 
            $db->transComplete();
 
            if ($db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Gagal memproses alokasi tabungan.');
            }
 
            $msg = $type === 'add'
                ? 'Berhasil menyisihkan Rp' . number_format($amount, 0, ',', '.') . ' dari "' . $wallet['name'] . '" ke target "' . $goal['name'] . '".'
                : 'Berhasil menarik Rp' . number_format($amount, 0, ',', '.') . ' dari target "' . $goal['name'] . '" ke "' . $wallet['name'] . '".';
 
            return redirect()->to('/savings')->with('message', $msg);
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
