<?php

namespace App\Models;

use CodeIgniter\Model;

class KopShuAlokasi extends Model
{
    protected $table            = 'kop_shu_alokasi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'tahun',
        'total_shu_bersih',
        'cadangan_persen',
        'jasa_modal_persen',
        'jasa_usaha_persen',
        'dana_pengurus_persen',
        'dana_pendidikan_persen',
        'status',
        'approval_date',
        'approved_by',
        'created_by',
        'updated_by',
    ];

    protected $useTimestamps = false;

    public function validatePercentageTotal(array $data): bool
    {
        $total = $data['cadangan_persen']
               + $data['jasa_modal_persen']
               + $data['jasa_usaha_persen']
               + $data['dana_pengurus_persen']
               + $data['dana_pendidikan_persen'];

        return abs($total - 100.0) < 0.01;
    }

    public static function getDefaults(): array
    {
        $config = new KopShuConfiguration();

        return [
            'cadangan_persen'        => (float) ($config->where('key', 'shu_default_cadangan_persen')->first()['value'] ?? 40),
            'jasa_modal_persen'      => (float) ($config->where('key', 'shu_default_jasa_modal_persen')->first()['value'] ?? 20),
            'jasa_usaha_persen'      => (float) ($config->where('key', 'shu_default_jasa_usaha_persen')->first()['value'] ?? 25),
            'dana_pengurus_persen'   => (float) ($config->where('key', 'shu_default_dana_pengurus_persen')->first()['value'] ?? 10),
            'dana_pendidikan_persen' => (float) ($config->where('key', 'shu_default_dana_pendidikan_persen')->first()['value'] ?? 5),
        ];
    }
}
