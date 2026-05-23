<?php

namespace App\Models;

use CodeIgniter\Model;

class IncomeCategoryModel extends Model
{
    protected $table            = 'income_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id', 
        'parent_id', 
        'name'
    ];
}
