<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'wallet_id',
        'to_wallet_id',
        'type', 
        'category_id', 
        'amount', 
        'description', 
        'transaction_date'
    ];

    // Enable auto timestamps for created_at
    protected $useTimestamps = false;
}
