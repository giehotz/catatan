<?php

namespace App\Models;

use CodeIgniter\Model;

class KopAngsuranSubmissionModel extends Model
{
    protected $table            = 'kop_angsuran_submissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pinjaman_id',
        'nominal_pengajuan',
        'bukti_bayar',
        'source',
        'status',
        'catatan_tolak',
        'approved_by',
        'approved_at',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
