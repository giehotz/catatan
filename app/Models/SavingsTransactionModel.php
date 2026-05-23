<?php
 
namespace App\Models;
 
use CodeIgniter\Model;
 
class SavingsTransactionModel extends Model
{
    protected $table            = 'savings_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'savings_goal_id',
        'wallet_id',
        'type',
        'amount',
        'notes',
    ];
 
    // Timestamps
    protected $useTimestamps = false;
}
