<?php
 
namespace App\Models;
 
use CodeIgniter\Model;
 
class WalletModel extends Model
{
    protected $table            = 'wallets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'name',
        'type',
        'balance',
    ];
 
    // Timestamps
    protected $useTimestamps = false;
 
    /**
     * Adjust the balance of a wallet by adding or subtracting an amount.
     *
     * @param int $walletId
     * @param float $amount
     * @return bool
     */
    public function adjustBalance(int $walletId, float $amount): bool
    {
        $wallet = $this->find($walletId);
        if (!$wallet) {
            return false;
        }
 
        $newBalance = floatval($wallet['balance']) + floatval($amount);
        return $this->update($walletId, ['balance' => $newBalance]);
    }
}
