<?php

namespace App\Models;

use CodeIgniter\Model;

class KopSimpananModel extends Model
{
    protected $table            = 'kop_simpanan';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'anggota_id',
        'jenis_simpanan',
        'tipe_transaksi',
        'nominal',
        'bulan',
        'tahun',
        'status',
        'bukti_transfer',
        'keterangan',
        'approved_by',
        'approved_at'
    ];

    protected $useTimestamps = false;
}
