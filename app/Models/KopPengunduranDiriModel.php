<?php

namespace App\Models;

use CodeIgniter\Model;

class KopPengunduranDiriModel extends Model
{
    protected $table            = 'kop_pengunduran_diri';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'anggota_id',
        'nomor_surat',
        'nomor_urut',
        'hash_verifikasi',
        'crypt_salt',
        'status',
        'alasan_keluar',
        'alasan_penolakan',
        'processed_by',
        'processed_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
