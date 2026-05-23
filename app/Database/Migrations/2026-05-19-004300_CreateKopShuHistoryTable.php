<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateKopShuHistoryTable extends Migration
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
            'anggota_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tahun' => [
                'type'       => 'INT',
                'constraint' => 4,
            ],
            'jasa_modal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'jasa_anggota' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'total_shu' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'tanggal_distribusi' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
            'distributed_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('anggota_id', 'kop_anggota', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('distributed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('kop_shu_history', true);

        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->db->disableForeignKeyChecks();
        $this->forge->dropTable('kop_shu_history', true);
        $this->db->enableForeignKeyChecks();
    }
}
