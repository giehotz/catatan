<?php

namespace App\Models;

use CodeIgniter\Model;

class KopKasInternalModel extends Model
{
    protected $table            = 'kop_kas_internal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'kategori_dana',
        'jenis_transaksi',
        'nominal',
        'reference_type',
        'reference_id',
        'keterangan',
        'tanggal_transaksi',
        'created_by'
    ];

    // Dates
    protected $useTimestamps = false; // Using raw SQL DEFAULT CURRENT_TIMESTAMP in DB

    /**
     * Menghitung total saldo saat ini berdasarkan kategori dana (misal: 'kas_utama' atau 'dana_talangan')
     * Saldo = Total Pemasukan - Total Pengeluaran
     */
    public function getSaldo($kategoriDana)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table($this->table);
        
        // Pemasukan
        $pemasukan = $builder->selectSum('nominal', 'total')
                             ->where('kategori_dana', $kategoriDana)
                             ->where('jenis_transaksi', 'pemasukan')
                             ->get()->getRow()->total ?? 0;
                             
        // Pengeluaran
        $pengeluaran = $db->table($this->table)->selectSum('nominal', 'total')
                               ->where('kategori_dana', $kategoriDana)
                               ->where('jenis_transaksi', 'pengeluaran')
                               ->get()->getRow()->total ?? 0;
                               
        return (float) $pemasukan - (float) $pengeluaran;
    }
    
    /**
     * Memindahkan dana antar kas internal
     */
    public function transferFunds($dariKategori, $keKategori, $nominal, $userId, $keterangan)
    {
        $db = \Config\Database::connect();
        $db->transStart();
        
        // 1. Catat Pengeluaran dari kas asal
        $this->insert([
            'kategori_dana'     => $dariKategori,
            'jenis_transaksi'   => 'pengeluaran',
            'nominal'           => $nominal,
            'reference_type'    => 'transfer_internal',
            'keterangan'        => "Transfer Keluar ke " . strtoupper(str_replace('_', ' ', $keKategori)) . " - " . $keterangan,
            'created_by'        => $userId
        ]);
        
        // 2. Catat Pemasukan ke kas tujuan
        $this->insert([
            'kategori_dana'     => $keKategori,
            'jenis_transaksi'   => 'pemasukan',
            'nominal'           => $nominal,
            'reference_type'    => 'transfer_internal',
            'keterangan'        => "Transfer Masuk dari " . strtoupper(str_replace('_', ' ', $dariKategori)) . " - " . $keterangan,
            'created_by'        => $userId
        ]);
        
        $db->transComplete();
        
        return $db->transStatus();
    }
}
