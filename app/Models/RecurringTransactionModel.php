<?php
 
namespace App\Models;
 
use CodeIgniter\Model;
 
class RecurringTransactionModel extends Model
{
    protected $table            = 'recurring_transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'type', 
        'category_id', 
        'amount', 
        'description', 
        'frequency', 
        'start_date', 
        'last_run', 
        'next_run', 
        'is_active'
    ];
 
    // Enable auto timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
