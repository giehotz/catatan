<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterKopShuHistoryAddFields extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->addColumn('kop_shu_history', [
            'jasa_usaha' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'after'      => 'jasa_anggota',
                'null'       => true,
            ],
            'volume_pinjaman' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'after'      => 'jasa_usaha',
                'null'       => true,
            ],
            'total_volume_pinjaman' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'after'      => 'volume_pinjaman',
                'null'       => true,
            ],
            'allocation_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'after'      => 'total_volume_pinjaman',
                'null'       => true,
            ],
        ]);

        $this->forge->addForeignKey('allocation_id', 'kop_shu_alokasi', 'id', 'SET NULL', 'CASCADE', 'fk_shu_history_alokasi');

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();

        $fkNames = ['fk_shu_history_alokasi', 'kop_shu_history_allocation_id_foreign'];
        foreach ($fkNames as $name) {
            try {
                $this->db->query("ALTER TABLE `kop_shu_history` DROP FOREIGN KEY `{$name}`");
            } catch (\Throwable $e) {
                // Ignore if FK name doesn't exist
            }
        }
        $this->forge->dropColumn('kop_shu_history', ['jasa_usaha', 'volume_pinjaman', 'total_volume_pinjaman', 'allocation_id']);

        $this->db->enableForeignKeyChecks();
    }
}
