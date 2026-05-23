<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateCooperativeForFinancialRules extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        // 1. Modify kop_simpanan
        $this->forge->modifyColumn('kop_simpanan', [
            'jenis_simpanan' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ]
        ]);

        $this->forge->addColumn('kop_simpanan', [
            'bulan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'nominal',
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'bulan',
            ],
        ]);

        // 2. Modify kop_pinjaman
        $this->forge->addColumn('kop_pinjaman', [
            'jasa_nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'nominal_total',
            ],
            'metode_bayar_jasa' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'cicil',
                'after'      => 'jasa_nominal',
            ],
            'jenis_bunga' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'flat',
                'after'      => 'metode_bayar_jasa',
            ],
            'bunga_opsi_bayar' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'cicil',
                'after'      => 'jenis_bunga',
            ],
        ]);

        // 3. Modify kop_angsuran
        $this->forge->addColumn('kop_angsuran', [
            'pokok_dibayar' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'nominal_bayar',
            ],
            'bunga_dibayar' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'pokok_dibayar',
            ],
            'jasa_dibayar' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'bunga_dibayar',
            ],
            'sisa_pinjaman' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => '0.00',
                'after'      => 'jasa_dibayar',
            ],
            'tanggal_jatuh_tempo' => [
                'type'    => 'DATE',
                'null'    => true,
                'after'      => 'sisa_pinjaman',
            ],
        ]);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        // Rollback columns
        $this->forge->dropColumn('kop_simpanan', ['bulan', 'tahun']);
        $this->forge->dropColumn('kop_pinjaman', ['jasa_nominal', 'metode_bayar_jasa', 'jenis_bunga', 'bunga_opsi_bayar']);
        $this->forge->dropColumn('kop_angsuran', ['pokok_dibayar', 'bunga_dibayar', 'jasa_dibayar', 'sisa_pinjaman', 'tanggal_jatuh_tempo']);

        // Reset jenis_simpanan back to ENUM
        $this->forge->modifyColumn('kop_simpanan', [
            'jenis_simpanan' => [
                'type'       => 'ENUM',
                'constraint' => ['pokok', 'wajib', 'sukarela'],
            ]
        ]);

        $this->db->enableForeignKeyChecks();
    }
}
