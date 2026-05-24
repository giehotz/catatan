<?php

namespace App\Models;

use CodeIgniter\Model;

class KopAngsuranModel extends Model
{
    protected $table            = 'kop_angsuran';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pinjaman_id',
        'submission_id_fk',
        'angsuran_ke',
        'nominal_bayar',
        'pokok_dibayar',
        'bunga_dibayar',
        'jasa_dibayar',
        'sisa_pinjaman',
        'tanggal_jatuh_tempo',
        'bukti_bayar',
        'status',
        'tanggal_bayar',
        'approved_by',
        'approved_at',
        'catatan_tolak',
    ];

    protected $useTimestamps = false;

    // ── Cache Invalidation Hooks ──────────────────────────────────────────────
    protected $afterInsert = ['invalidateParentLoanCache'];
    protected $afterUpdate = ['invalidateParentLoanCache'];
    protected $afterDelete = ['invalidateParentLoanCache'];

    /**
     * Automatically clear the parent loan's cached amortization schedule
     * whenever an installment record is created, updated, or deleted.
     * Triggered at the Model layer to cover all data mutation paths.
     */
    protected function invalidateParentLoanCache(array $data): array
    {
        // On insert, pinjaman_id is in $data['data']
        $pinjamanId = $data['data']['pinjaman_id'] ?? null;

        // On update/delete, we may need to look up the record
        if (!$pinjamanId && !empty($data['id'])) {
            $id = is_array($data['id']) ? ($data['id'][0] ?? null) : $data['id'];
            if ($id) {
                $record = $this->builder()->select('pinjaman_id')->where('id', $id)->get()->getRowArray();
                $pinjamanId = $record['pinjaman_id'] ?? null;
            }
        }

        if ($pinjamanId) {
            cache()->delete("loan_schedule_{$pinjamanId}");
        }
        return $data;
    }
}