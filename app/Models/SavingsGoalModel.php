<?php
 
namespace App\Models;
 
use CodeIgniter\Model;
 
class SavingsGoalModel extends Model
{
    protected $table            = 'savings_goals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'name',
        'target_amount',
        'current_amount',
        'target_date',
    ];
 
    // Timestamps
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
