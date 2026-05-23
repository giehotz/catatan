<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateKopKasInternalTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kategori_dana' => [
                'type'       => 'ENUM',
                'constraint' => ['kas_utama', 'dana_talangan', 'hibah', 'lainnya'],
                'default'    => 'kas_utama',
            ],
            'jenis_transaksi' => [
                'type'       => 'ENUM',
                'constraint' => ['pemasukan', 'pengeluaran', 'transfer_internal'],
                'default'    => 'pemasukan',
            ],
            'nominal' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'reference_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'reference_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'tanggal_transaksi' => [
                'type'    => 'TIMESTAMP',
                'default' => new RawSql('CURRENT_TIMESTAMP'),
            ],
        ]);
        
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('kop_kas_internal', true);
    }

    public function down()
    {
        $this->forge->dropTable('kop_kas_internal', true);
    }
}
