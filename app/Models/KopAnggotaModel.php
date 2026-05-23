<?php

namespace App\Models;

use CodeIgniter\Model;

class KopAnggotaModel extends Model
{
    protected $table            = 'kop_anggota';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'nomor_anggota',
        'status_keaktifan'
    ];

    protected $useTimestamps = false;
}
