<?php

namespace App\Models;

use CodeIgniter\Model;

class KopShuModel extends Model
{
    protected $table            = 'kop_shu_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'anggota_id',
        'tahun',
        'jasa_modal',
        'jasa_anggota',
        'jasa_usaha',
        'volume_pinjaman',
        'total_volume_pinjaman',
        'allocation_id',
        'total_shu',
        'tanggal_distribusi',
        'distributed_by',
    ];

    protected $useTimestamps = false;
}
