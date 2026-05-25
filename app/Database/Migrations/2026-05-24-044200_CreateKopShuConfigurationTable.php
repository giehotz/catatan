<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateKopShuConfigurationTable extends Migration
{
    public function up()
    {
        $this->db->disableForeignKeyChecks();

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'key' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'value' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'is_editable' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('key');
        $this->forge->createTable('kop_shu_configuration', true);

        // Seed default values (skip if already exist)
        if ($this->db->table('kop_shu_configuration')->countAll() === 0) {
            $seeds = [
                [
                    'key'         => 'shu_default_cadangan_persen',
                    'value'       => '40.00',
                    'description' => 'Persentase default Cadangan Koperasi',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_default_jasa_modal_persen',
                    'value'       => '20.00',
                    'description' => 'Persentase default Jasa Modal (Simpanan)',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_default_jasa_usaha_persen',
                    'value'       => '25.00',
                    'description' => 'Persentase default Jasa Usaha (Pinjaman)',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_default_dana_pengurus_persen',
                    'value'       => '10.00',
                    'description' => 'Persentase default Dana Pengurus',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_default_dana_pendidikan_persen',
                    'value'       => '5.00',
                    'description' => 'Persentase default Dana Pendidikan',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_include_inactive_members',
                    'value'       => '0',
                    'description' => 'Apakah include anggota non-aktif dalam perhitungan SHU?',
                    'is_editable' => 1,
                ],
                [
                    'key'         => 'shu_cutoff_days_before_rat',
                    'value'       => '30',
                    'description' => 'Cut-off hari sebelum RAT untuk perhitungan SHU',
                    'is_editable' => 1,
                ],
            ];

            $this->db->table('kop_shu_configuration')->insertBatch($seeds);
        }

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('kop_shu_configuration', true);
        $this->db->enableForeignKeyChecks();
    }
}
